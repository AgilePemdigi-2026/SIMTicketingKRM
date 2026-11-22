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
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Endroid\QrCode\Writer\PngWriter;
use Barryvdh\DomPDF\Facade\Pdf;

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

    /**
     * Menampilkan E-Tiket Sementara sebagai bukti
     * pemesanan Cash sebelum pembayaran dikonfirmasi.
     */
    public function temporaryETicket(
        Request $request,
        string $code
    ): View|RedirectResponse {
        // Hanya pemilik pesanan yang boleh mengakses.
        $order = TicketOrder::query()
            ->where('code', $code)
            ->where('user_id', $request->user()->getKey())
            ->with([
                'items.visitors',
                'payment',
            ])
            ->firstOrFail();

        // E-Tiket Sementara hanya untuk pembayaran Cash.
        abort_unless(
            $order->payment?->method === 'cash',
            404,
            'E-Tiket Sementara hanya tersedia untuk pembayaran Cash.'
        );

        // Jika sudah Lunas, arahkan ke E-Tiket final.
        if (
            $order->status === 'paid'
            && $order->payment?->status === 'paid'
        ) {
            return redirect()->route(
                'ticket-payment.e-ticket-placeholder',
                ['code' => $order->code]
            );
        }

        // Hanya pesanan yang masih menunggu pembayaran
        // yang bisa menampilkan E-Tiket Sementara.
        abort_unless(
            $order->status === 'pending_payment'
            && $order->payment?->status !== 'paid',
            403,
            'Pesanan tidak dapat menggunakan E-Tiket Sementara.'
        );

        // Kode singkat untuk pembayaran di loket.
        $cashDisplayCode = 'CASH-' . str_pad(
            (string) $order->id,
            4,
            '0',
            STR_PAD_LEFT
        );

        // QR khusus bukti pemesanan, BUKAN QR check-in.
        // Format sengaja berbeda dari qr_token E-Tiket final.
        $qrCode = (new Builder())->build(
            writer: new SvgWriter(),
            data: 'KRM-CASH-ORDER:' . $order->code,
            size: 240,
            margin: 10,
        );

        $qrCodeDataUri = $qrCode->getDataUri();

        return view(
            'wisatawan.ticket-payment.e-ticket-temporary',
            compact(
                'order',
                'cashDisplayCode',
                'qrCodeDataUri'
            )
        );
    }

    public function eTicketPlaceholder(
        Request $request,
        string $code
    ): View|RedirectResponse {
        $order = TicketOrder::query()
            ->where('code', $code)
            ->where('user_id', $request->user()->getKey())
            ->with([
                'items.visitors',
                'payment',
                'issuedTickets.visitor',
                'issuedTickets.item',
            ])
            ->firstOrFail();

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

        foreach ($order->issuedTickets as $ticket) {
            $qrCode = (new Builder())->build(
                writer: new SvgWriter(),
                data: $ticket->qr_token,
                size: 240,
                margin: 10,
            );

            $ticket->setAttribute(
                'qr_code_data_uri',
                $qrCode->getDataUri()
            );
        }

        return view(
            'wisatawan.ticket-payment.e-ticket',
            compact('order')
        );
    }

    /**
     * Mengunduh seluruh E-Tiket dalam satu pesanan
     * sebagai file PDF.
     */
    public function downloadETicket(
        Request $request,
        string $code
    ) {
        // Hanya pemilik pesanan yang boleh mengunduh.
        $order = TicketOrder::query()
            ->where('code', $code)
            ->where('user_id', $request->user()->getKey())
            ->with([
                'payment',
                'issuedTickets.visitor',
                'issuedTickets.item',
            ])
            ->firstOrFail();

        // PDF hanya tersedia setelah pembayaran Lunas.
        abort_unless(
            $order->status === 'paid'
            && $order->payment?->status === 'paid',
            403,
            'E-Tiket hanya dapat diunduh setelah pembayaran Lunas.'
        );

        // Pastikan E-Tiket sudah diterbitkan.
        abort_if(
            $order->issuedTickets->isEmpty(),
            404,
            'E-Tiket belum diterbitkan.'
        );

        // Generate QR Code PNG agar kompatibel dengan Dompdf.
        foreach ($order->issuedTickets as $ticket) {
            $qrCode = (new Builder())->build(
                writer: new PngWriter(),
                data: $ticket->qr_token,
                size: 280,
                margin: 10,
            );

            $ticket->setAttribute(
                'qr_code_data_uri',
                $qrCode->getDataUri()
            );
        }

        // Satu PDF berisi seluruh E-Tiket dalam pesanan.
        $pdf = Pdf::loadView(
            'wisatawan.ticket-payment.e-ticket-pdf',
            compact('order')
        )->setPaper('a4', 'portrait');

        return $pdf->download(
            'E-Tiket-' . $order->code . '.pdf'
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
