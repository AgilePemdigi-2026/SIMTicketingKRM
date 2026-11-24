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
}
