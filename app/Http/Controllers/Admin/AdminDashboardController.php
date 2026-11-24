<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacilityReservation;
use App\Models\IssuedTicket;
use App\Models\TicketOrder;
use App\Models\TicketPayment;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $today = CarbonImmutable::now('Asia/Jakarta');

        $start = $today->startOfDay()->utc();
        $end = $today->addDay()->startOfDay()->utc();

        $stats = [
            'transaksi_tiket' => TicketOrder::query()
                ->where('created_at', '>=', $start)
                ->where('created_at', '<', $end)
                ->count(),

            'pembayaran_lunas' => TicketPayment::query()
                ->where('status', 'paid')
                ->where('paid_at', '>=', $start)
                ->where('paid_at', '<', $end)
                ->count(),

            'pendapatan_tiket' => TicketPayment::query()
                ->where('status', 'paid')
                ->where('paid_at', '>=', $start)
                ->where('paid_at', '<', $end)
                ->sum('amount'),

            'pengunjung_checkin' => IssuedTicket::query()
                ->where('status', 'used')
                ->where('used_at', '>=', $start)
                ->where('used_at', '<', $end)
                ->count(),

            'reservasi_hari_ini' => FacilityReservation::query()
                ->whereDate('visit_date', $today->toDateString())
                ->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
