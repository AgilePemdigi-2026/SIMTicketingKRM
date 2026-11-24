<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\TicketPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CounterCashController extends Controller
{
    /**
     * Halaman penerimaan Cash untuk transaksi Jual Tiket Langsung.
     * Pembayaran Cash Booking tetap menggunakan halaman lamanya.
     */
    public function show(Request $request, TicketPayment $payment): View|RedirectResponse
    {
        abort_unless($request->user()?->role === 'petugas', 403);

        $payment->load(['order.items', 'order.staff']);
        $order = $payment->order;

        // Jangan izinkan transaksi online atau milik kasir lain.
        abort_unless(
            $order !== null
                && $order->sale_channel === 'counter'
                && (int) $order->staff_user_id === (int) $request->user()->id
                && $payment->method === 'cash',
            404
        );

        if ($payment->status === 'paid' && $order->status === 'paid') {
            return redirect()->route('petugas.loket.success', $payment->id);
        }

        abort_unless(
            $payment->status === 'pending'
                && $order->status === 'pending_payment',
            409,
            'Pembayaran Cash tidak dapat diproses pada status ini.'
        );

        return view('petugas.loket.cash', compact('payment'));
    }
}
