<?php

namespace App\Http\Controllers\Wisatawan;

use App\Contracts\TicketCatalogProvider;
use App\Events\TicketPaymentConfirmed;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutTicketPurchaseRequest;
use App\Http\Requests\PreviewTicketPurchaseRequest;
use App\Listeners\IssueTicketsAfterPayment;
use App\Models\TicketOrder;
use App\Models\FacilityReservation;
use App\Services\TicketPurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TicketPurchaseController extends Controller
{
    public function index(Request $request, TicketCatalogProvider $catalog): JsonResponse|View
    {
        $tickets = $catalog->activeTickets();
        if (! $request->expectsJson()) {
            return view('wisatawan.ticket-purchase.index', [
                'tickets' => $tickets,
                'booker' => $request->user()->only(['name', 'identity_number', 'phone', 'email']),
                'visitorType' => $request->user()->visitor_type === 'mancanegara' ? 'mancanegara' : 'domestik',
                'today' => now('Asia/Jakarta')->toDateString(),
                'checkoutToken' => is_string($request->old('checkout_token')) && Str::isUuid($request->old('checkout_token'))
                    ? $request->old('checkout_token') : (string) Str::uuid(),
            ]);
        }

        return response()->json(['is_dummy' => false, 'currency' => 'IDR', 'tickets' => $tickets]);
    }

    public function preview(PreviewTicketPurchaseRequest $request, TicketPurchaseService $service): JsonResponse
    {
        return response()->json([
            'is_dummy' => false, 'currency' => 'IDR',
            'quote' => $service->calculate($request->validated()),
            'quota_reserved' => false,
        ]);
    }

    public function orders(Request $request): JsonResponse|View
    {
        $orders = TicketOrder::with([
            'items',
            'issuedTickets',
            'payment.gatewayAttempts' => fn ($query) => $query->orderByDesc('id'),
        ])->where('user_id', $request->user()->getKey())->orderByDesc('created_at')->orderByDesc('id')->paginate(20);

        foreach ($orders->getCollection() as $order) {
            if ($order->status === 'paid' && $order->payment?->status === 'paid' && $order->issuedTickets->isEmpty()) {
                app(IssueTicketsAfterPayment::class)->handle(new TicketPaymentConfirmed($order->code));
                $order->load('issuedTickets');
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'is_dummy' => false,
                'currency' => 'IDR',
                'orders' => $orders,
            ]);
        }

        // Ambil riwayat reservasi milik wisatawan yang login.
        // Pembayaran dan percobaan QRIS diambil melalui relasi.
        $reservations = FacilityReservation::query()
            ->with([
                'payment.attempts' => fn ($query) =>
                    $query->orderByDesc('id'),
                'pendingRescheduleRequest',
            ])
            ->where('user_id', $request->user()->getKey())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'reservasi_page');

        // Kirim riwayat tiket dan reservasi ke halaman yang sama.
        return view('wisatawan.ticket-purchase.orders', compact(
            'orders',
            'reservations'
        ));

    }

    public function store(
        CheckoutTicketPurchaseRequest $request,
        TicketPurchaseService $service
    ): JsonResponse|RedirectResponse {
        // Simpan pesanan melalui service yang sudah ada.
        // Validasi, harga, dan data pengunjung tetap diproses backend.
        $order = $service->create(
            $request->user(),
            $request->validated()
        );

        // Setelah pesanan dibuat, langsung masuk ke
        // tahap 3: Pembayaran QRIS atau Cash.
        if (! $request->expectsJson()) {
            return redirect()
                ->route('ticket-payment.show', $order->code)
                ->with('purchase_created', true);
        }

        // Respons API tetap dipertahankan.
        return response()->json([
            'is_dummy' => false,
            'currency' => 'IDR',
            'order' => $order,
        ], 201);
    }

    public function show(Request $request, string $code): JsonResponse|View
    {
        $order = TicketOrder::where('user_id', $request->user()->getKey())
            ->where('code', $code)->with(['items.visitors', 'payment'])->firstOrFail();

        if (! $request->expectsJson()) {
            return view('wisatawan.ticket-purchase.show', compact('order'));
        }

        return response()->json(['is_dummy' => false, 'currency' => 'IDR', 'order' => $order]);
    }
}
