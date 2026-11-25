<?php

namespace Tests\Feature;

use App\Models\FacilityReservation;
use App\Models\FacilityReservationPayment;
use App\Models\IssuedTicket;
use App\Models\TicketOrder;
use App\Models\TicketPayment;
use App\Services\OperationalReportService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\Support\PostgresTicketTestCase;

class OperationalReportServiceTest extends PostgresTicketTestCase
{
    private const DAY = '2026-11-24';

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
            'visit_date' => self::DAY,
            'visitor_type' => 'domestik',
            'booker_name' => 'Pemesan Laporan Tes',
            'booker_identity_number' => 'TES-LAPORAN',
            'booker_phone' => '081234567890',
            'booker_email' => 'report@example.test',
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
        string $status = 'paid',
        array $overrides = []
    ): TicketPayment {
        $payment = $order->payment()->create(
            array_replace([
                'code' => (string) Str::uuid(),
                'method' => $method,
                'status' => $status,
                'amount' => $order->total,
                'paid_at' => $status === 'paid'
                    ? now('UTC')
                    : null,
            ], $overrides)
        );

        if ($payment->status === 'paid') {
            $order->update(['status' => 'paid']);
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
            'name' => 'Pengunjung Laporan Tes',
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

        return FacilityReservation::create(
            array_replace([
                'code' => (string) Str::uuid(),
                'invoice_number' => 'RSV-TEST-'
                    .Str::upper(Str::random(12)),
                'user_id' => $user->id,
                'id_fasilitas' => 1,
                'id_sesi' => 1,
                'facility_name' => 'Gazebo Uji Coba',
                'session_name' => 'Pagi',
                'visit_date' => self::DAY,
                'start_time' => '08:00:00',
                'end_time' => '10:00:00',
                'duration_minutes' => 120,
                'participant_count' => 1,
                'customer_name' => 'Pemesan Laporan Tes',
                'identity_number' => 'TES-RESERVASI',
                'phone' => '081234567890',
                'email' => 'reservation@example.test',
                'purpose' => 'Pengujian laporan',
                'additional_information' => null,
                'unit_price' => '50000.00',
                'total_amount' => '50000.00',
                'administrative_status' => 'pending',
                'capacity_hold_until' =>
                    now('UTC')->addMinutes(15),
            ], $overrides)
        );
    }

    private function createReservationPayment(
        FacilityReservation $reservation,
        string $status = 'paid',
        array $overrides = []
    ): FacilityReservationPayment {
        return $reservation->payment()->create(
            array_replace([
                'method' => 'qris',
                'status' => $status,
                'amount' => $reservation->total_amount,
                'paid_at' => $status === 'paid'
                    ? now('UTC')
                    : null,
            ], $overrides)
        );
    }

    private function report(
        string $from = self::DAY,
        string $until = self::DAY
    ): array {
        return app(OperationalReportService::class)
            ->generate($from, $until);
    }

    private function summary(): array
    {
        return $this->report()['summary'];
    }

    private function utc(string $value): CarbonImmutable
    {
        return CarbonImmutable::parse($value, 'UTC');
    }

    // ==========================================
    // BASIC REPORT CALCULATIONS
    // ==========================================

    // OR-01
    public function test_empty_report_contains_zero_values(): void
    {
        $report = $this->report();

        $this->assertSame([
            'transaksi_tiket' => 0,
            'pembayaran_lunas' => 0,
            'pendapatan_tiket' => '0.00',
            'tiket_terbit' => 0,
            'pengunjung_checkin' => 0,
            'reservasi_terjadwal' => 0,
            'reservasi_lunas' => 0,
            'pendapatan_reservasi' => '0.00',
            'total_pendapatan' => '0.00',
        ], $report['summary']);

        $this->assertCount(1, $report['daily']);

        $this->assertSame(
            self::DAY,
            $report['daily'][0]['date']
        );
    }

    // OR-02
    public function test_report_counts_ticket_orders(): void
    {
        $this->createOrder();
        $this->createOrder();
        $this->createOrder();

        $summary = $this->summary();

        $this->assertSame(
            3,
            $summary['transaksi_tiket']
        );

        $this->assertSame(
            0,
            $summary['pembayaran_lunas']
        );
    }

    // OR-03
    public function test_paid_cash_is_counted_as_ticket_revenue(): void
    {
        $this->createPayment(
            $this->createOrder(),
            'cash'
        );

        $summary = $this->summary();

        $this->assertSame(
            1,
            $summary['pembayaran_lunas']
        );

        $this->assertSame(
            '50000.00',
            $summary['pendapatan_tiket']
        );
    }

    // OR-04
    public function test_paid_qris_is_counted_as_ticket_revenue(): void
    {
        $this->createPayment(
            $this->createOrder(),
            'qris'
        );

        $summary = $this->summary();

        $this->assertSame(
            1,
            $summary['pembayaran_lunas']
        );

        $this->assertSame(
            '50000.00',
            $summary['pendapatan_tiket']
        );
    }

    // OR-05
    public function test_pending_ticket_payment_is_excluded(): void
    {
        $this->createPayment(
            $this->createOrder(),
            'cash',
            'pending',
            ['paid_at' => now('UTC')]
        );

        $summary = $this->summary();

        $this->assertSame(
            1,
            $summary['transaksi_tiket']
        );

        $this->assertSame(
            0,
            $summary['pembayaran_lunas']
        );

        $this->assertSame(
            '0.00',
            $summary['pendapatan_tiket']
        );
    }

    // OR-06
    public function test_rejected_payment_is_excluded(): void
    {
        $this->createPayment(
            $this->createOrder(),
            'cash',
            'rejected',
            ['paid_at' => now('UTC')]
        );

        $summary = $this->summary();

        $this->assertSame(
            0,
            $summary['pembayaran_lunas']
        );

        $this->assertSame(
            '0.00',
            $summary['pendapatan_tiket']
        );
    }

    // OR-07
    public function test_cash_revenue_uses_amount_not_received_cash(): void
    {
        $this->createPayment(
            $this->createOrder(),
            'cash',
            'paid',
            [
                'cash_received_amount' => '100000.00',
                'cash_change_amount' => '50000.00',
            ]
        );

        $summary = $this->summary();

        $this->assertSame(
            '50000.00',
            $summary['pendapatan_tiket']
        );

        $this->assertSame(
            '50000.00',
            $summary['total_pendapatan']
        );
    }

    // OR-08
    public function test_report_counts_issued_tickets(): void
    {
        $order = $this->createOrder();

        $this->createPayment($order);

        $this->createIssuedTicket($order);
        $this->createIssuedTicket($order);

        $summary = $this->summary();

        $this->assertSame(
            2,
            $summary['tiket_terbit']
        );

        $this->assertSame(
            1,
            $summary['pembayaran_lunas']
        );
    }

    // OR-09
    public function test_used_ticket_is_counted_as_checkin(): void
    {
        $order = $this->createOrder();

        $this->createPayment($order);

        $this->createIssuedTicket($order, 'used');

        $summary = $this->summary();

        $this->assertSame(
            1,
            $summary['pengunjung_checkin']
        );
    }

    // OR-10
    public function test_active_ticket_is_excluded_from_checkins(): void
    {
        $order = $this->createOrder();

        $this->createPayment($order);

        $this->createIssuedTicket($order, 'active');

        $summary = $this->summary();

        $this->assertSame(
            1,
            $summary['tiket_terbit']
        );

        $this->assertSame(
            0,
            $summary['pengunjung_checkin']
        );
    }

    // OR-11
    public function test_reservation_count_uses_visit_date(): void
    {
        $this->createReservation();

        $this->createReservation([
            'visit_date' => '2026-11-26',
        ]);

        $report = $this->report();

        $this->assertSame(
            1,
            $report['summary']['reservasi_terjadwal']
        );

        $this->assertSame(
            0,
            $report['summary']['reservasi_lunas']
        );

        $this->assertSame(
            1,
            $this->report(
                '2026-11-26',
                '2026-11-26'
            )['summary']['reservasi_terjadwal']
        );
    }

    // OR-12
    public function test_reservation_revenue_uses_payment_date(): void
    {
        $reservation = $this->createReservation([
            'visit_date' => '2026-11-26',
        ]);

        $this->createReservationPayment($reservation);

        $paymentDay = $this->summary();

        $visitDay = $this->report(
            '2026-11-26',
            '2026-11-26'
        )['summary'];

        $this->assertSame(
            0,
            $paymentDay['reservasi_terjadwal']
        );

        $this->assertSame(
            1,
            $paymentDay['reservasi_lunas']
        );

        $this->assertSame(
            '50000.00',
            $paymentDay['pendapatan_reservasi']
        );

        $this->assertSame(
            1,
            $visitDay['reservasi_terjadwal']
        );

        $this->assertSame(
            '0.00',
            $visitDay['pendapatan_reservasi']
        );
    }

    // OR-13
    public function test_unpaid_reservation_is_excluded_from_revenue(): void
    {
        $reservation = $this->createReservation();

        $this->createReservationPayment(
            $reservation,
            'pending',
            ['paid_at' => now('UTC')]
        );

        $summary = $this->summary();

        $this->assertSame(
            1,
            $summary['reservasi_terjadwal']
        );

        $this->assertSame(
            0,
            $summary['reservasi_lunas']
        );

        $this->assertSame(
            '0.00',
            $summary['pendapatan_reservasi']
        );
    }

    // OR-14
    public function test_report_combines_ticket_and_reservation_revenue(): void
    {
        $this->createPayment(
            $this->createOrder()
        );

        $reservation = $this->createReservation([
            'unit_price' => '75000.00',
            'total_amount' => '75000.00',
        ]);

        $this->createReservationPayment($reservation);

        $summary = $this->summary();

        $this->assertSame(
            '50000.00',
            $summary['pendapatan_tiket']
        );

        $this->assertSame(
            '75000.00',
            $summary['pendapatan_reservasi']
        );

        $this->assertSame(
            '125000.00',
            $summary['total_pendapatan']
        );
    }

    // ==========================================
    // PAYMENT METHODS AND SALES CHANNELS
    // ==========================================

    // OR-15
    public function test_cash_payment_breakdown(): void
    {
        $this->createPayment(
            $this->createOrder(),
            'cash'
        );

        $this->createPayment(
            $this->createOrder(),
            'cash'
        );

        $this->createPayment(
            $this->createOrder(),
            'qris'
        );

        $cash = $this->report()['payment_methods']['cash'];

        $this->assertSame(
            2,
            $cash['transaksi_tiket']
        );

        $this->assertSame(
            0,
            $cash['transaksi_reservasi']
        );

        $this->assertSame(
            2,
            $cash['total_transaksi']
        );

        $this->assertSame(
            '100000.00',
            $cash['total_pendapatan']
        );
    }

    // OR-16
    public function test_qris_breakdown_includes_reservation_payments(): void
    {
        $this->createPayment(
            $this->createOrder(),
            'qris'
        );

        $this->createReservationPayment(
            $this->createReservation()
        );

        $qris = $this->report()['payment_methods']['qris'];

        $this->assertSame(
            1,
            $qris['transaksi_tiket']
        );

        $this->assertSame(
            1,
            $qris['transaksi_reservasi']
        );

        $this->assertSame(
            2,
            $qris['total_transaksi']
        );

        $this->assertSame(
            '100000.00',
            $qris['total_pendapatan']
        );
    }

    // OR-17
    public function test_online_sales_channel_is_counted(): void
    {
        $this->createPayment(
            $this->createOrder([
                'sale_channel' => 'online',
            ]),
            'cash'
        );

        $channels = $this->report()['sales_channels'];

        $this->assertSame(
            1,
            $channels['online']['transaksi_lunas']
        );

        $this->assertSame(
            '50000.00',
            $channels['online']['pendapatan_tiket']
        );

        $this->assertSame(
            0,
            $channels['counter']['transaksi_lunas']
        );
    }

    // OR-18
    public function test_counter_sales_channel_is_counted(): void
    {
        $staff = $this->account('petugas');

        $order = $this->createOrder([
            'sale_channel' => 'counter',
            'staff_user_id' => $staff->id,
        ]);

        $this->createPayment($order, 'cash');

        $channels = $this->report()['sales_channels'];

        $this->assertSame(
            1,
            $channels['counter']['transaksi_lunas']
        );

        $this->assertSame(
            '50000.00',
            $channels['counter']['pendapatan_tiket']
        );

        $this->assertSame(
            0,
            $channels['online']['transaksi_lunas']
        );
    }

    // OR-19
    public function test_legacy_online_order_is_classified_as_online(): void
    {
        $order = $this->createOrder();

        // Use the default online channel for legacy-compatible data.
        $this->createPayment($order, 'qris');

        $channels = $this->report()['sales_channels'];

        $this->assertSame(
            1,
            $channels['online']['transaksi_lunas']
        );

        $this->assertSame(
            0,
            $channels['other']['transaksi_lunas']
        );
    }

    // ==========================================
    // WIB BOUNDARIES AND DAILY AGGREGATION
    // ==========================================

    // OR-20
    public function test_start_boundary_is_inclusive(): void
    {
        $timestamps = [
            '2026-11-23 16:59:59',
            '2026-11-23 17:00:00',
        ];

        foreach ($timestamps as $timestamp) {
            $order = $this->createOrder();

            $order->forceFill([
                'created_at' => $this->utc($timestamp),
            ])->save();

            $this->createPayment(
                $order,
                'cash',
                'paid',
                ['paid_at' => $this->utc($timestamp)]
            );

            $ticket = $this->createIssuedTicket(
                $order,
                'used'
            );

            $ticket->forceFill([
                'created_at' => $this->utc($timestamp),
                'used_at' => $this->utc($timestamp),
            ])->save();

            $reservation = $this->createReservation();

            $this->createReservationPayment(
                $reservation,
                'paid',
                ['paid_at' => $this->utc($timestamp)]
            );
        }

        $report = $this->report();
        $summary = $report['summary'];

        $this->assertSame(
            1,
            $summary['transaksi_tiket']
        );

        $this->assertSame(
            1,
            $summary['pembayaran_lunas']
        );

        $this->assertSame(
            1,
            $summary['tiket_terbit']
        );

        $this->assertSame(
            1,
            $summary['pengunjung_checkin']
        );

        $this->assertSame(
            1,
            $summary['reservasi_lunas']
        );

        $this->assertSame(
            '100000.00',
            $summary['total_pendapatan']
        );

        $this->assertSame(
            '2026-11-23T17:00:00+00:00',
            $report['period']['start_utc']
        );
    }

    // OR-21
    public function test_end_boundary_is_exclusive(): void
    {
        $timestamps = [
            '2026-11-24 16:59:59',
            '2026-11-24 17:00:00',
        ];

        foreach ($timestamps as $timestamp) {
            $order = $this->createOrder();

            $order->forceFill([
                'created_at' => $this->utc($timestamp),
            ])->save();

            $this->createPayment(
                $order,
                'qris',
                'paid',
                ['paid_at' => $this->utc($timestamp)]
            );

            $ticket = $this->createIssuedTicket(
                $order,
                'used'
            );

            $ticket->forceFill([
                'created_at' => $this->utc($timestamp),
                'used_at' => $this->utc($timestamp),
            ])->save();

            $this->createReservationPayment(
                $this->createReservation(),
                'paid',
                ['paid_at' => $this->utc($timestamp)]
            );
        }

        $report = $this->report();
        $summary = $report['summary'];

        $this->assertSame(
            1,
            $summary['transaksi_tiket']
        );

        $this->assertSame(
            1,
            $summary['pembayaran_lunas']
        );

        $this->assertSame(
            1,
            $summary['tiket_terbit']
        );

        $this->assertSame(
            1,
            $summary['pengunjung_checkin']
        );

        $this->assertSame(
            1,
            $summary['reservasi_lunas']
        );

        $this->assertSame(
            '100000.00',
            $summary['total_pendapatan']
        );

        $this->assertSame(
            '2026-11-24T17:00:00+00:00',
            $report['period']['end_utc_exclusive']
        );
    }

    // OR-22
    public function test_daily_aggregation_uses_wib_calendar_dates(): void
    {
        $timestamps = [
            '2026-11-24 16:59:59',
            '2026-11-24 17:00:00',
        ];

        foreach ($timestamps as $timestamp) {
            $order = $this->createOrder();

            $order->forceFill([
                'created_at' => $this->utc($timestamp),
            ])->save();

            $this->createPayment(
                $order,
                'cash',
                'paid',
                ['paid_at' => $this->utc($timestamp)]
            );

            $ticket = $this->createIssuedTicket(
                $order,
                'used'
            );

            $ticket->forceFill([
                'created_at' => $this->utc($timestamp),
                'used_at' => $this->utc($timestamp),
            ])->save();

            $this->createReservationPayment(
                $this->createReservation(),
                'paid',
                ['paid_at' => $this->utc($timestamp)]
            );
        }

        $report = $this->report(
            '2026-11-24',
            '2026-11-25'
        );

        $this->assertCount(2, $report['daily']);

        $this->assertSame(
            '2026-11-24',
            $report['daily'][0]['date']
        );

        $this->assertSame(
            '2026-11-25',
            $report['daily'][1]['date']
        );

        foreach ($report['daily'] as $day) {
            $this->assertSame(
                1,
                $day['transaksi_tiket']
            );

            $this->assertSame(
                1,
                $day['pembayaran_lunas']
            );

            $this->assertSame(
                1,
                $day['tiket_terbit']
            );

            $this->assertSame(
                1,
                $day['pengunjung_checkin']
            );

            $this->assertSame(
                1,
                $day['reservasi_lunas']
            );

            $this->assertSame(
                '100000.00',
                $day['total_pendapatan']
            );
        }
    }

    // OR-23
    public function test_daily_report_zero_fills_missing_dates(): void
    {
        $this->createPayment(
            $this->createOrder()
        );

        $report = $this->report(
            '2026-11-23',
            '2026-11-25'
        );

        $this->assertCount(3, $report['daily']);

        $this->assertSame(
            ['2026-11-23', '2026-11-24', '2026-11-25'],
            array_column($report['daily'], 'date')
        );

        $this->assertSame(
            '0.00',
            $report['daily'][0]['total_pendapatan']
        );

        $this->assertSame(
            '50000.00',
            $report['daily'][1]['total_pendapatan']
        );

        $this->assertSame(
            '0.00',
            $report['daily'][2]['total_pendapatan']
        );
    }

    // OR-24
    public function test_daily_totals_match_summary(): void
    {
        $order = $this->createOrder();

        $this->createPayment($order);

        $this->createIssuedTicket(
            $order,
            'used'
        );

        $this->createReservationPayment(
            $this->createReservation([
                'visit_date' => '2026-11-25',
            ])
        );

        $report = $this->report(
            '2026-11-23',
            '2026-11-25'
        );

        foreach ($report['summary'] as $key => $value) {
            $sum = collect($report['daily'])->sum($key);

            if (is_int($value)) {
                $this->assertSame(
                    $value,
                    (int) $sum,
                    $key
                );
            } else {
                $this->assertSame(
                    $value,
                    number_format($sum, 2, '.', ''),
                    $key
                );
            }
        }

        $this->assertSame(
            '100000.00',
            $report['summary']['total_pendapatan']
        );
    }

    // OR-25
    public function test_payment_method_totals_match_summary(): void
    {
        $this->createPayment(
            $this->createOrder(),
            'cash'
        );

        $this->createPayment(
            $this->createOrder(),
            'qris'
        );

        $this->createReservationPayment(
            $this->createReservation()
        );

        $report = $this->report();

        $methods = collect(
            $report['payment_methods']
        );

        $this->assertSame(
            3,
            $methods->sum('total_transaksi')
        );

        $this->assertSame(
            $report['summary']['total_pendapatan'],
            number_format(
                $methods->sum('total_pendapatan'),
                2,
                '.',
                ''
            )
        );

        $this->assertSame(
            '150000.00',
            $report['summary']['total_pendapatan']
        );
    }

    // OR-26
    public function test_sales_channel_totals_match_ticket_summary(): void
    {
        $this->createPayment(
            $this->createOrder()
        );

        $this->createPayment(
            $this->createOrder([
                'sale_channel' => 'counter',
                'staff_user_id' =>
                    $this->account('petugas')->id,
            ])
        );

        $report = $this->report();

        $channels = collect(
            $report['sales_channels']
        );

        $this->assertSame(
            $report['summary']['pembayaran_lunas'],
            $channels->sum('transaksi_lunas')
        );

        $this->assertSame(
            $report['summary']['pendapatan_tiket'],
            number_format(
                $channels->sum('pendapatan_tiket'),
                2,
                '.',
                ''
            )
        );

        $this->assertSame(
            '100000.00',
            $report['summary']['pendapatan_tiket']
        );
    }

    // OR-27
    public function test_decimal_money_is_summed_exactly(): void
    {
        $this->createPayment(
            $this->createOrder([
                'total' => '50000.10',
            ])
        );

        $this->createPayment(
            $this->createOrder([
                'total' => '50000.20',
            ]),
            'qris'
        );

        $this->createReservationPayment(
            $this->createReservation([
                'unit_price' => '50000.30',
                'total_amount' => '50000.30',
            ])
        );

        $report = $this->report();

        $this->assertSame(
            '100000.30',
            $report['summary']['pendapatan_tiket']
        );

        $this->assertSame(
            '50000.30',
            $report['summary']['pendapatan_reservasi']
        );

        $this->assertSame(
            '150000.60',
            $report['summary']['total_pendapatan']
        );

        $this->assertSame(
            '150000.60',
            $report['daily'][0]['total_pendapatan']
        );
    }

    // ==========================================
    // INVALID PERIOD VALIDATION
    // ==========================================

    // OR-28
    public function test_reversed_date_range_is_rejected(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Tanggal akhir tidak boleh lebih awal dari tanggal awal.'
        );

        $this->report(
            '2026-11-25',
            '2026-11-24'
        );
    }

    // OR-29
    public function test_invalid_calendar_date_is_rejected(): void
    {
        foreach ([
            '2026-02-30',
            '2026-13-01',
            '2026-1-01',
            '0000-01-01',
            'not-a-date',
        ] as $invalidDate) {
            try {
                $this->report(
                    $invalidDate,
                    '2026-11-24'
                );

                $this->fail(
                    'Invalid date accepted: '.$invalidDate
                );
            } catch (InvalidArgumentException $exception) {
                $this->assertSame(
                    'Format tanggal harus YYYY-MM-DD dan valid.',
                    $exception->getMessage()
                );
            }
        }
    }

    // OR-30
    public function test_maximum_period_is_enforced(): void
    {
        $valid = $this->report(
            '2024-01-01',
            '2024-12-31'
        );

        $this->assertSame(
            366,
            $valid['period']['days']
        );

        $this->assertCount(
            366,
            $valid['daily']
        );

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Periode laporan maksimal 366 hari.'
        );

        $this->report(
            '2024-01-01',
            '2025-01-01'
        );
    }
}
