<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacilityReservation;
use App\Models\FacilityReservationPayment;
use App\Models\IssuedTicket;
use App\Models\TicketOrder;
use App\Models\TicketPayment;
use App\Models\TicketOrderVisitor;
use App\Services\OperationalReportService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(OperationalReportService $service): View
    {
        $today = CarbonImmutable::now('Asia/Jakarta');

        $start = $today->startOfDay()->utc();
        $end = $today->addDay()->startOfDay()->utc();

        // Preserve existing daily monitoring indicators.
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

        $paidItems = DB::connection('pgsql')
            ->table('ticket_order_items as items')
            ->join(
                'ticket_orders as orders',
                'orders.id',
                '=',
                'items.ticket_order_id'
            )
            ->where('orders.status', 'paid')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('ticket_payments as payments')
                    ->whereColumn(
                        'payments.ticket_order_id',
                        'orders.id'
                    )
                    ->where('payments.status', 'paid');
            });

        $soldTickets = (int) (clone $paidItems)
            ->selectRaw(
                'COALESCE(SUM(items.adult_quantity + items.child_quantity), 0) AS quantity'
            )
            ->value('quantity');

        $ticketRevenue = TicketPayment::query()
            ->where('status', 'paid')
            ->sum('amount');

        $reservationRevenue = FacilityReservationPayment::query()
            ->where('status', 'paid')
            ->sum('amount');

        // All-time operational statistics.
        $overview = [
            'total_tiket' => $soldTickets,

            'total_pengunjung' => TicketOrderVisitor::query()
                ->count(),

            'total_transaksi' => TicketOrder::query()
                ->count(),

            'total_pendapatan' => bcadd(
                (string) $ticketRevenue,
                (string) $reservationRevenue,
                2
            ),

            'total_reservasi' => FacilityReservation::query()
                ->count(),

            'total_checkin' => IssuedTicket::query()
                ->where('status', 'used')
                ->count(),
        ];

        // Operational summary for the current visit date.
        $todayTickets = IssuedTicket::query()
            ->whereHas('order', function ($query) use ($today) {
                $query->whereDate(
                    'visit_date',
                    $today->toDateString()
                )->where('status', 'paid');
            });

        $operations = [
            'pengunjung' => (clone $todayTickets)->count(),

            'checkin' => $stats['pengunjung_checkin'],

            'unused' => (clone $todayTickets)
                ->where('status', 'active')
                ->whereNull('used_at')
                ->count(),

            'pending' => TicketPayment::query()
                ->where('method', 'cash')
                ->where('status', 'pending')
                ->whereHas('order', function ($query) {
                    $query->where(function ($channel) {
                        $channel->where('sale_channel', 'online')
                            ->orWhereNull('sale_channel');
                    });
                })
                ->count(),

            'reservasi' => $stats['reservasi_hari_ini'],
        ];

        // Reuse the operational report service for WIB aggregation.
        $monthStart = $today->startOfMonth();

        $previousMonth = $today
            ->subMonthNoOverflow()
            ->startOfMonth();

        $monthlyReport = $service->generate(
            $monthStart->toDateString(),
            $today->toDateString()
        );

        $previousReport = $service->generate(
            $previousMonth->toDateString(),
            $previousMonth->endOfMonth()->toDateString()
        );

        $yearlyReport = $service->generate(
            $today->startOfYear()->toDateString(),
            $today->toDateString()
        );

        $monthlyVisitors = [];

        foreach ($yearlyReport['daily'] as $day) {
            $month = substr($day['date'], 0, 7);

            $monthlyVisitors[$month] =
                ($monthlyVisitors[$month] ?? 0)
                + $day['pengunjung_checkin'];
        }

        $popularTickets = (clone $paidItems)
            ->selectRaw(
                'items.ticket_name, SUM(items.adult_quantity + items.child_quantity) AS quantity'
            )
            ->groupBy('items.ticket_name')
            ->orderByDesc('quantity')
            ->orderBy('items.ticket_name')
            ->limit(5)
            ->get();

        $popularFacilities = FacilityReservation::query()
            ->selectRaw(
                'facility_name, COUNT(*) AS quantity'
            )
            ->groupBy('facility_name')
            ->orderByDesc('quantity')
            ->orderBy('facility_name')
            ->limit(5)
            ->get();

        $paidOrders = TicketOrder::query()
            ->where('status', 'paid')
            ->count();

        $paidReservations = FacilityReservation::query()
            ->whereHas('payment', function ($query) {
                $query->where('status', 'paid');
            })
            ->count();

        $currentRevenue = (float)
            $monthlyReport['summary']['total_pendapatan'];

        $previousRevenue = (float)
            $previousReport['summary']['total_pendapatan'];

        $revenueGrowth = $previousRevenue > 0
            ? (($currentRevenue - $previousRevenue)
                / $previousRevenue) * 100
            : null;

        return view('admin.dashboard', compact(
            'stats',
            'today',
            'overview',
            'operations',
            'monthlyReport',
            'monthlyVisitors',
            'popularTickets',
            'popularFacilities',
            'paidOrders',
            'paidReservations',
            'revenueGrowth'
        ));
    }
}
