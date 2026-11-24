<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\TicketPayment;
use App\Services\TicketQrisPaymentService;
use App\Services\TicketQrisStatusSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CounterQrisController extends Controller
{
    /**
     * Halaman status pembayaran QRIS untuk transaksi loket.
     */
    public function show(Request $request, TicketPayment $payment): View
    {
        $payment = $this->authorizedPayment($request, $payment);

        $attempt = $payment->gatewayAttempts
            ->sortByDesc('id')
            ->first();

        return view('petugas.loket.qris', compact('payment', 'attempt'));
    }

    /**
     * Buat transaksi QRIS Midtrans atau gunakan kembali yang masih aktif.
     */
    public function create(
        Request $request,
        TicketPayment $payment,
        TicketQrisPaymentService $service
    ): RedirectResponse {
        $payment = $this->authorizedPayment($request, $payment);

        if ($payment->status === 'paid') {
            return redirect()->route('petugas.loket.success', $payment->id);
        }

        $service->createForCounter($request->user(), $payment->order->code);

        return redirect()
            ->route('petugas.loket.qris.show', $payment->id)
            ->with('payment_updated', 'QRIS Midtrans Sandbox berhasil disiapkan.');
    }

    /**
     * Sinkronkan status dari Midtrans tanpa mengonfirmasi pembayaran manual.
     */
    public function sync(
        Request $request,
        TicketPayment $payment,
        TicketQrisStatusSyncService $service
    ): RedirectResponse {
        $payment = $this->authorizedPayment($request, $payment);

        if ($payment->status === 'paid') {
            return redirect()->route('petugas.loket.qris.show', $payment->id);
        }

        $service->syncForCounter($request->user(), $payment->order->code);

        return redirect()
            ->route('petugas.loket.qris.show', $payment->id)
            ->with('payment_updated', 'Status pembayaran diperbarui dari Midtrans Sandbox.');
    }

    /**
     * Petugas hanya boleh mengakses transaksi QRIS loket yang dibuat sendiri.
     */
    private function authorizedPayment(
        Request $request,
        TicketPayment $payment
    ): TicketPayment {
        abort_unless($request->user()?->role === 'petugas', 403);

        $payment->load([
            'order.items',
            'order.staff',
            'gatewayAttempts',
        ]);

        $order = $payment->order;

        abort_unless(
            $order !== null
            && $order->sale_channel === 'counter'
            && $order->user_id === null
            && (int) $order->staff_user_id === (int) $request->user()->id
            && $payment->method === 'qris',
            404
        );

        return $payment;
    }
}
