<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\TicketPayment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashBookingSuccessController extends Controller
{
    /**
     * Halaman sukses Cash Booking ONLINE, bukan pembelian counter/loket.
     * Status Lunas diperiksa ulang dari database pada setiap permintaan.
     */
    public function show(Request $request, TicketPayment $payment): View
    {
        $payment = $this->authorizedPaidBooking($request, $payment);

        // Nama petugas berdasarkan siapa yang mencatat konfirmasi,
        // bukan siapa yang sedang membuka halaman ini sekarang.
        $cashierName = $payment->confirmed_by
            ? User::query()->whereKey($payment->confirmed_by)->value('name')
            : null;

        return view('petugas.ticket-payment.success', [
            'payment' => $payment,
            'cashierName' => $cashierName,
        ]);
    }

    /**
     * Mencetak PDF E-Tiket ASLI (QR token terbitan sistem), bukan mockup.
     */
    public function print(Request $request, TicketPayment $payment)
    {
        $payment = $this->authorizedPaidBooking($request, $payment);
        $order = $payment->order;

        // Pastikan E-Tiket asli sudah diterbitkan.
        abort_unless(
            $order->issuedTickets->isNotEmpty(),
            409,
            'E-Tiket untuk booking ini belum terbit.'
        );

        // Generate gambar QR dari token E-Tiket ASLI.
        // PNG digunakan agar kompatibel dengan Dompdf.
        foreach ($order->issuedTickets as $ticket) {
            abort_unless(
                filled($ticket->qr_token),
                409,
                'Token QR E-Tiket tidak ditemukan.'
            );

            $qrCode = (new Builder())->build(
                writer: new PngWriter(),
                data: $ticket->qr_token,
                size: 280,
                margin: 10,
            );

            // Kirim gambar QR ke template PDF.
            $ticket->setAttribute(
                'qr_code_data_uri',
                $qrCode->getDataUri()
            );
        }

        // Cetak E-Tiket dengan QR Code yang sudah dibuat.
        return Pdf::loadView(
            'wisatawan.ticket-payment.e-ticket-pdf',
            compact('order')
        )
            ->setPaper('a4', 'portrait')
            ->stream('E-Tiket-' . $order->code . '.pdf');
    }

    private function authorizedPaidBooking(Request $request, TicketPayment $payment): TicketPayment
    {
        abort_unless($request->user()?->role === 'petugas', 403);

        $payment->load([
            'order.items.visitors',
            'order.issuedTickets.visitor',
        ]);

        abort_unless(
            $payment->order !== null
            && $payment->order->sale_channel === 'online'
            && $payment->method === 'cash'
            && $payment->status === 'paid',
            404
        );

        return $payment;
    }
}
