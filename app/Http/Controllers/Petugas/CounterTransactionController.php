<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\TicketPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CounterTransactionController extends Controller
{
    /**
     * Halaman transaksi berhasil untuk pembelian loket.
     */
    public function success(
        Request $request,
        TicketPayment $payment
    ): View {
        $payment = $this->authorizedPayment(
            $request,
            $payment
        );

        return view(
            'petugas.loket.success',
            compact('payment')
        );
    }

    /**
     * Cetak E-Tiket asli tanpa membuat QR token baru.
     */
    public function print(
        Request $request,
        TicketPayment $payment
    ) {
        $payment = $this->authorizedPayment(
            $request,
            $payment
        );

        $order = $payment->order;

        // Buat gambar QR dari token yang sudah diterbitkan.
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

        // Gunakan template PDF E-Tiket yang sama
        // dengan milik Wisatawan.
        $pdf = Pdf::loadView(
            'wisatawan.ticket-payment.e-ticket-pdf',
            compact('order')
        )->setPaper('a4', 'portrait');

        return $pdf->stream(
            'E-Tiket-' . $order->code . '.pdf'
        );
    }

    /**
     * Pastikan hanya Petugas pembuat transaksi loket
     * yang dapat mengakses tiket setelah pembayaran Lunas.
     */
    private function authorizedPayment(
        Request $request,
        TicketPayment $payment
    ): TicketPayment {
        abort_unless(
            $request->user()?->role === 'petugas',
            403
        );

        $payment->load([
            'order.staff',
            'order.items',
            'order.issuedTickets.visitor',
            'order.issuedTickets.item',
        ]);

        $order = $payment->order;

        abort_unless(
            $order !== null
            && $order->sale_channel === 'counter'
            && (int) $order->staff_user_id
                === (int) $request->user()->id,
            404
        );

        abort_unless(
            $payment->status === 'paid'
            && $order->status === 'paid',
            403,
            'Tiket hanya dapat dicetak setelah pembayaran Lunas.'
        );

        abort_if(
            $order->issuedTickets->isEmpty(),
            404,
            'E-Tiket belum diterbitkan.'
        );

        return $payment;
    }
}
