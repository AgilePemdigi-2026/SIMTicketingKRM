<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewTicketPaymentRequest;
use App\Http\Requests\SelectTicketPaymentRequest;
use App\Http\Requests\SubmitTicketPaymentProofRequest;
use App\Models\TicketOrder;
use App\Models\TicketPayment;
use App\Models\TicketPaymentSubmission;
use App\Services\TicketPaymentService;
use App\Services\TicketQrisPaymentService;
use App\Services\TicketQrisStatusSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TicketPaymentController extends Controller
{
    public function show(Request $request, string $code): JsonResponse|View
    {
        $order = TicketOrder::where('code', $code)->where('user_id', $request->user()->id)
        ->with([
            'items',
            'payment.submissions' => fn ($query) => $query->orderByDesc('id'),
            'payment.events',
            'payment.gatewayAttempts' => fn ($query) => $query->orderByDesc('id'),
        ])->firstOrFail();

        if ($request->expectsJson()) {
            return response()->json(['order_code' => $order->code, 'order_status' => $order->status,
                'currency' => 'IDR', 'total' => $order->total, 'payment' => $order->payment]);
        }

        return view('wisatawan.ticket-payment.show', compact('order'));
    }

    public function eTicketPlaceholder(
        Request $request,
        string $code
    ): View|RedirectResponse {
        // Hanya ambil pesanan milik Wisatawan yang login.
        $order = TicketOrder::query()
            ->where('code', $code)
            ->where('user_id', $request->user()->getKey())
            ->with(['items', 'payment'])
            ->firstOrFail();

        // E-Tiket sementara hanya untuk pembayaran Lunas.
        if (
            $order->status !== 'paid'
            || $order->payment?->status !== 'paid'
        ) {
            return redirect()
                ->route('ticket-payment.show', $order->code)
                ->with(
                    'payment_updated',
                    'Halaman E-Tiket tersedia setelah pembayaran Lunas.'
                );
        }

        // Halaman placeholder, tanpa QR Code.
        return view(
            'wisatawan.ticket-payment.e-ticket-placeholder',
            compact('order')
        );
    }

    public function select(SelectTicketPaymentRequest $request, string $code, TicketPaymentService $service): JsonResponse|RedirectResponse
    {
        return $this->result($request, $service->select($request->user(), $code, $request->validated('method')));
    }

    public function createQris(
        Request $request,
        string $code,
        TicketQrisPaymentService $service
    ): JsonResponse|RedirectResponse
    {
        // Membuat atau mengambil kembali transaksi QRIS.
        $attempt = $service->createFor(
            $request->user(),
            $code
        );

        // Respons untuk pengujian API.
        if ($request->expectsJson()) {
            return response()->json([
                'order_code' => $code,
                'status' => $attempt->status,
                'qr_image_url' => $attempt->qr_image_url,
                'expires_at' => $attempt->expires_at?->toIso8601String(),
            ]);
        }

        // Kembali ke halaman pembayaran Wisatawan.
        return redirect()
            ->route('ticket-payment.show', $code)
            ->with(
                'payment_updated',
                'QRIS Midtrans Sandbox berhasil disiapkan.'
            );
    }

    public function syncQris(
        Request $request,
        string $code,
        TicketQrisStatusSyncService $service
    ): JsonResponse|RedirectResponse
    {
        // Ambil status terbaru langsung dari Midtrans Sandbox.
        // Service memverifikasi bahwa pesanan milik Wisatawan.
        $attempt = $service->syncFor(
            $request->user(),
            $code
        );

        // Respons untuk API / automated testing.
        if ($request->expectsJson()) {
            return response()->json([
                'order_code' => $code,
                'status' => $attempt->status,
                'expires_at' => $attempt->expires_at?->toIso8601String(),
            ]);
        }

        // Kembali ke halaman pembayaran dengan status terbaru.
        return redirect()
            ->route('ticket-payment.show', $code)
            ->with(
                'payment_updated',
                'Status pembayaran QRIS berhasil diperbarui dari Midtrans.'
            );
    }

    public function submit(SubmitTicketPaymentProofRequest $request, string $code, TicketPaymentService $service): JsonResponse|RedirectResponse
    {
        return $this->result($request, $service->submit($request->user(), $code, $request->file('proof')));
    }

    public function index(Request $request): JsonResponse|View
    {
        $payments = TicketPayment::with('order:id,code,booker_name,visit_date')
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(20);

        return $request->expectsJson() ? response()->json(['payments' => $payments])
            : view('petugas.ticket-payment.index', compact('payments'));
    }

    public function detail(Request $request, TicketPayment $payment): JsonResponse|View
    {
        $payment->load(['order', 'submissions' => fn ($query) => $query->orderByDesc('id'), 'events']);

        return $request->expectsJson() ? response()->json(['payment' => $payment])
            : view('petugas.ticket-payment.show', compact('payment'));
    }

    public function confirm(ReviewTicketPaymentRequest $request, TicketPayment $payment, TicketPaymentService $service): JsonResponse|RedirectResponse
    {
        return $this->result($request, $service->review($request->user(), $payment->id, $request->validated(), true));
    }

    public function reject(ReviewTicketPaymentRequest $request, TicketPayment $payment, TicketPaymentService $service): JsonResponse|RedirectResponse
    {
        return $this->result($request, $service->review($request->user(), $payment->id, $request->validated(), false));
    }

    public function proof(Request $request, TicketPaymentSubmission $submission): BinaryFileResponse
    {
        $payment = $submission->payment()->with('order')->firstOrFail();
        abort_unless($request->user()->role === 'petugas'
            || ($request->user()->role === 'wisatawan' && $payment->order->user_id === $request->user()->id), 404);
        abort_unless(Storage::disk('local')->exists($submission->file_path), 404);

        return response()->file(Storage::disk('local')->path($submission->file_path), [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }

    private function result(Request $request, TicketPayment $payment): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['currency' => 'IDR', 'order_code' => $payment->order->code,
                'order_status' => $payment->order->status, 'payment' => $payment->load(['submissions', 'events'])]);
        }
        $route = $request->user()->role === 'petugas' ? 'ticket-payment.staff.show' : 'ticket-payment.show';
        $parameter = $request->user()->role === 'petugas' ? $payment->id : $payment->order->code;

        return redirect()->route($route, $parameter)->with('payment_updated', 'Pembayaran berhasil diperbarui.');
    }
}
