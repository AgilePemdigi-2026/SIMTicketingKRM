<?php

namespace App\Http\Controllers\Petugas;

use App\Contracts\TicketCatalogProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Petugas\CheckoutCounterTicketRequest;
use App\Http\Requests\Petugas\PreviewCounterTicketRequest;
use App\Services\TicketPaymentService;
use App\Services\TicketPurchaseService;
use App\Support\TicketPurchaseDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TicketCounterController extends Controller
{
    public function index(TicketCatalogProvider $catalog): View
    {
        return view('petugas.loket.index', [
            'tickets' => $catalog->activeTickets(),
            'today' => now('Asia/Jakarta')->toDateString(),
            'quote' => null,
            'checkoutToken' => (string) Str::uuid(),
        ]);
    }

    public function preview(
        PreviewCounterTicketRequest $request,
        TicketPurchaseService $service,
        TicketCatalogProvider $catalog
    ): View {
        $quote = $service->calculate($request->validated());

        return view('petugas.loket.index', [
            'tickets' => $catalog->activeTickets(),
            'today' => now('Asia/Jakarta')->toDateString(),
            'quote' => $quote,
            'checkoutToken' => $request->input('checkout_token') ?: (string) Str::uuid(),
        ]);
    }

    public function checkout(
        CheckoutCounterTicketRequest $request,
        TicketPurchaseService $purchaseService,
        TicketPaymentService $paymentService,
        TicketPurchaseDatabase $database
    ): RedirectResponse {
        $data = $request->validated();
        $staff = $request->user();

        // Simpan pesanan walk-in dan pembayaran secara atomik.
        $payment = $database->connection()->transaction(
            function () use ($staff, $data, $purchaseService, $paymentService) {
                $order = $purchaseService->createForCounter($staff, $data);

                return $paymentService->selectForCounter(
                    $staff,
                    $order->code,
                    $data['payment_method']
                );
            }
        );

        if ($payment->method === 'qris') {
            return redirect()
                ->route('petugas.loket.qris.show', $payment->id)
                ->with(
                    'payment_updated',
                    'Pesanan loket berhasil dibuat. Buat QR Midtrans untuk pembayaran.'
                );
        }

        // Cash tetap menuju konfirmasi penerimaan uang oleh Petugas.
        return redirect()
            ->route('petugas.loket.cash.show', $payment->id)
            ->with(
                'payment_updated',
                'Pesanan loket berhasil dibuat. Silakan selesaikan pembayaran Cash.'
            );
    }
}
