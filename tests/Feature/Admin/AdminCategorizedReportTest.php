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

class AdminCategorizedReportTest extends PostgresTicketTestCase
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

    private function categoryFilters(string $category): array
    {
        return array_merge($this->customPeriod(), ['category' => $category]);
    }

    public function test_all_seven_category_pages_and_totals(): void
    {
        $this->seedReport();
        $expected = [
            'ticket-sales' => ['Total', 1, 0, 1, '50000.00'],
            'visitors' => ['Total', 1, 0, 1, 1],
            'revenue' => ['Total', '150000.00', '75000.00', '225000.00'],
            'cash' => ['Total', '', '1 transaksi', '', '50000.00'],
            'qris' => ['Total', '', '2 transaksi', '', '175000.00'],
            'reservations' => ['Total', '1 reservasi', '', 1, '', '', '75000.00', '75000.00'],
            'checkins' => ['Total', '', '', '', '1 check-in'],
        ];
        foreach ($expected as $category => $totals) {
            $response = $this->index($this->categoryFilters($category));
            $response->assertOk()->assertViewIs('admin.reports.category');
            $this->assertSame($totals, $response->viewData('report')['totals']);
            $response->assertDontSee('TES-EXPORT-ID')->assertDontSee('TES-EXPORT-VISITOR');
        }
    }

    public function test_category_index_links_and_unavailable_categories(): void
    {
        $response = $this->index($this->customPeriod());
        foreach (\App\Services\CategorizedReportService::CATEGORIES as $category => $title) {
            $response->assertSee($title);
            foreach (['admin.reports.index', 'admin.reports.export.excel', 'admin.reports.export.pdf'] as $route) {
                $response->assertSee(route($route, $this->categoryFilters($category)));
            }
        }
        $response->assertSee('Laporan Pemakaian Wahana')->assertSee('Laporan Pembatalan')->assertSee('Sumber data belum tersedia.');
    }

    public function test_all_seven_category_excel_downloads(): void
    {
        $this->seedReport();
        foreach (array_keys(\App\Services\CategorizedReportService::CATEGORIES) as $category) {
            $report = app(\App\Services\CategorizedReportService::class)->generate($category, self::DAY, self::DAY);
            $response = $this->excel($this->categoryFilters($category));
            $response->assertDownload('laporan-'.$category.'-20261124-20261124.xlsx');
            $this->withWorkbook($response, function ($book) use ($report) {
                $this->assertSame(1, $book->getSheetCount());
                $sheet = $book->getActiveSheet();
                $this->assertSame($report['title'], $sheet->getCell('A2')->getValue());
                $row = 7 + count($report['rows']);
                foreach ($report['totals'] as $i => $value) {
                    $cell = chr(65 + $i).$row;
                    if (is_numeric($value) && in_array($report['types'][$i], ['money', 'integer'], true)) {
                        $this->assertNumericCell($sheet, $cell, $value);
                    } elseif ($value !== '') {
                        $this->assertSame($value, $sheet->getCell($cell)->getValue());
                    }
                }
            });
        }
    }

    public function test_all_seven_category_pdf_downloads(): void
    {
        $this->seedReport();
        foreach (\App\Services\CategorizedReportService::CATEGORIES as $category => $title) {
            $this->assertPdfResponse($this->pdf($this->categoryFilters($category)), 'laporan-'.$category.'-20261124-20261124.pdf');
            $report = app(\App\Services\CategorizedReportService::class)->generate($category, self::DAY, self::DAY);
            $html = view('admin.reports.category-pdf', compact('report'))->render();
            $this->assertStringContainsString($title, $html);
            $this->assertStringNotContainsString('TES-EXPORT-ID', $html);
        }
    }

    public function test_unsupported_categories_are_rejected(): void
    {
        foreach (['attractions', 'cancellations', 'invalid', ['cash']] as $category) {
            $params = array_merge($this->customPeriod(), ['category' => $category]);
            $this->index($params)->assertNotFound();
            $this->excel($params)->assertNotFound();
            $this->pdf($params)->assertNotFound();
        }
    }

    public function test_category_access_control(): void
    {
        foreach (['admin.reports.index', 'admin.reports.export.excel', 'admin.reports.export.pdf'] as $route) {
            $url = route($route, $this->categoryFilters('cash'));
            foreach (['petugas', 'wisatawan'] as $role) {
                $this->actingAs($this->account($role))->get($url)->assertForbidden();
            }
        }
    }

    public function test_empty_category_and_invalid_period(): void
    {
        $params = array_merge($this->customPeriod('2020-01-01', '2020-01-03'), ['category' => 'ticket-sales']);
        $this->index($params)->assertOk()->assertSee('Tidak ada data pada periode ini.');
        $this->assertSame([], $this->index($params)->viewData('report')['rows']);
        $params['until'] = '2019-12-31';
        $this->index($params)->assertSessionHasErrors('until');
        $this->excel($params)->assertSessionHasErrors('until');
        $this->pdf($params)->assertSessionHasErrors('until');
    }
}
