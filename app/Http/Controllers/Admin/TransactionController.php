<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketPayment;
use App\Support\TicketPurchaseDatabase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Endroid\QrCode\Builder\Builder as QrBuilder;
use Endroid\QrCode\Writer\SvgWriter;

class TransactionController extends Controller
{
    public function index(Request $request, TicketPurchaseDatabase $database): View
    {
        $connection = $database->connection();

        $query = TicketPayment::on($connection->getName())
            ->with([
                'order.items',
                'order.issuedTickets',
            ])
            ->whereHas('order');

        // Filter tanggal kunjungan.
        if ($request->filled('visit_date')) {
            $query->whereHas('order', function (Builder $order) use ($request) {
                $order->whereDate('visit_date', $request->input('visit_date'));
            });
        }

        // Filter status pembayaran.
        if ($request->filled('payment_status') &&
            $request->input('payment_status') !== 'semua') {
            $query->where('status', $request->input('payment_status'));
        }

        // Filter status tiket.
if ($request->filled('ticket_status') &&
    $request->input('ticket_status') !== 'semua') {

    $ticketStatus = $request->input('ticket_status');

    if ($ticketStatus === 'belum_terbit') {
        $query->whereDoesntHave('order.issuedTickets');
    }

    if ($ticketStatus === 'belum_checkin') {
        $query->whereHas('order.issuedTickets')
            ->whereDoesntHave('order.issuedTickets', function ($ticket) {
                $ticket->where('status', 'used');
            });
    }

    if ($ticketStatus === 'sebagian_checkin') {
        $query->whereHas('order.issuedTickets', function ($ticket) {
            $ticket->where('status', 'used');
        })->whereHas('order.issuedTickets', function ($ticket) {
            $ticket->where('status', '!=', 'used');
        });
    }

    if ($ticketStatus === 'sudah_checkin') {
        $query->whereHas('order.issuedTickets', function ($ticket) {
            $ticket->where('status', 'used');
        })->whereDoesntHave('order.issuedTickets', function ($ticket) {
            $ticket->where('status', '!=', 'used');
        });
    }
}

        // Filter metode pembayaran.
        if ($request->filled('method') &&
            $request->input('method') !== 'semua') {
            $query->where('method', $request->input('method'));
        }

        // Pencarian nomor transaksi atau nama pengunjung.
        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->whereHas('order', function (Builder $order) use ($search) {
                $order->where('code', 'ilike', "%{$search}%")
                    ->orWhere('booker_name', 'ilike', "%{$search}%");
            });
        }

        $payments = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.transaksi.index', [
            'payments' => $payments,
        ]);
    }


public function show(int $payment, TicketPurchaseDatabase $database): View
{
    $connection = $database->connection();

    $payment = TicketPayment::on($connection->getName())
        ->with([
            'order.items.visitors',
            'order.issuedTickets.checkedInBy',
            'order.staff',
        ])
        ->whereHas('order')
        ->findOrFail($payment);

    $order = $payment->order;
    $tickets = $order->issuedTickets;

    $totalTickets = $tickets->count();

    $usedTickets = $tickets->where('status', 'used');

    $usedCount = $usedTickets->count();

    if ($totalTickets === 0) {
        $ticketStatus = 'E-Tiket Belum Terbit';
    } elseif ($usedCount === 0) {
        $ticketStatus = 'Belum Check-In';
    } elseif ($usedCount === $totalTickets) {
        $ticketStatus = 'Sudah Check-In';
    } else {
        $ticketStatus = 'Sebagian Check-In';
    }

    $lastCheckInAt = $usedTickets
        ->pluck('used_at')
        ->filter()
        ->sortDesc()
        ->first();

    $validatorNames = $usedTickets
        ->map(fn ($ticket) => $ticket->checkedInBy?->name)
        ->filter()
        ->unique()
        ->values();

    $cashierName = $order->staff?->name;

    // QR hanya ditampilkan jika pembayaran sudah lunas
// dan E-Tiket memang sudah diterbitkan.
if (
    $order->status === 'paid'
    && $payment->status === 'paid'
) {
    foreach ($tickets as $ticket) {
        if (filled($ticket->qr_token)) {
            $qrCode = (new QrBuilder())->build(
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
    }
}

    return view('admin.transaksi.show', [
        'payment' => $payment,
        'order' => $order,
        'tickets' => $tickets,
        'ticketStatus' => $ticketStatus,
        'lastCheckInAt' => $lastCheckInAt,
        'validatorNames' => $validatorNames,
        'cashierName' => $cashierName,
    ]);
}

}
