<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\FacilityReservation;
use App\Models\IssuedTicket;
use App\Models\TicketPayment;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\TicketOrderVisitor;

class PetugasDashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Gunakan tanggal operasional WIB.
        $nowWib = CarbonImmutable::now('Asia/Jakarta');
        $today = $nowWib->toDateString();

        // Batas waktu hari ini dikonversi ke UTC.
        $startUtc = $nowWib->startOfDay()
            ->setTimezone('UTC');

        $endUtc = $nowWib->addDay()->startOfDay()
            ->setTimezone('UTC');

        // 1. Jumlah E-Tiket terbit untuk kunjungan hari ini.
        $visitorsToday = IssuedTicket::query()
            ->whereHas('order', function ($query) use ($today) {
                $query->whereDate('visit_date', $today)
                    ->where('status', 'paid');
            })
            ->count();

        // 2. Tiket yang berhasil check-in hari ini.
        $checkedInToday = IssuedTicket::query()
            ->where('status', 'used')
            ->where('used_at', '>=', $startUtc)
            ->where('used_at', '<', $endUtc)
            ->count();

        // 3. Cash Booking Online yang belum dibayar.
        $pendingCashBookings = TicketPayment::query()
            ->where('method', 'cash')
            ->where('status', 'pending')
            ->whereHas('order', function ($query) {
                $query->where(function ($channel) {
                    $channel->where('sale_channel', 'online')
                        ->orWhereNull('sale_channel');
                });
            })
            ->count();

        // 4. Transaksi tiket yang dibuat hari ini.
        $transactionsToday = TicketPayment::query()
            ->whereHas('order')
            ->where('created_at', '>=', $startUtc)
            ->where('created_at', '<', $endUtc)
            ->count();

        // 5. Reservasi fasilitas untuk hari ini yang lunas.
        $reservationsToday = FacilityReservation::query()
            ->whereDate('visit_date', $today)
            ->whereHas('payment', function ($query) {
                $query->where('status', 'paid');
            })
            ->count();


        // 6. Aktivitas pengunjung berdasarkan tanggal kunjungan.
        $activityQuery = TicketOrderVisitor::query()
            ->whereHas('item.order', function ($query) use ($today) {
                $query->whereDate('visit_date', $today);
            });

        $activityTotal = (clone $activityQuery)->count();

        $activitiesToday = $activityQuery
            ->with([
                'item.order.payment',
                'item.order.issuedTickets',
            ])
            ->orderByDesc('id')
            ->limit(7)
            ->get()
            ->map(function (TicketOrderVisitor $visitor) {
                $item = $visitor->item;
                $order = $item?->order;

                $issuedTicket = $order?->issuedTickets
                    ?->firstWhere('ticket_order_visitor_id', $visitor->id);

                $paymentStatus = $order?->payment?->status;

                $ticketStatus = match (true) {
                    $issuedTicket?->status === 'used'
                        || $issuedTicket?->used_at !== null
                            => 'Sudah Check-In',

                    $paymentStatus === 'paid'
                        && $order?->status === 'paid'
                            => 'Belum Digunakan',

                    in_array($paymentStatus, ['pending', 'under_review'], true)
                            => 'Menunggu Pembayaran',

                    $paymentStatus === 'expired'
                            => 'Kedaluwarsa',

                    $paymentStatus === 'failed'
                            => 'Gagal',

                    default => 'Status Tidak Diketahui',
                };

                return [
                    'name' => $visitor->name,
                    'visit_date' => $order?->visit_date,
                    'ticket_name' => $item?->ticket_name,
                    'status' => $ticketStatus,
                ];
            });

        return view('petugas.ticket-payment.dashboard', [
            'today' => $today,
            'nowWib' => $nowWib,
            'visitorsToday' => $visitorsToday,
            'checkedInToday' => $checkedInToday,
            'pendingCashBookings' => $pendingCashBookings,
            'transactionsToday' => $transactionsToday,
            'reservationsToday' => $reservationsToday,
            'activitiesToday' => $activitiesToday,
            'activityTotal' => $activityTotal,
        ]);
    }
}
