<?php

namespace Tests\Feature\Admin;

use App\Models\FacilityReservation;
use App\Models\IssuedTicket;
use App\Models\TicketOrder;
use App\Models\TicketPayment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Support\PostgresTicketTestCase;

class AdminDashboardTest extends PostgresTicketTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(
            'simticketingkrm_purchase_test',
            $this->testConnection->getDatabaseName()
        );

        Http::preventStrayRequests();

        $this->travelTo(
            CarbonImmutable::parse(
                '2026-11-24 12:00:00',
                'Asia/Jakarta'
            )->utc()
        );
    }

    protected function tearDown(): void
    {
        $this->travelBack();

        parent::tearDown();
    }

    private function createOrder(
        array $overrides = []
    ): TicketOrder {
        $user = $this->account('wisatawan');

        return TicketOrder::create(array_replace([
            'code' => (string) Str::uuid(),
            'user_id' => $user->id,
            'sale_channel' => 'online',
            'visit_date' => now('Asia/Jakarta')->toDateString(),
            'visitor_type' => 'domestik',
            'booker_name' => 'Pemesan Monitoring Tes',
            'booker_identity_number' => 'TES-MONITORING',
            'booker_phone' => '081234567890',
            'booker_email' => 'monitoring@example.test',
            'total' => '50000.00',
            'status' => 'pending_payment',
            'checkout_token' => (string) Str::uuid(),
            'checkout_fingerprint' => hash(
                'sha256',
                (string) Str::uuid()
            ),
        ], $overrides));
    }

    private function createPayment(
        TicketOrder $order,
        string $method = 'cash',
        string $status = 'paid'
    ): TicketPayment {
        $payment = $order->payment()->create([
            'code' => (string) Str::uuid(),
            'method' => $method,
            'status' => $status,
            'amount' => $order->total,
            'paid_at' => $status === 'paid'
                ? now('UTC')
                : null,
        ]);

        if ($status === 'paid') {
            $order->update([
                'status' => 'paid',
            ]);
        }

        return $payment;
    }

    private function createIssuedTicket(
        TicketOrder $order,
        string $status = 'active'
    ): IssuedTicket {
        $item = $order->items()->create([
            'ticket_reference' => 'master:1',
            'ticket_name' => 'Tiket Reguler Tes',
            'ticket_category' => 'Reguler',
            'adult_quantity' => 1,
            'child_quantity' => 0,
            'adult_unit_price' => '50000.00',
            'child_unit_price' => '0.00',
            'subtotal' => '50000.00',
        ]);

        $visitor = $item->visitors()->create([
            'name' => 'Pengunjung Monitoring Tes',
            'identity_number' => 'TES-PENGUNJUNG',
            'age_category' => 'adult',
        ]);

        return IssuedTicket::create([
            'ticket_order_id' => $order->id,
            'ticket_order_item_id' => $item->id,
            'ticket_order_visitor_id' => $visitor->id,
            'ticket_code' => (string) Str::uuid(),
            'qr_token' => Str::random(64),
            'status' => $status,
            'used_at' => $status === 'used'
                ? now('UTC')
                : null,
            'checked_in_by' => $status === 'used'
                ? $this->account('petugas')->id
                : null,
        ]);
    }

    private function createReservation(
        array $overrides = []
    ): FacilityReservation {
        $user = $this->account('wisatawan');

        return FacilityReservation::create(array_replace([
            'code' => (string) Str::uuid(),
            'invoice_number' => 'RSV-TEST-'
                .Str::upper(Str::random(12)),
            'user_id' => $user->id,
            'id_fasilitas' => 1,
            'id_sesi' => 1,
            'facility_name' => 'Gazebo Uji Coba',
            'session_name' => 'Pagi',
            'visit_date' => now('Asia/Jakarta')->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'duration_minutes' => 120,
            'participant_count' => 1,
            'customer_name' => 'Pemesan Reservasi Tes',
            'identity_number' => 'TES-RESERVASI',
            'phone' => '081234567890',
            'email' => 'reservasi@example.test',
            'purpose' => 'Pengujian monitoring',
            'additional_information' => null,
            'unit_price' => '50000.00',
            'total_amount' => '50000.00',
            'administrative_status' => 'pending',
            'capacity_hold_until' => now('UTC')->addMinutes(15),
        ], $overrides));
    }

    private function dashboardStats(): array
    {
        $response = $this->actingAs(
            $this->account('admin')
        )->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas('stats');

        return $response->viewData('stats');
    }

    // AT-01: Admin dapat mengakses dashboard.
    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->account('admin'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewIs('admin.dashboard')
            ->assertViewHas('stats');
    }

    // AT-02: Statistik awal tanpa transaksi.
    public function test_dashboard_displays_zero_without_transactions(): void
    {
        $stats = $this->dashboardStats();

        $this->assertSame(0, $stats['transaksi_tiket']);
        $this->assertSame(0, $stats['pembayaran_lunas']);
        $this->assertEquals(0, $stats['pendapatan_tiket']);
        $this->assertSame(0, $stats['pengunjung_checkin']);
        $this->assertSame(0, $stats['reservasi_hari_ini']);
    }

    // AT-03: Menghitung pesanan tiket hari ini.
    public function test_dashboard_counts_ticket_orders_created_today(): void
    {
        $this->createOrder();
        $this->createOrder();

        $stats = $this->dashboardStats();

        $this->assertSame(2, $stats['transaksi_tiket']);
        $this->assertSame(0, $stats['pembayaran_lunas']);
    }

    // AT-04: Pembayaran pending tidak dihitung sebagai pendapatan.
    public function test_pending_payment_is_excluded_from_revenue(): void
    {
        $order = $this->createOrder();

        $this->createPayment(
            $order,
            'cash',
            'pending'
        );

        $stats = $this->dashboardStats();

        $this->assertSame(1, $stats['transaksi_tiket']);
        $this->assertSame(0, $stats['pembayaran_lunas']);
        $this->assertEquals(0, $stats['pendapatan_tiket']);
    }

    // AT-05: Pembayaran Cash lunas dihitung.
    public function test_paid_cash_payment_is_counted_in_revenue(): void
    {
        $order = $this->createOrder();

        $this->createPayment($order, 'cash');

        $stats = $this->dashboardStats();

        $this->assertSame(1, $stats['pembayaran_lunas']);
        $this->assertEquals(
            50000,
            $stats['pendapatan_tiket']
        );
    }

    // AT-06: Pembayaran QRIS lunas dihitung.
    public function test_paid_qris_payment_is_counted_in_revenue(): void
    {
        $order = $this->createOrder();

        $this->createPayment($order, 'qris');

        $stats = $this->dashboardStats();

        $this->assertSame(1, $stats['pembayaran_lunas']);
        $this->assertEquals(
            50000,
            $stats['pendapatan_tiket']
        );
    }

    // AT-07: Akumulasi pendapatan Cash dan QRIS.
    public function test_dashboard_accumulates_cash_and_qris_revenue(): void
    {
        $cashOrder = $this->createOrder();

        $qrisOrder = $this->createOrder([
            'total' => '75000.00',
        ]);

        $this->createPayment($cashOrder, 'cash');
        $this->createPayment($qrisOrder, 'qris');

        $stats = $this->dashboardStats();

        $this->assertSame(2, $stats['transaksi_tiket']);
        $this->assertSame(2, $stats['pembayaran_lunas']);
        $this->assertEquals(
            125000,
            $stats['pendapatan_tiket']
        );
    }

    // AT-08: E-Tiket aktif belum dihitung sebagai check-in.
    public function test_active_ticket_is_not_counted_as_checkin(): void
    {
        $order = $this->createOrder();

        $this->createPayment($order);
        $this->createIssuedTicket($order, 'active');

        $stats = $this->dashboardStats();

        $this->assertSame(0, $stats['pengunjung_checkin']);
    }

    // AT-09: E-Tiket berstatus used dihitung.
    public function test_used_ticket_is_counted_as_checkin(): void
    {
        $order = $this->createOrder();

        $this->createPayment($order);
        $this->createIssuedTicket($order, 'used');

        $stats = $this->dashboardStats();

        $this->assertSame(1, $stats['pengunjung_checkin']);
    }

    // AT-10: Reservasi hari ini dihitung.
    public function test_dashboard_counts_reservations_scheduled_today(): void
    {
        $this->createReservation();

        $stats = $this->dashboardStats();

        $this->assertSame(1, $stats['reservasi_hari_ini']);
    }

    // AT-11: Reservasi pada tanggal lain tidak dihitung.
    public function test_dashboard_excludes_reservations_on_other_dates(): void
    {
        $this->createReservation([
            'visit_date' => now('Asia/Jakarta')
                ->addDay()
                ->toDateString(),
        ]);

        $stats = $this->dashboardStats();

        $this->assertSame(0, $stats['reservasi_hari_ini']);
    }

    // AT-12: Pendapatan reservasi dipisahkan dari tiket.
    public function test_reservation_payment_is_excluded_from_ticket_revenue(): void
    {
        $reservation = $this->createReservation();

        $reservation->payment()->create([
            'method' => 'qris',
            'status' => 'paid',
            'amount' => '50000.00',
            'paid_at' => now('UTC'),
        ]);

        $stats = $this->dashboardStats();

        $this->assertSame(1, $stats['reservasi_hari_ini']);
        $this->assertSame(0, $stats['pembayaran_lunas']);
        $this->assertEquals(0, $stats['pendapatan_tiket']);
    }

    // AT-13: Wisatawan tidak dapat mengakses Dashboard Admin.
    public function test_wisatawan_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->account('wisatawan'))
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    // AT-14: Petugas tidak dapat mengakses Dashboard Admin.
    public function test_petugas_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->account('petugas'))
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    // AT-15: Guest diarahkan ke login.
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }


    // AT-16: Pesanan sebelum batas awal WIB tidak dihitung.
    public function test_order_before_wib_day_start_is_excluded(): void
    {
        $order = $this->createOrder();

        $order->forceFill([
            'created_at' => CarbonImmutable::parse(
                '2026-11-23 16:59:59',
                'UTC'
            ),
        ])->save();

        $stats = $this->dashboardStats();

        $this->assertSame(0, $stats['transaksi_tiket']);
    }

    // AT-17: Pesanan tepat pada awal hari WIB dihitung.
    public function test_order_at_wib_day_start_is_included(): void
    {
        $order = $this->createOrder();

        $order->forceFill([
            'created_at' => CarbonImmutable::parse(
                '2026-11-23 17:00:00',
                'UTC'
            ),
        ])->save();

        $stats = $this->dashboardStats();

        $this->assertSame(1, $stats['transaksi_tiket']);
    }

    // AT-18: Pesanan sebelum akhir hari WIB dihitung.
    public function test_order_before_wib_day_end_is_included(): void
    {
        $order = $this->createOrder();

        $order->forceFill([
            'created_at' => CarbonImmutable::parse(
                '2026-11-24 16:59:59',
                'UTC'
            ),
        ])->save();

        $this->travelTo(
            CarbonImmutable::parse(
                '2026-11-24 16:59:59',
                'UTC'
            )
        );

        $stats = $this->dashboardStats();

        $this->assertSame(1, $stats['transaksi_tiket']);
    }

    // AT-19: Pesanan tepat pada akhir hari WIB tidak dihitung.
    public function test_order_at_wib_day_end_is_excluded(): void
    {
        $order = $this->createOrder();

        $order->forceFill([
            'created_at' => CarbonImmutable::parse(
                '2026-11-24 17:00:00',
                'UTC'
            ),
        ])->save();

        $stats = $this->dashboardStats();

        $this->assertSame(0, $stats['transaksi_tiket']);
    }

    // AT-20: Pendapatan dihitung berdasarkan paid_at.
    public function test_payment_revenue_uses_paid_at_wib_boundaries(): void
    {
        $timestamps = [
            '2026-11-23 16:59:59',
            '2026-11-23 17:00:00',
            '2026-11-24 16:59:59',
            '2026-11-24 17:00:00',
        ];

        foreach ($timestamps as $timestamp) {
            $order = $this->createOrder();

            $order->forceFill([
                'created_at' => CarbonImmutable::parse(
                    '2026-11-23 12:00:00',
                    'UTC'
                ),
            ])->save();

            $payment = $this->createPayment(
                $order,
                'cash'
            );

            $payment->forceFill([
                'paid_at' => CarbonImmutable::parse(
                    $timestamp,
                    'UTC'
                ),
            ])->save();
        }

        $stats = $this->dashboardStats();

        $this->assertSame(0, $stats['transaksi_tiket']);
        $this->assertSame(2, $stats['pembayaran_lunas']);
        $this->assertEquals(
            100000,
            $stats['pendapatan_tiket']
        );
    }

    // AT-21: Check-in dihitung berdasarkan used_at.
    public function test_checkin_uses_used_at_wib_boundaries(): void
    {
        $timestamps = [
            '2026-11-23 16:59:59',
            '2026-11-23 17:00:00',
            '2026-11-24 16:59:59',
            '2026-11-24 17:00:00',
        ];

        foreach ($timestamps as $timestamp) {
            $order = $this->createOrder();

            $this->createPayment($order);

            $ticket = $this->createIssuedTicket(
                $order,
                'used'
            );

            $ticket->forceFill([
                'used_at' => CarbonImmutable::parse(
                    $timestamp,
                    'UTC'
                ),
            ])->save();
        }

        $stats = $this->dashboardStats();

        $this->assertSame(
            2,
            $stats['pengunjung_checkin']
        );
    }

    // AT-22: Statistik berubah mengikuti pergantian hari WIB.
    public function test_dashboard_follows_wib_date_rollover(): void
    {
        $order = $this->createOrder([
            'visit_date' => '2026-11-25',
        ]);

        $boundary = CarbonImmutable::parse(
            '2026-11-24 17:00:00',
            'UTC'
        );

        $order->forceFill([
            'created_at' => $boundary,
        ])->save();

        $payment = $this->createPayment(
            $order,
            'qris'
        );

        $payment->forceFill([
            'paid_at' => $boundary,
        ])->save();

        $ticket = $this->createIssuedTicket(
            $order,
            'used'
        );

        $ticket->forceFill([
            'used_at' => $boundary,
        ])->save();

        $this->createReservation([
            'visit_date' => '2026-11-25',
        ]);

        // Satu detik sebelum pergantian hari WIB.
        $this->travelTo(
            $boundary->subSecond()
        );

        $before = $this->dashboardStats();

        $this->assertSame(0, $before['transaksi_tiket']);
        $this->assertSame(0, $before['pembayaran_lunas']);
        $this->assertEquals(0, $before['pendapatan_tiket']);
        $this->assertSame(0, $before['pengunjung_checkin']);
        $this->assertSame(0, $before['reservasi_hari_ini']);

        // Tepat pukul 00:00 WIB pada tanggal berikutnya.
        $this->travelTo($boundary);

        $after = $this->dashboardStats();

        $this->assertSame(1, $after['transaksi_tiket']);
        $this->assertSame(1, $after['pembayaran_lunas']);
        $this->assertEquals(
            50000,
            $after['pendapatan_tiket']
        );
        $this->assertSame(1, $after['pengunjung_checkin']);
        $this->assertSame(1, $after['reservasi_hari_ini']);
    }


    // ==========================================
    // DASHBOARD ANALYTICS HELPERS
    // ==========================================

    private function dashboardAnalytics()
    {
        $response = $this->actingAs(
            $this->account('admin')
        )->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertViewIs('admin.dashboard')
            ->assertViewHasAll([
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
                'revenueGrowth',
            ]);

        return $response;
    }

    private function analyticsOrder(
        string $method = 'cash',
        string $status = 'paid',
        array $overrides = []
    ): TicketOrder {
        $order = $this->createOrder($overrides);

        $this->createPayment(
            $order,
            $method,
            $status
        );

        return $order;
    }

    private function analyticsReservationPayment(
        FacilityReservation $reservation,
        string $status = 'paid'
    ): void {
        $reservation->payment()->create([
            'method' => 'qris',
            'status' => $status,
            'amount' => $reservation->total_amount,
            'paid_at' => $status === 'paid'
                ? now('UTC')
                : null,
        ]);
    }

    // ==========================================
    // OVERALL STATISTICS
    // ==========================================

    // AT-23: Total paid ticket quantities.
    public function test_overview_counts_paid_ticket_quantities(): void
    {
        $paid = $this->analyticsOrder();

        $paid->items()->create([
            'ticket_reference' => 'master:1',
            'ticket_name' => 'Tiket Reguler Tes',
            'ticket_category' => 'Reguler',
            'adult_quantity' => 2,
            'child_quantity' => 1,
            'adult_unit_price' => '50000.00',
            'child_unit_price' => '25000.00',
            'subtotal' => '125000.00',
        ]);

        $pending = $this->analyticsOrder(
            'cash',
            'pending'
        );

        $pending->items()->create([
            'ticket_reference' => 'master:1',
            'ticket_name' => 'Tiket Reguler Tes',
            'ticket_category' => 'Reguler',
            'adult_quantity' => 4,
            'child_quantity' => 2,
            'adult_unit_price' => '50000.00',
            'child_unit_price' => '25000.00',
            'subtotal' => '250000.00',
        ]);

        $overview = $this->dashboardAnalytics()
            ->viewData('overview');

        $this->assertSame(
            3,
            $overview['total_tiket']
        );
    }

    // AT-24: Registered visitor records.
    public function test_overview_counts_registered_visitors(): void
    {
        $order = $this->analyticsOrder();

        $this->createIssuedTicket($order);
        $this->createIssuedTicket($order, 'used');

        $overview = $this->dashboardAnalytics()
            ->viewData('overview');

        $this->assertSame(
            2,
            $overview['total_pengunjung']
        );
    }

    // AT-25: All orders versus successful orders.
    public function test_overview_counts_all_ticket_orders(): void
    {
        $this->analyticsOrder();
        $this->analyticsOrder('qris');

        $this->analyticsOrder(
            'cash',
            'pending'
        );

        $response = $this->dashboardAnalytics();

        $this->assertSame(
            3,
            $response->viewData('overview')['total_transaksi']
        );

        $this->assertSame(
            2,
            $response->viewData('paidOrders')
        );
    }

    // AT-26: Combined revenue and decimal precision.
    public function test_overview_combines_paid_revenue_exactly(): void
    {
        $this->analyticsOrder(
            'cash',
            'paid',
            ['total' => '50000.25']
        );

        $this->analyticsOrder(
            'qris',
            'paid',
            ['total' => '75000.50']
        );

        $this->analyticsOrder(
            'cash',
            'pending',
            ['total' => '90000.00']
        );

        $reservation = $this->createReservation([
            'unit_price' => '25000.25',
            'total_amount' => '25000.25',
        ]);

        $this->analyticsReservationPayment(
            $reservation
        );

        $unpaidReservation = $this->createReservation();

        $this->analyticsReservationPayment(
            $unpaidReservation,
            'pending'
        );

        $overview = $this->dashboardAnalytics()
            ->viewData('overview');

        $this->assertSame(
            '150001.00',
            $overview['total_pendapatan']
        );
    }

    // AT-27: Total and paid facility reservations.
    public function test_overview_counts_all_and_paid_reservations(): void
    {
        $paid = $this->createReservation();

        $this->analyticsReservationPayment($paid);

        $this->createReservation([
            'visit_date' => '2026-11-26',
        ]);

        $response = $this->dashboardAnalytics();

        $this->assertSame(
            2,
            $response->viewData('overview')['total_reservasi']
        );

        $this->assertSame(
            1,
            $response->viewData('paidReservations')
        );
    }

    // AT-28: Historical and current check-ins.
    public function test_overview_counts_all_used_tickets(): void
    {
        $order = $this->analyticsOrder();

        $this->createIssuedTicket($order, 'active');
        $this->createIssuedTicket($order, 'used');

        $historical = $this->createIssuedTicket(
            $order,
            'used'
        );

        $historical->forceFill([
            'used_at' => CarbonImmutable::parse(
                '2026-10-20 08:00:00',
                'UTC'
            ),
        ])->save();

        $response = $this->dashboardAnalytics();

        $this->assertSame(
            2,
            $response->viewData('overview')['total_checkin']
        );

        $this->assertSame(
            1,
            $response->viewData('operations')['checkin']
        );
    }

    // ==========================================
    // DAILY OPERATIONS
    // ==========================================

    // AT-29: Today's paid visit tickets.
    public function test_operations_count_paid_visitors_today(): void
    {
        $todayOrder = $this->analyticsOrder();

        $this->createIssuedTicket($todayOrder);
        $this->createIssuedTicket($todayOrder, 'used');

        $futureOrder = $this->analyticsOrder(
            'cash',
            'paid',
            ['visit_date' => '2026-11-25']
        );

        $this->createIssuedTicket($futureOrder);

        $operations = $this->dashboardAnalytics()
            ->viewData('operations');

        $this->assertSame(
            2,
            $operations['pengunjung']
        );
    }

    // AT-30: Actual check-ins today.
    public function test_operations_count_actual_daily_checkins(): void
    {
        $order = $this->analyticsOrder();

        $this->createIssuedTicket($order, 'used');

        $previous = $this->createIssuedTicket(
            $order,
            'used'
        );

        $previous->forceFill([
            'used_at' => CarbonImmutable::parse(
                '2026-11-23 16:59:59',
                'UTC'
            ),
        ])->save();

        $operations = $this->dashboardAnalytics()
            ->viewData('operations');

        $this->assertSame(
            1,
            $operations['checkin']
        );
    }

    // AT-31: Unused active tickets for today's visits.
    public function test_operations_count_unused_tickets_today(): void
    {
        $order = $this->analyticsOrder();

        $this->createIssuedTicket($order, 'active');
        $this->createIssuedTicket($order, 'used');

        $future = $this->analyticsOrder(
            'cash',
            'paid',
            ['visit_date' => '2026-11-25']
        );

        $this->createIssuedTicket($future, 'active');

        $operations = $this->dashboardAnalytics()
            ->viewData('operations');

        $this->assertSame(
            1,
            $operations['unused']
        );
    }

    // AT-32: Pending online cash bookings only.
    public function test_operations_count_pending_online_cash(): void
    {
        $this->analyticsOrder(
            'cash',
            'pending'
        );

        $this->analyticsOrder(
            'qris',
            'pending'
        );

        $this->analyticsOrder(
            'cash',
            'paid'
        );

        $this->analyticsOrder(
            'cash',
            'pending',
            [
                'sale_channel' => 'counter',
                'staff_user_id' =>
                    $this->account('petugas')->id,
            ]
        );

        $operations = $this->dashboardAnalytics()
            ->viewData('operations');

        $this->assertSame(
            1,
            $operations['pending']
        );
    }

    // AT-33: Reservations use scheduled visit date.
    public function test_operations_count_reservations_today(): void
    {
        $this->createReservation();

        $paid = $this->createReservation();

        $this->analyticsReservationPayment($paid);

        $this->createReservation([
            'visit_date' => '2026-11-25',
        ]);

        $operations = $this->dashboardAnalytics()
            ->viewData('operations');

        $this->assertSame(
            2,
            $operations['reservasi']
        );
    }

    // ==========================================
    // DASHBOARD CHARTS AND STATISTICS
    // ==========================================

    // AT-34: Daily visitor chart includes zero days.
    public function test_analytics_daily_visitors_use_calendar_dates(): void
    {
        $order = $this->analyticsOrder();

        $this->createIssuedTicket($order, 'used');

        $earlier = $this->createIssuedTicket(
            $order,
            'used'
        );

        $earlier->forceFill([
            'used_at' => CarbonImmutable::parse(
                '2026-11-10 08:00:00',
                'UTC'
            ),
        ])->save();

        $report = $this->dashboardAnalytics()
            ->viewData('monthlyReport');

        $this->assertSame(
            '2026-11-01',
            $report['period']['from']
        );

        $this->assertSame(
            '2026-11-24',
            $report['period']['until']
        );

        $this->assertCount(
            24,
            $report['daily']
        );

        $daily = array_column(
            $report['daily'],
            'pengunjung_checkin',
            'date'
        );

        $this->assertSame(
            0,
            $daily['2026-11-01']
        );

        $this->assertSame(
            1,
            $daily['2026-11-10']
        );

        $this->assertSame(
            1,
            $daily['2026-11-24']
        );
    }

    // AT-35: Monthly visitor aggregation.
    public function test_analytics_monthly_visitors_are_grouped_correctly(): void
    {
        $order = $this->analyticsOrder();

        $this->createIssuedTicket($order, 'used');

        $october = $this->createIssuedTicket(
            $order,
            'used'
        );

        $october->forceFill([
            'used_at' => CarbonImmutable::parse(
                '2026-10-15 08:00:00',
                'UTC'
            ),
        ])->save();

        $previousYear = $this->createIssuedTicket(
            $order,
            'used'
        );

        $previousYear->forceFill([
            'used_at' => CarbonImmutable::parse(
                '2025-12-31 08:00:00',
                'UTC'
            ),
        ])->save();

        $months = $this->dashboardAnalytics()
            ->viewData('monthlyVisitors');

        $this->assertCount(11, $months);

        $this->assertSame(
            0,
            $months['2026-01']
        );

        $this->assertSame(
            1,
            $months['2026-10']
        );

        $this->assertSame(
            1,
            $months['2026-11']
        );

        $this->assertArrayNotHasKey(
            '2025-12',
            $months
        );
    }

    // AT-36: Revenue chart combines paid ticket and facility revenue.
    public function test_analytics_revenue_chart_combines_payments(): void
    {
        $this->analyticsOrder(
            'cash',
            'paid'
        );

        $reservation = $this->createReservation([
            'unit_price' => '75000.00',
            'total_amount' => '75000.00',
        ]);

        $this->analyticsReservationPayment(
            $reservation
        );

        $report = $this->dashboardAnalytics()
            ->viewData('monthlyReport');

        $daily = array_column(
            $report['daily'],
            'total_pendapatan',
            'date'
        );

        $this->assertSame(
            '125000.00',
            $daily['2026-11-24']
        );

        $this->assertSame(
            '0.00',
            $daily['2026-11-23']
        );

        $this->assertSame(
            '125000.00',
            $report['summary']['total_pendapatan']
        );
    }

    // AT-37: Paid payment method distribution.
    public function test_analytics_payment_method_distribution(): void
    {
        $this->analyticsOrder('cash');
        $this->analyticsOrder('cash');
        $this->analyticsOrder('qris');

        $this->analyticsOrder(
            'cash',
            'pending'
        );

        $this->analyticsReservationPayment(
            $this->createReservation()
        );

        $response = $this->dashboardAnalytics();

        $methods = $response
            ->viewData('monthlyReport')['payment_methods'];

        $this->assertSame(
            2,
            $methods['cash']['total_transaksi']
        );

        $this->assertSame(
            2,
            $methods['qris']['total_transaksi']
        );

        $this->assertSame(
            0,
            $methods['other']['total_transaksi']
        );

        $response->assertSee(
            'aria-label="Distribusi metode pembayaran"',
            false
        );
    }

    // AT-38: Popular ticket types use paid quantities.
    public function test_analytics_popular_tickets_use_paid_quantities(): void
    {
        $paid = $this->analyticsOrder();

        $this->createIssuedTicket($paid);
        $this->createIssuedTicket($paid);

        $second = $this->analyticsOrder(
            'qris'
        );

        $second->items()->create([
            'ticket_reference' => 'master:1',
            'ticket_name' => 'Tiket Alternatif Tes',
            'ticket_category' => 'Reguler',
            'adult_quantity' => 1,
            'child_quantity' => 0,
            'adult_unit_price' => '50000.00',
            'child_unit_price' => '0.00',
            'subtotal' => '50000.00',
        ]);

        $pending = $this->analyticsOrder(
            'cash',
            'pending'
        );

        $pending->items()->create([
            'ticket_reference' => 'master:1',
            'ticket_name' => 'Tiket Pending Tes',
            'ticket_category' => 'Reguler',
            'adult_quantity' => 10,
            'child_quantity' => 0,
            'adult_unit_price' => '50000.00',
            'child_unit_price' => '0.00',
            'subtotal' => '500000.00',
        ]);

        $tickets = $this->dashboardAnalytics()
            ->viewData('popularTickets');

        $this->assertCount(2, $tickets);

        $this->assertSame(
            'Tiket Reguler Tes',
            $tickets->first()->ticket_name
        );

        $this->assertSame(
            2,
            (int) $tickets->first()->quantity
        );

        $this->assertSame(
            'Tiket Alternatif Tes',
            $tickets->last()->ticket_name
        );

        $this->assertNotContains(
            'Tiket Pending Tes',
            $tickets->pluck('ticket_name')->all()
        );
    }

    // AT-39: Facility popularity uses all reservations.
    public function test_analytics_popular_facilities_are_ranked(): void
    {
        $this->createReservation();
        $this->createReservation();

        $this->createReservation([
            'facility_name' => 'Fasilitas Alternatif Tes',
        ]);

        $facilities = $this->dashboardAnalytics()
            ->viewData('popularFacilities');

        $this->assertCount(
            2,
            $facilities
        );

        $this->assertSame(
            'Gazebo Uji Coba',
            $facilities->first()->facility_name
        );

        $this->assertSame(
            2,
            (int) $facilities->first()->quantity
        );

        $this->assertSame(
            1,
            (int) $facilities->last()->quantity
        );
    }

    // ==========================================
    // REVENUE GROWTH
    // ==========================================

    // AT-40: Revenue growth against previous month.
    public function test_analytics_revenue_growth_is_calculated(): void
    {
        $previous = $this->createOrder([
            'total' => '50000.00',
        ]);

        $payment = $this->createPayment($previous);

        $payment->forceFill([
            'paid_at' => CarbonImmutable::parse(
                '2026-10-20 08:00:00',
                'UTC'
            ),
        ])->save();

        $this->analyticsOrder(
            'qris',
            'paid',
            ['total' => '75000.00']
        );

        $response = $this->dashboardAnalytics();

        $this->assertEqualsWithDelta(
            50.0,
            $response->viewData('revenueGrowth'),
            0.000001
        );

        $response->assertSee(
            '+50,0% dibanding bulan lalu'
        );
    }

    // AT-41: Zero previous revenue has no growth percentage.
    public function test_analytics_growth_is_null_without_previous_revenue(): void
    {
        $this->analyticsOrder();

        $response = $this->dashboardAnalytics();

        $this->assertNull(
            $response->viewData('revenueGrowth')
        );

        $response->assertSee(
            'Pendapatan tiket dan reservasi lunas'
        );
    }

    // ==========================================
    // DASHBOARD CONSISTENCY
    // ==========================================

    // AT-42: Dashboard sections and report values.
    public function test_analytics_dashboard_matches_report_data(): void
    {
        $order = $this->analyticsOrder();

        $this->createIssuedTicket(
            $order,
            'used'
        );

        $this->analyticsOrder(
            'cash',
            'pending'
        );

        $this->analyticsReservationPayment(
            $this->createReservation()
        );

        $response = $this->dashboardAnalytics();

        $report = $response->viewData(
            'monthlyReport'
        );

        $expected = app(
            \App\Services\OperationalReportService::class
        )->generate(
            '2026-11-01',
            '2026-11-24'
        );

        foreach ([
            'period',
            'summary',
            'daily',
            'payment_methods',
            'sales_channels',
        ] as $key) {
            $this->assertSame(
                $expected[$key],
                $report[$key]
            );
        }

        $this->assertSame(
            '100000.00',
            $response->viewData('overview')['total_pendapatan']
        );

        $this->assertSame(
            $response->viewData('stats')['pengunjung_checkin'],
            $response->viewData('operations')['checkin']
        );

        $response
            ->assertSee('Total Tiket Terjual')
            ->assertSee('Total Pengunjung')
            ->assertSee('Total Transaksi')
            ->assertSee('Total Pendapatan')
            ->assertSee('Total Reservasi')
            ->assertSee('Pengunjung Check-in')
            ->assertSee('Operasional Hari Ini')
            ->assertSee('Grafik & Statistik')
            ->assertSee('Pengunjung per Hari')
            ->assertSee('Pengunjung per Bulan')
            ->assertSee('Grafik Pendapatan')
            ->assertSee('Metode Pembayaran')
            ->assertSee('Jenis Tiket Terpopuler')
            ->assertSee('Reservasi Fasilitas')
            ->assertSee('Manajemen Transaksi')
            ->assertSee('Monitoring Pendapatan')
            ->assertSee(route('admin.reports.index'))
            ->assertDontSee('Pemesan Monitoring Tes')
            ->assertDontSee('TES-PENGUNJUNG');
    }
}
