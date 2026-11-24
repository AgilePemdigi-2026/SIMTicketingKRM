<?php

namespace Tests\Feature\Admin;

use App\Models\FacilityReservation;
use App\Models\IssuedTicket;
use App\Models\TicketOrder;
use App\Services\OperationalReportService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Tests\Support\PostgresTicketTestCase;

class AdminOperationalReportTest extends PostgresTicketTestCase
{
    private const DAY = '2026-11-24';

    private const METRICS = [
        'transaksi_tiket',
        'pembayaran_lunas',
        'pendapatan_tiket',
        'tiket_terbit',
        'pengunjung_checkin',
        'reservasi_terjadwal',
        'reservasi_lunas',
        'pendapatan_reservasi',
        'total_pendapatan',
    ];

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

    private function customPeriod(
        string $from = self::DAY,
        string $until = self::DAY
    ): array {
        return [
            'period' => 'custom',
            'from' => $from,
            'until' => $until,
        ];
    }

    private function index(array $filters = [])
    {
        return $this->actingAs($this->account('admin'))
            ->get(route('admin.reports.index', $filters));
    }

    private function excel(array $filters = [])
    {
        return $this->actingAs($this->account('admin'))
            ->get(route(
                'admin.reports.export.excel',
                $filters
            ));
    }

    private function pdf(array $filters = [])
    {
        return $this->actingAs($this->account('admin'))
            ->get(route(
                'admin.reports.export.pdf',
                $filters
            ));
    }

    private function report(
        string $from = self::DAY,
        string $until = self::DAY
    ): array {
        return app(OperationalReportService::class)
            ->generate($from, $until);
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
            'booker_name' => 'Pemesan Export Tes',
            'booker_identity_number' => 'TES-EXPORT-ID',
            'booker_phone' => '081234567890',
            'booker_email' => 'export@example.test',
            'total' => '50000.00',
            'status' => 'pending_payment',
            'checkout_token' => (string) Str::uuid(),
            'checkout_fingerprint' => hash(
                'sha256',
                (string) Str::uuid()
            ),
        ], $overrides));
    }

    private function createPaidOrder(
        string $amount = '50000.00',
        string $method = 'cash',
        string $channel = 'online',
        ?string $timestamp = null
    ): TicketOrder {
        $time = $timestamp === null
            ? CarbonImmutable::now('UTC')
            : CarbonImmutable::parse($timestamp, 'UTC');

        $order = $this->createOrder([
            'total' => $amount,
            'sale_channel' => $channel,
            'staff_user_id' => $channel === 'counter'
                ? $this->account('petugas')->id
                : null,
        ]);

        $order->forceFill([
            'created_at' => $time,
        ])->save();

        $order->payment()->create([
            'code' => (string) Str::uuid(),
            'method' => $method,
            'status' => 'paid',
            'amount' => $amount,
            'paid_at' => $time,
        ]);

        $order->update(['status' => 'paid']);

        return $order;
    }

    private function createUsedTicket(
        TicketOrder $order
    ): IssuedTicket {
        $item = $order->items()->create([
            'ticket_reference' => 'master:1',
            'ticket_name' => 'Tiket Reguler Tes',
            'ticket_category' => 'Reguler',
            'adult_quantity' => 1,
            'child_quantity' => 0,
            'adult_unit_price' => $order->total,
            'child_unit_price' => '0.00',
            'subtotal' => $order->total,
        ]);

        $visitor = $item->visitors()->create([
            'name' => 'Pengunjung Export Tes',
            'identity_number' => 'TES-EXPORT-VISITOR',
            'age_category' => 'adult',
        ]);

        return IssuedTicket::create([
            'ticket_order_id' => $order->id,
            'ticket_order_item_id' => $item->id,
            'ticket_order_visitor_id' => $visitor->id,
            'ticket_code' => (string) Str::uuid(),
            'qr_token' => Str::random(64),
            'status' => 'used',
            'used_at' => now('UTC'),
            'checked_in_by' => $this->account('petugas')->id,
        ]);
    }

    private function createPaidReservation(
        string $amount = '75000.00',
        string $visitDate = self::DAY
    ): FacilityReservation {
        $user = $this->account('wisatawan');

        $reservation = FacilityReservation::create([
            'code' => (string) Str::uuid(),
            'invoice_number' => 'RSV-TEST-'
                .Str::upper(Str::random(12)),
            'user_id' => $user->id,
            'id_fasilitas' => 1,
            'id_sesi' => 1,
            'facility_name' => 'Gazebo Uji Coba',
            'session_name' => 'Pagi',
            'visit_date' => $visitDate,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'duration_minutes' => 120,
            'participant_count' => 1,
            'customer_name' => 'Pemesan Export Tes',
            'identity_number' => 'TES-EXPORT-RESERVASI',
            'phone' => '081234567890',
            'email' => 'reservation-export@example.test',
            'purpose' => 'Pengujian export laporan',
            'additional_information' => null,
            'unit_price' => $amount,
            'total_amount' => $amount,
            'administrative_status' => 'pending',
            'capacity_hold_until' =>
                now('UTC')->addMinutes(15),
        ]);

        $reservation->payment()->create([
            'method' => 'qris',
            'status' => 'paid',
            'amount' => $amount,
            'paid_at' => now('UTC'),
        ]);

        return $reservation;
    }

    private function seedReport(): void
    {
        $order = $this->createPaidOrder(
            '50000.00',
            'cash',
            'counter'
        );

        $this->createUsedTicket($order);

        $this->createPaidOrder(
            '100000.00',
            'qris',
            'online'
        );

        $this->createPaidReservation(
            '75000.00'
        );

        // An unpaid order must not contribute to revenue.
        $this->createOrder();
    }

    private function workbook($response): Spreadsheet
    {
        $response->assertOk();

        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $content = $response->streamedContent();

        $this->assertStringStartsWith(
            "PK\x03\x04",
            $content
        );

        $path = tempnam(
            sys_get_temp_dir(),
            'report-xlsx-'
        );

        $this->assertNotFalse($path);

        try {
            $this->assertSame(
                strlen($content),
                file_put_contents($path, $content)
            );

            return (new Xlsx())->load($path);

        } finally {
            @unlink($path);
        }
    }

    private function withWorkbook(
        $response,
        callable $callback
    ): void {
        $workbook = $this->workbook($response);

        try {
            $callback($workbook);
        } finally {
            $workbook->disconnectWorksheets();
        }
    }

    private function assertNumericCell(
        $sheet,
        string $cell,
        int|float|string $expected
    ): void {
        $this->assertSame(
            DataType::TYPE_NUMERIC,
            $sheet->getCell($cell)->getDataType(),
            $cell
        );

        $this->assertEqualsWithDelta(
            (float) $expected,
            (float) $sheet->getCell($cell)->getValue(),
            0.000001,
            $cell
        );
    }

    private function assertPeriod(
        array $filters,
        string $from,
        string $until,
        int $days
    ): void {
        $response = $this->index($filters);

        $response->assertOk();

        $period = $response->viewData('period');

        $this->assertSame($from, $period['from']);
        $this->assertSame($until, $period['until']);
        $this->assertSame($days, $period['days']);
        $this->assertSame(
            'Asia/Jakarta',
            $period['timezone']
        );

        $daily = $response->viewData('daily');

        $this->assertCount($days, $daily);
        $this->assertSame($from, $daily[0]['date']);
        $this->assertSame(
            $until,
            $daily[$days - 1]['date']
        );
    }

    private function assertPdfResponse(
        $response,
        string $filename
    ): string {
        $response
            ->assertOk()
            ->assertDownload($filename)
            ->assertHeader(
                'Content-Type',
                'application/pdf'
            )
            ->assertHeader(
                'X-Content-Type-Options',
                'nosniff'
            );

        $content = $response->getContent();

        $this->assertIsString($content);

        $this->assertStringStartsWith(
            '%PDF-',
            $content
        );

        $this->assertStringContainsString(
            '%%EOF',
            substr($content, -1024)
        );

        return $content;
    }

    private function pdfHtml(array $report): string
    {
        return view(
            'admin.reports.pdf',
            ['report' => $report]
        )->render();
    }

    // ==========================================
    // REPORT PAGE AND ACCESS CONTROL
    // ==========================================

    // OR-UI-01
    public function test_admin_can_access_report(): void
    {
        $this->index()
            ->assertOk()
            ->assertViewIs('admin.reports.index')
            ->assertSee('Laporan Operasional');
    }

    // OR-UI-02
    public function test_petugas_cannot_access_report(): void
    {
        $this->actingAs($this->account('petugas'))
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    // OR-UI-03
    public function test_wisatawan_cannot_access_report(): void
    {
        $this->actingAs($this->account('wisatawan'))
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    // OR-UI-04
    public function test_guest_report_redirects_to_login(): void
    {
        $this->get(route('admin.reports.index'))
            ->assertRedirect(route('login'));
    }

    // OR-UI-05
    public function test_report_supplies_required_view_data(): void
    {
        $this->index()
            ->assertOk()
            ->assertViewHasAll([
                'report',
                'period',
                'summary',
                'paymentMethods',
                'salesChannels',
                'daily',
                'generatedAt',
                'filters',
            ]);
    }

    // OR-UI-06
    public function test_report_displays_operational_sections(): void
    {
        $this->index(['view' => 'detail'])
            ->assertOk()
            ->assertSee('Ringkasan Operasional')
            ->assertSee('Total Pendapatan Operasional')
            ->assertSee('Rincian Metode Pembayaran')
            ->assertSee('Rincian Kanal Penjualan Tiket')
            ->assertSee('Rekapitulasi Harian')
            ->assertSee('Catatan Perhitungan');
    }

    // OR-UI-07
    public function test_report_displays_both_charts(): void
    {
        $this->createPaidOrder();

        $this->index(['period' => 'daily', 'view' => 'detail'])
            ->assertOk()
            ->assertSee('Grafik Pendapatan Harian')
            ->assertSee('Grafik Tiket dan Kunjungan')
            ->assertSee(
                'aria-label="Grafik Pendapatan Harian"',
                false
            )
            ->assertSee(
                'aria-label="Grafik Tiket dan Kunjungan"',
                false
            );
    }

    // OR-UI-08
    public function test_empty_report_displays_zero_state(): void
    {
        $response = $this->index(
            array_merge($this->customPeriod(
                '2020-01-01',
                '2020-01-03'
            ), ['view' => 'detail'])
        );

        $response
            ->assertOk()
            ->assertSee(
                'Belum ada aktivitas operasional pada periode ini.'
            )
            ->assertSee(
                'Belum ada pendapatan pada periode ini.'
            );

        $this->assertSame(
            '0.00',
            $response->viewData('summary')['total_pendapatan']
        );

        $this->assertCount(
            3,
            $response->viewData('daily')
        );
    }

    // OR-UI-09
    public function test_export_links_preserve_displayed_dates(): void
    {
        $filters = $this->customPeriod(
            '2026-11-01',
            '2026-11-30'
        );

        $this->index($filters)
            ->assertOk()
            ->assertSee('Export Excel')
            ->assertSee('Export PDF')
            ->assertSee(route(
                'admin.reports.export.excel',
                $filters
            ))
            ->assertSee(route(
                'admin.reports.export.pdf',
                $filters
            ));
    }

    // OR-UI-10
    public function test_report_hides_private_customer_data(): void
    {
        $this->seedReport();

        $this->index(['period' => 'daily', 'view' => 'detail'])
            ->assertOk()
            ->assertDontSee('Pemesan Export Tes')
            ->assertDontSee('TES-EXPORT-ID')
            ->assertDontSee('TES-EXPORT-RESERVASI')
            ->assertDontSee('export@example.test')
            ->assertDontSee('081234567890');
    }

    // ==========================================
    // PERIOD FILTER AND VALIDATION
    // ==========================================

    // OR-F-01
    public function test_default_period_is_month_to_date(): void
    {
        $this->assertPeriod(
            [],
            '2026-11-01',
            self::DAY,
            24
        );
    }

    // OR-F-02
    public function test_daily_period_is_current_wib_day(): void
    {
        $this->assertPeriod(
            ['period' => 'daily'],
            self::DAY,
            self::DAY,
            1
        );
    }

    // OR-F-03
    public function test_weekly_period_contains_seven_days(): void
    {
        $this->assertPeriod(
            ['period' => 'weekly'],
            '2026-11-18',
            self::DAY,
            7
        );
    }

    // OR-F-04
    public function test_monthly_period_is_month_to_date(): void
    {
        $this->assertPeriod(
            ['period' => 'monthly'],
            '2026-11-01',
            self::DAY,
            24
        );
    }

    // OR-F-05
    public function test_custom_period_includes_both_dates(): void
    {
        $this->assertPeriod(
            $this->customPeriod(
                '2026-11-01',
                '2026-11-30'
            ),
            '2026-11-01',
            '2026-11-30',
            30
        );
    }

    // OR-F-06
    public function test_invalid_period_is_rejected(): void
    {
        $this->index(['period' => 'invalid'])
            ->assertRedirect(route('admin.reports.index'))
            ->assertSessionHasErrors('period');
    }

    // OR-F-07
    public function test_custom_dates_are_required(): void
    {
        $this->index(['period' => 'custom'])
            ->assertRedirect(route('admin.reports.index'))
            ->assertSessionHasErrors([
                'from',
                'until',
            ]);
    }

    // OR-F-08
    public function test_reversed_custom_dates_are_rejected(): void
    {
        $this->index(
            $this->customPeriod(
                '2026-11-30',
                '2026-11-01'
            )
        )
            ->assertRedirect(route('admin.reports.index'))
            ->assertSessionHasErrors('until');
    }

    // OR-F-09
    public function test_invalid_calendar_date_is_rejected(): void
    {
        $this->actingAs($this->account('admin'))
            ->getJson(route(
                'admin.reports.index',
                $this->customPeriod(
                    '2026-02-30',
                    '2026-03-01'
                )
            ))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('from');
    }

    // OR-F-10
    public function test_period_longer_than_366_days_is_rejected(): void
    {
        $this->actingAs($this->account('admin'))
            ->getJson(route(
                'admin.reports.index',
                $this->customPeriod(
                    '2024-01-01',
                    '2025-01-01'
                )
            ))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('until');
    }

    // OR-F-11
    public function test_period_of_366_days_is_accepted(): void
    {
        $this->assertPeriod(
            $this->customPeriod(
                '2024-01-01',
                '2024-12-31'
            ),
            '2024-01-01',
            '2024-12-31',
            366
        );
    }

    // OR-F-12
    public function test_daily_filter_uses_wib_midnight(): void
    {
        // 17:00 UTC is midnight of the next day in WIB.
        $this->travelTo(
            CarbonImmutable::parse(
                '2026-11-24 17:00:00',
                'UTC'
            )
        );

        $this->assertPeriod(
            ['period' => 'daily'],
            '2026-11-25',
            '2026-11-25',
            1
        );
    }

    // ==========================================
    // EXCEL DOWNLOAD AND WORKBOOK CONTENTS
    // ==========================================

    // OR-XLSX-01
    public function test_admin_can_download_excel(): void
    {
        $response = $this->excel(
            $this->customPeriod()
        );

        $response
            ->assertOk()
            ->assertDownload(
                'laporan-operasional-20261124-20261124.xlsx'
            )
            ->assertHeader(
                'X-Content-Type-Options',
                'nosniff'
            );

        $this->withWorkbook(
            $response,
            function (Spreadsheet $workbook) {
                $this->assertSame(
                    4,
                    $workbook->getSheetCount()
                );
            }
        );
    }

    // OR-XLSX-02
    public function test_excel_contains_four_named_worksheets(): void
    {
        $this->withWorkbook(
            $this->excel($this->customPeriod()),
            function (Spreadsheet $workbook) {
                $this->assertSame([
                    'Ringkasan',
                    'Rekap Harian',
                    'Metode Pembayaran',
                    'Kanal Penjualan',
                ], $workbook->getSheetNames());
            }
        );
    }

    // OR-XLSX-03
    public function test_excel_summary_contains_all_metrics(): void
    {
        $this->seedReport();

        $this->withWorkbook(
            $this->excel($this->customPeriod()),
            function (Spreadsheet $workbook) {
                $sheet = $workbook->getSheetByName(
                    'Ringkasan'
                );

                $this->assertSame(
                    'Indikator',
                    $sheet->getCell('A6')->getValue()
                );

                $this->assertSame(
                    'Total Pendapatan',
                    $sheet->getCell('A15')->getValue()
                );

                $expected = [
                    3,
                    2,
                    150000,
                    1,
                    1,
                    1,
                    1,
                    75000,
                    225000,
                ];

                foreach ($expected as $index => $value) {
                    $this->assertNumericCell(
                        $sheet,
                        'B'.($index + 7),
                        $value
                    );
                }
            }
        );
    }

    // OR-XLSX-04
    public function test_excel_money_is_numeric_and_formatted(): void
    {
        $this->createPaidOrder('50000.25');

        $this->withWorkbook(
            $this->excel($this->customPeriod()),
            function (Spreadsheet $workbook) {
                $sheet = $workbook->getSheetByName(
                    'Ringkasan'
                );

                $this->assertNumericCell(
                    $sheet,
                    'B15',
                    50000.25
                );

                $this->assertSame(
                    '"Rp " #,##0.00',
                    $sheet->getStyle('B15')
                        ->getNumberFormat()
                        ->getFormatCode()
                );
            }
        );
    }

    // OR-XLSX-05
    public function test_excel_daily_sheet_contains_all_dates(): void
    {
        $this->withWorkbook(
            $this->excel(
                $this->customPeriod(
                    '2026-11-01',
                    '2026-11-30'
                )
            ),
            function (Spreadsheet $workbook) {
                $sheet = $workbook->getSheetByName(
                    'Rekap Harian'
                );

                $this->assertSame(
                    37,
                    $sheet->getHighestDataRow()
                );

                for ($i = 0; $i < 30; $i++) {
                    $row = $i + 7;

                    $expected = CarbonImmutable::parse(
                        '2026-11-01',
                        'Asia/Jakarta'
                    )->addDays($i)->toDateString();

                    $cell = $sheet->getCell('A'.$row);

                    $this->assertSame(
                        DataType::TYPE_NUMERIC,
                        $cell->getDataType()
                    );

                    $actual = Date::excelToDateTimeObject(
                        (float) $cell->getValue()
                    )->format('Y-m-d');

                    $this->assertSame(
                        $expected,
                        $actual
                    );
                }

                $this->assertSame(
                    'Total',
                    $sheet->getCell('A37')->getValue()
                );
            }
        );
    }

    // OR-XLSX-06
    public function test_excel_contains_daily_table_controls(): void
    {
        $this->withWorkbook(
            $this->excel(
                $this->customPeriod(
                    '2026-11-23',
                    '2026-11-25'
                )
            ),
            function (Spreadsheet $workbook) {
                $sheet = $workbook->getSheetByName(
                    'Rekap Harian'
                );

                $this->assertSame(
                    'B7',
                    $sheet->getFreezePane()
                );

                $this->assertSame(
                    'A6:J9',
                    $sheet->getAutoFilter()->getRange()
                );

                $this->assertSame(
                    'dd/mm/yyyy',
                    $sheet->getStyle('A7')
                        ->getNumberFormat()
                        ->getFormatCode()
                );
            }
        );
    }

    // OR-XLSX-07
    public function test_excel_payment_and_channel_breakdowns(): void
    {
        $this->seedReport();

        $this->withWorkbook(
            $this->excel($this->customPeriod()),
            function (Spreadsheet $workbook) {
                $methods = $workbook->getSheetByName(
                    'Metode Pembayaran'
                );

                $channels = $workbook->getSheetByName(
                    'Kanal Penjualan'
                );

                $this->assertSame(
                    'Cash',
                    $methods->getCell('A7')->getValue()
                );

                $this->assertNumericCell(
                    $methods,
                    'G7',
                    50000
                );

                $this->assertNumericCell(
                    $methods,
                    'G8',
                    175000
                );

                $this->assertNumericCell(
                    $methods,
                    'G10',
                    225000
                );

                $this->assertNumericCell(
                    $channels,
                    'C7',
                    100000
                );

                $this->assertNumericCell(
                    $channels,
                    'C8',
                    50000
                );

                $this->assertNumericCell(
                    $channels,
                    'C10',
                    150000
                );
            }
        );
    }

    // OR-XLSX-08
    public function test_excel_empty_period_contains_zero_values(): void
    {
        $this->withWorkbook(
            $this->excel(
                $this->customPeriod(
                    '2020-01-01',
                    '2020-01-03'
                )
            ),
            function (Spreadsheet $workbook) {
                $summary = $workbook->getSheetByName(
                    'Ringkasan'
                );

                $daily = $workbook->getSheetByName(
                    'Rekap Harian'
                );

                for ($row = 7; $row <= 15; $row++) {
                    $this->assertNumericCell(
                        $summary,
                        'B'.$row,
                        0
                    );
                }

                $this->assertSame(
                    10,
                    $daily->getHighestDataRow()
                );

                $this->assertNumericCell(
                    $daily,
                    'J10',
                    0
                );
            }
        );
    }

    // OR-XLSX-09
    public function test_excel_rejects_invalid_period(): void
    {
        $this->excel(
            $this->customPeriod(
                '2026-11-30',
                '2026-11-01'
            )
        )
            ->assertRedirect(route('admin.reports.index'))
            ->assertSessionHasErrors('until');
    }

    // OR-XLSX-10
    public function test_petugas_cannot_export_excel(): void
    {
        $this->actingAs($this->account('petugas'))
            ->get(route('admin.reports.export.excel'))
            ->assertForbidden();
    }

    // OR-XLSX-11
    public function test_wisatawan_cannot_export_excel(): void
    {
        $this->actingAs($this->account('wisatawan'))
            ->get(route('admin.reports.export.excel'))
            ->assertForbidden();
    }

    // OR-XLSX-12
    public function test_guest_cannot_export_excel(): void
    {
        $this->get(route('admin.reports.export.excel'))
            ->assertRedirect(route('login'));
    }

    // ==========================================
    // PDF DOWNLOAD AND TEMPLATE CONTENTS
    // ==========================================

    // OR-PDF-01
    public function test_admin_can_download_pdf(): void
    {
        $this->assertPdfResponse(
            $this->pdf($this->customPeriod()),
            'laporan-operasional-20261124-20261124.pdf'
        );
    }

    // OR-PDF-02
    public function test_pdf_uses_selected_date_range(): void
    {
        $this->assertPdfResponse(
            $this->pdf(
                $this->customPeriod(
                    '2026-11-01',
                    '2026-11-30'
                )
            ),
            'laporan-operasional-20261101-20261130.pdf'
        );
    }

    // OR-PDF-03
    public function test_pdf_supports_daily_weekly_monthly_presets(): void
    {
        $cases = [
            'daily' =>
                'laporan-operasional-20261124-20261124.pdf',

            'weekly' =>
                'laporan-operasional-20261118-20261124.pdf',

            'monthly' =>
                'laporan-operasional-20261101-20261124.pdf',
        ];

        foreach ($cases as $period => $filename) {
            $this->assertPdfResponse(
                $this->pdf(['period' => $period]),
                $filename
            );
        }
    }

    // OR-PDF-04
    public function test_pdf_template_displays_all_sections(): void
    {
        $this->seedReport();

        $html = $this->pdfHtml($this->report());

        foreach ([
            'KEBUN RAYA MANGROVE SURABAYA',
            'BRIDA Kota Surabaya',
            'LAPORAN OPERASIONAL',
            'Total Pendapatan Operasional',
            'I. Ringkasan Operasional',
            'II. Rincian Metode Pembayaran',
            'III. Rincian Kanal Penjualan Tiket',
            'IV. Rekapitulasi Harian',
            'CATATAN PERHITUNGAN',
        ] as $text) {
            $this->assertStringContainsString(
                $text,
                $html
            );
        }
    }

    // OR-PDF-05
    public function test_pdf_template_contains_correct_revenue(): void
    {
        $this->seedReport();

        $html = $this->pdfHtml($this->report());

        $this->assertStringContainsString(
            'Rp 150.000,00',
            $html
        );

        $this->assertStringContainsString(
            'Rp 75.000,00',
            $html
        );

        $this->assertStringContainsString(
            'Rp 225.000,00',
            $html
        );
    }

    // OR-PDF-06
    public function test_pdf_template_contains_all_daily_dates(): void
    {
        $report = $this->report(
            '2026-11-01',
            '2026-11-30'
        );

        $html = $this->pdfHtml($report);

        foreach ($report['daily'] as $day) {
            $date = CarbonImmutable::parse(
                $day['date']
            )->format('d/m/Y');

            $this->assertStringContainsString(
                $date,
                $html
            );
        }

        $this->assertSame(
            2,
            substr_count(
                $html,
                '<div class="page-break">'
            )
        );
    }

    // OR-PDF-07
    public function test_pdf_supports_empty_period(): void
    {
        $filters = $this->customPeriod(
            '2020-01-01',
            '2020-01-03'
        );

        $this->assertPdfResponse(
            $this->pdf($filters),
            'laporan-operasional-20200101-20200103.pdf'
        );

        $html = $this->pdfHtml(
            $this->report(
                '2020-01-01',
                '2020-01-03'
            )
        );

        $this->assertStringContainsString(
            'Belum ada aktivitas operasional pada periode ini.',
            $html
        );

        $this->assertStringContainsString(
            'Rp 0,00',
            $html
        );
    }

    // OR-PDF-08
    public function test_pdf_rejects_invalid_dates(): void
    {
        $this->pdf(
            $this->customPeriod(
                '2026-02-30',
                '2026-03-01'
            )
        )
            ->assertRedirect(route('admin.reports.index'))
            ->assertSessionHasErrors('from');
    }

    // OR-PDF-09
    public function test_pdf_rejects_period_over_366_days(): void
    {
        $this->actingAs($this->account('admin'))
            ->getJson(route(
                'admin.reports.export.pdf',
                $this->customPeriod(
                    '2024-01-01',
                    '2025-01-01'
                )
            ))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('until');
    }

    // OR-PDF-10
    public function test_petugas_cannot_export_pdf(): void
    {
        $this->actingAs($this->account('petugas'))
            ->get(route('admin.reports.export.pdf'))
            ->assertForbidden();
    }

    // OR-PDF-11
    public function test_wisatawan_cannot_export_pdf(): void
    {
        $this->actingAs($this->account('wisatawan'))
            ->get(route('admin.reports.export.pdf'))
            ->assertForbidden();
    }

    // OR-PDF-12
    public function test_guest_cannot_export_pdf(): void
    {
        $this->get(route('admin.reports.export.pdf'))
            ->assertRedirect(route('login'));
    }

    // ==========================================
    // REPORT AND EXPORT CONSISTENCY
    // ==========================================

    // OR-C-01
    public function test_report_view_matches_database_service(): void
    {
        $this->seedReport();

        $expected = $this->report();

        $response = $this->index(
            $this->customPeriod()
        );

        $response->assertOk();

        foreach ([
            'summary' => 'summary',
            'daily' => 'daily',
            'paymentMethods' => 'payment_methods',
            'salesChannels' => 'sales_channels',
            'period' => 'period',
        ] as $viewKey => $serviceKey) {
            $this->assertSame(
                $expected[$serviceKey],
                $response->viewData($viewKey)
            );
        }

        $this->assertSame(
            '225000.00',
            $response->viewData('summary')['total_pendapatan']
        );
    }

    // OR-C-02
    public function test_excel_daily_and_summary_totals_match_service(): void
    {
        $this->seedReport();

        $filters = $this->customPeriod(
            '2026-11-23',
            '2026-11-25'
        );

        $report = $this->report(
            $filters['from'],
            $filters['until']
        );

        $this->withWorkbook(
            $this->excel($filters),
            function (Spreadsheet $workbook) use ($report) {
                $summary = $workbook->getSheetByName(
                    'Ringkasan'
                );

                $daily = $workbook->getSheetByName(
                    'Rekap Harian'
                );

                foreach (self::METRICS as $index => $key) {
                    $expected = $report['summary'][$key];

                    $this->assertNumericCell(
                        $summary,
                        'B'.($index + 7),
                        $expected
                    );

                    $column = chr(66 + $index);

                    $this->assertNumericCell(
                        $daily,
                        $column.'10',
                        $expected
                    );

                    $sum = 0;

                    foreach ($report['daily'] as $i => $day) {
                        $cell = $column.($i + 7);

                        $this->assertNumericCell(
                            $daily,
                            $cell,
                            $day[$key]
                        );

                        $sum += (float) $daily
                            ->getCell($cell)
                            ->getValue();
                    }

                    $this->assertEqualsWithDelta(
                        (float) $expected,
                        $sum,
                        0.000001
                    );
                }
            }
        );
    }

    // OR-C-03
    public function test_export_template_and_excel_share_decimal_totals(): void
    {
        $this->createPaidOrder('50000.25');

        $this->createPaidReservation('75000.50');

        $report = $this->report();

        $this->assertSame(
            '125000.75',
            $report['summary']['total_pendapatan']
        );

        $this->withWorkbook(
            $this->excel($this->customPeriod()),
            function (Spreadsheet $workbook) {
                $this->assertNumericCell(
                    $workbook->getSheetByName('Ringkasan'),
                    'B15',
                    125000.75
                );

                $this->assertNumericCell(
                    $workbook->getSheetByName('Rekap Harian'),
                    'J8',
                    125000.75
                );

                $this->assertNumericCell(
                    $workbook->getSheetByName('Metode Pembayaran'),
                    'G10',
                    125000.75
                );
            }
        );

        $this->assertStringContainsString(
            'Rp 125.000,75',
            $this->pdfHtml($report)
        );
    }

    // OR-C-04
    public function test_exports_exclude_private_customer_data(): void
    {
        $this->seedReport();

        $privateValues = [
            'Pemesan Export Tes',
            'TES-EXPORT-ID',
            'TES-EXPORT-RESERVASI',
            'TES-EXPORT-VISITOR',
            'export@example.test',
            '081234567890',
        ];

        $this->withWorkbook(
            $this->excel($this->customPeriod()),
            function (Spreadsheet $workbook) use ($privateValues) {
                $cells = [];

                foreach ($workbook->getAllSheets() as $sheet) {
                    foreach ($sheet->toArray() as $row) {
                        foreach ($row as $value) {
                            if (is_scalar($value)) {
                                $cells[] = (string) $value;
                            }
                        }
                    }
                }

                $content = implode("\n", $cells);

                foreach ($privateValues as $value) {
                    $this->assertStringNotContainsString(
                        $value,
                        $content
                    );
                }
            }
        );

        $html = $this->pdfHtml($this->report());

        foreach ($privateValues as $value) {
            $this->assertStringNotContainsString(
                $value,
                $html
            );
        }
    }
}
