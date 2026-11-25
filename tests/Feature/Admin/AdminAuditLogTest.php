<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Tests\Support\PostgresTicketTestCase;

class AdminAuditLogTest extends PostgresTicketTestCase
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

        // Clear only the isolated test transaction.
        // The original test database state is restored by rollback.
        AuditLog::query()->delete();
    }

    protected function tearDown(): void
    {
        $this->travelBack();

        parent::tearDown();
    }

    private function createLog(array $overrides = []): AuditLog
    {
        return AuditLog::create(array_replace([
            'user_id' => null,
            'actor_name' => 'Petugas Audit Tes',
            'actor_role' => 'petugas',
            'module' => 'Autentikasi',
            'action' => 'login',
            'description' => 'Pengguna berhasil login.',
            'subject_type' => 'User',
            'subject_id' => '123',
            'old_values' => null,
            'new_values' => null,
            'created_at' => now('UTC'),
        ], $overrides));
    }

    private function index(array $filters = [])
    {
        return $this->actingAs($this->account('admin'))
            ->get(route('admin.audit-log.index', $filters));
    }

    private function export(array $filters = [])
    {
        return $this->actingAs($this->account('admin'))
            ->get(route('admin.audit-log.export', $filters));
    }

    private function assertListedIds(
        array $expected,
        array $filters = []
    ): void {
        $response = $this->index($filters);

        $response->assertOk();

        $logs = $response->viewData('logs');

        $this->assertSame(
            $expected,
            $logs->getCollection()->pluck('id')->all()
        );

        $this->assertSame(count($expected), $logs->total());
    }

    private function boundaryLogs(): array
    {
        $timestamps = [
            '2026-11-23 16:59:59',
            '2026-11-23 17:00:00',
            '2026-11-24 16:59:59',
            '2026-11-24 17:00:00',
        ];

        $logs = [];

        foreach ($timestamps as $timestamp) {
            $logs[] = $this->createLog([
                'created_at' => CarbonImmutable::parse(
                    $timestamp,
                    'UTC'
                ),
            ]);
        }

        return $logs;
    }

    private function csvRows($response): array
    {
        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringStartsWith(
            "\xEF\xBB\xBFsep=;\r\n",
            $content
        );

        $content = substr(
            $content,
            strlen("\xEF\xBB\xBFsep=;\r\n")
        );

        $handle = fopen('php://temp', 'w+');

        fwrite($handle, $content);
        rewind($handle);

        $rows = [];

        while (
            ($row = fgetcsv($handle, 0, ';', '"', ''))
                !== false
        ) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function csvIds(array $rows): array
    {
        return array_map(
            static fn ($row) => (int) $row[0],
            array_slice($rows, 1)
        );
    }

    // ==========================================
    // ADMIN AUDIT LOG PAGE AND ACCESS CONTROL
    // ==========================================

    // AL-UI-01
    public function test_admin_can_access_audit_log(): void
    {
        $this->createLog();

        $this->index()
            ->assertOk()
            ->assertViewIs('admin.audit-log.index')
            ->assertViewHasAll([
                'stats',
                'logs',
                'filters',
                'modules',
            ])
            ->assertSee('Audit Log')
            ->assertSee('Cari Aktivitas')
            ->assertSee('Export Log');
    }

    // AL-UI-02
    public function test_petugas_cannot_access_audit_log(): void
    {
        $this->actingAs($this->account('petugas'))
            ->get(route('admin.audit-log.index'))
            ->assertForbidden();
    }

    // AL-UI-03
    public function test_wisatawan_cannot_access_audit_log(): void
    {
        $this->actingAs($this->account('wisatawan'))
            ->get(route('admin.audit-log.index'))
            ->assertForbidden();
    }

    // AL-UI-04
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.audit-log.index'))
            ->assertRedirect(route('login'));
    }

    // AL-UI-05
    public function test_statistics_match_database(): void
    {
        $this->createLog([
            'actor_role' => 'admin',
        ]);

        $this->createLog([
            'actor_role' => 'wisatawan',
        ]);

        $this->createLog([
            'actor_role' => 'system',
            'actor_name' => 'Sistem',
            'module' => 'Pembayaran',
            'action' => 'qris_payment_confirmed',
        ]);

        $response = $this->index([
            'module' => 'Pembayaran',
        ]);

        $response->assertOk();

        $stats = $response->viewData('stats');

        $this->assertSame(3, $stats['total_aktivitas']);
        $this->assertSame(3, $stats['aktivitas_hari_ini']);
        $this->assertSame(2, $stats['aktivitas_pengguna']);
        $this->assertSame(1, $stats['aktivitas_sistem']);

        // Global statistics are not affected by list filters.
        $this->assertSame(
            1,
            $response->viewData('logs')->total()
        );
    }

    // AL-UI-06
    public function test_daily_statistics_use_wib_boundaries(): void
    {
        $this->boundaryLogs();

        $response = $this->index();

        $response->assertOk();

        $stats = $response->viewData('stats');

        $this->assertSame(4, $stats['total_aktivitas']);
        $this->assertSame(2, $stats['aktivitas_hari_ini']);
    }

    // AL-UI-07
    public function test_latest_activity_is_displayed_first(): void
    {
        $older = $this->createLog([
            'created_at' => now('UTC')->subDay(),
        ]);

        $first = $this->createLog();
        $second = $this->createLog();

        $this->assertListedIds([
            $second->id,
            $first->id,
            $older->id,
        ]);
    }

    // AL-UI-08
    public function test_audit_log_paginates_ten_records(): void
    {
        for ($i = 0; $i < 13; $i++) {
            $this->createLog();
        }

        $first = $this->index();

        $first->assertOk();

        $logs = $first->viewData('logs');

        $this->assertSame(13, $logs->total());
        $this->assertSame(10, $logs->count());
        $this->assertSame(2, $logs->lastPage());

        $second = $this->index([
            'page' => 2,
        ]);

        $second->assertOk();

        $this->assertSame(
            3,
            $second->viewData('logs')->count()
        );
    }

    // AL-UI-09
    public function test_detail_displays_change_history(): void
    {
        $log = $this->createLog([
            'module' => 'Pembayaran',
            'action' => 'payment_confirmed',
            'old_values' => [
                'status' => 'pending',
            ],
            'new_values' => [
                'status' => 'paid',
                'amount' => '50000.00',
                'method' => 'cash',
                'paid_at' => '2026-11-24T05:00:00+00:00',
            ],
        ]);

        $this->actingAs($this->account('admin'))
            ->get(route('admin.audit-log.show', $log))
            ->assertOk()
            ->assertViewIs('admin.audit-log.show')
            ->assertSee('Data Sebelum')
            ->assertSee('Data Sesudah')
            ->assertSee('pending')
            ->assertSee('paid')
            ->assertSee('Rp 50.000')
            ->assertSee('24/11/2026 12:00:00 WIB');
    }

    // AL-UI-10
    public function test_missing_audit_log_returns_not_found(): void
    {
        $this->actingAs($this->account('admin'))
            ->get(route('admin.audit-log.show', [
                'auditLog' => 999999999,
            ]))
            ->assertNotFound();
    }

    // AL-UI-11
    public function test_non_admin_cannot_view_audit_log_detail(): void
    {
        $log = $this->createLog();

        foreach (['petugas', 'wisatawan'] as $role) {
            $this->actingAs($this->account($role))
                ->get(route('admin.audit-log.show', $log))
                ->assertForbidden();
        }
    }

    // AL-UI-12
    public function test_audit_log_routes_are_read_only(): void
    {
        $log = $this->createLog();

        $this->actingAs($this->account('admin'));

        $url = route('admin.audit-log.show', $log);

        foreach (['PUT', 'PATCH', 'DELETE'] as $method) {
            $this->call($method, $url, [
                'description' => 'Modified activity',
            ])->assertStatus(405);
        }

        $this->post(route('admin.audit-log.index'), [
            'action' => 'unauthorized_create',
        ])->assertStatus(405);

        $this->assertSame(
            'Pengguna berhasil login.',
            $log->fresh()->description
        );

        $this->assertSame(1, AuditLog::count());
    }

    // AL-UI-13
    public function test_detail_hides_sensitive_history_fields(): void
    {
        // Insert unsafe fixture directly to verify view-level filtering.
        $log = $this->createLog([
            'old_values' => [
                'status' => 'pending',
                'password' => 'PRIVATE-PASSWORD-FIXTURE',
            ],
            'new_values' => [
                'status' => 'paid',
                'qr_token' => 'PRIVATE-QR-FIXTURE',
                'server_key' => 'PRIVATE-SERVER-FIXTURE',
                'identity_number' => 'PRIVATE-IDENTITY-FIXTURE',
            ],
        ]);

        $this->actingAs($this->account('admin'))
            ->get(route('admin.audit-log.show', $log))
            ->assertOk()
            ->assertSee('pending')
            ->assertSee('paid')
            ->assertDontSee('PRIVATE-PASSWORD-FIXTURE')
            ->assertDontSee('PRIVATE-QR-FIXTURE')
            ->assertDontSee('PRIVATE-SERVER-FIXTURE')
            ->assertDontSee('PRIVATE-IDENTITY-FIXTURE');
    }

    // AL-UI-14
    public function test_empty_audit_log_displays_empty_state(): void
    {
        $response = $this->index();

        $response
            ->assertOk()
            ->assertSee('Tidak ada aktivitas ditemukan.')
            ->assertDontSee('Export Log');

        $stats = $response->viewData('stats');

        $this->assertSame(0, $stats['total_aktivitas']);
        $this->assertSame(0, $stats['aktivitas_hari_ini']);
        $this->assertSame(0, $stats['aktivitas_pengguna']);
        $this->assertSame(0, $stats['aktivitas_sistem']);
    }

    // ==========================================
    // SEARCH AND FILTER
    // ==========================================

    // AL-F-01
    public function test_search_by_actor_name(): void
    {
        $match = $this->createLog([
            'actor_name' => 'Petugas Mangrove',
        ]);

        $this->createLog([
            'actor_name' => 'Admin Operasional',
        ]);

        $this->assertListedIds(
            [$match->id],
            ['search' => 'Mangrove']
        );
    }

    // AL-F-02
    public function test_search_by_action_code(): void
    {
        $match = $this->createLog([
            'module' => 'Validasi Tiket',
            'action' => 'ticket_checked_in',
            'description' => 'Tiket digunakan.',
        ]);

        $this->createLog();

        $this->assertListedIds(
            [$match->id],
            ['search' => 'ticket_checked_in']
        );
    }

    // AL-F-03
    public function test_search_by_displayed_action_label(): void
    {
        $match = $this->createLog([
            'module' => 'Pembayaran',
            'action' => 'payment_confirmed',
            'description' => 'Transaksi berhasil.',
        ]);

        $this->createLog();

        $this->assertListedIds(
            [$match->id],
            ['search' => 'Konfirmasi Cash']
        );
    }

    // AL-F-04
    public function test_search_is_case_insensitive(): void
    {
        $match = $this->createLog();

        $this->createLog([
            'action' => 'logout',
            'description' => 'Pengguna berhasil logout.',
        ]);

        $this->assertListedIds(
            [$match->id],
            ['search' => 'LOGIN']
        );
    }

    // AL-F-05
    public function test_search_treats_wildcards_as_literal_text(): void
    {
        $match = $this->createLog([
            'actor_name' => 'Petugas 100%_!',
        ]);

        $this->createLog([
            'actor_name' => 'Petugas 100ABC',
        ]);

        $this->assertListedIds(
            [$match->id],
            ['search' => '%_!']
        );

        $this->assertListedIds(
            [],
            ['search' => "' OR 1=1 --"]
        );
    }

    // AL-F-06
    public function test_search_supports_zero_keyword(): void
    {
        $match = $this->createLog([
            'description' => 'Nominal 0 rupiah.',
        ]);

        $this->createLog([
            'description' => 'Aktivitas biasa.',
        ]);

        $this->assertListedIds(
            [$match->id],
            ['search' => '0']
        );
    }

    // AL-F-07
    public function test_filter_by_module(): void
    {
        $match = $this->createLog([
            'module' => 'Reservasi',
            'action' => 'reservation_created',
        ]);

        $this->createLog();

        $this->assertListedIds(
            [$match->id],
            ['module' => 'Reservasi']
        );
    }

    // AL-F-08
    public function test_filter_from_date_is_inclusive(): void
    {
        [$before, $start, $last, $next] =
            $this->boundaryLogs();

        $this->assertListedIds(
            [$next->id, $last->id, $start->id],
            ['from' => '2026-11-24']
        );
    }

    // AL-F-09
    public function test_filter_until_date_includes_entire_wib_day(): void
    {
        [$before, $start, $last, $next] =
            $this->boundaryLogs();

        $this->assertListedIds(
            [$last->id, $start->id, $before->id],
            ['until' => '2026-11-24']
        );
    }

    // AL-F-10
    public function test_date_range_uses_wib_boundaries(): void
    {
        [$before, $start, $last, $next] =
            $this->boundaryLogs();

        $this->assertListedIds(
            [$last->id, $start->id],
            [
                'from' => '2026-11-24',
                'until' => '2026-11-24',
            ]
        );
    }

    // AL-F-11
    public function test_combined_filters_use_all_conditions(): void
    {
        $match = $this->createLog([
            'actor_name' => 'Petugas Target',
            'module' => 'Pembayaran',
        ]);

        $this->createLog([
            'actor_name' => 'Petugas Target',
            'module' => 'Autentikasi',
        ]);

        $this->createLog([
            'actor_name' => 'Petugas Lain',
            'module' => 'Pembayaran',
        ]);

        $this->createLog([
            'actor_name' => 'Petugas Target',
            'module' => 'Pembayaran',
            'created_at' => now('UTC')->subDay(),
        ]);

        $this->assertListedIds(
            [$match->id],
            [
                'search' => 'Petugas Target',
                'module' => 'Pembayaran',
                'from' => '2026-11-24',
                'until' => '2026-11-24',
            ]
        );
    }

    // AL-F-12
    public function test_invalid_date_range_is_rejected(): void
    {
        $this->actingAs($this->account('admin'))
            ->from(route('admin.audit-log.index'))
            ->get(route('admin.audit-log.index', [
                'from' => '2026-11-25',
                'until' => '2026-11-24',
            ]))
            ->assertRedirect(route('admin.audit-log.index'))
            ->assertSessionHasErrors('until');

        $this->getJson(route('admin.audit-log.index', [
            'from' => 'not-a-date',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('from');
    }

    // AL-F-13
    public function test_invalid_module_is_rejected(): void
    {
        $this->actingAs($this->account('admin'))
            ->from(route('admin.audit-log.index'))
            ->get(route('admin.audit-log.index', [
                'module' => 'Unknown Module',
            ]))
            ->assertSessionHasErrors('module');
    }

    // AL-F-14
    public function test_pagination_preserves_active_filters(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->createLog([
                'actor_name' => 'Petugas Pagination',
                'module' => 'Pembayaran',
            ]);
        }

        $this->createLog([
            'module' => 'Reservasi',
        ]);

        $filters = [
            'search' => 'Pagination',
            'module' => 'Pembayaran',
            'from' => '2026-11-24',
            'until' => '2026-11-24',
        ];

        $response = $this->index($filters);

        $response->assertOk();

        $logs = $response->viewData('logs');

        $this->assertSame(12, $logs->total());

        $nextUrl = $logs->nextPageUrl();

        $this->assertNotNull($nextUrl);

        parse_str(
            parse_url($nextUrl, PHP_URL_QUERY),
            $query
        );

        foreach ($filters as $key => $value) {
            $this->assertSame($value, $query[$key]);
        }

        $this->assertSame('2', $query['page']);

        $this->get($nextUrl)
            ->assertOk()
            ->assertViewHas(
                'logs',
                fn ($logs) => $logs->count() === 2
            );
    }

    // AL-F-15
    public function test_detail_back_link_preserves_filters(): void
    {
        $log = $this->createLog();

        $filters = [
            'search' => 'login',
            'module' => 'Autentikasi',
            'from' => '2026-11-24',
            'until' => '2026-11-24',
            'page' => 2,
        ];

        $url = route(
            'admin.audit-log.show',
            array_merge([
                'auditLog' => $log->id,
            ], $filters)
        );

        $backUrl = route(
            'admin.audit-log.index',
            $filters
        );

        $this->actingAs($this->account('admin'))
            ->get($url)
            ->assertOk()
            ->assertSee('Kembali')
            ->assertSee($backUrl);
    }

    // AL-F-16
    public function test_empty_search_result_hides_export_button(): void
    {
        $this->createLog();

        $this->index([
            'search' => 'NONEXISTENT-ACTIVITY-TEST',
        ])
            ->assertOk()
            ->assertSee('Tidak ada aktivitas ditemukan.')
            ->assertSee('Coba gunakan kata pencarian')
            ->assertDontSee('Export Log')
            ->assertViewHas(
                'logs',
                fn ($logs) => $logs->total() === 0
            );
    }

    // ==========================================
    // CSV EXPORT AND SECURITY
    // ==========================================

    // AL-EX-01
    public function test_admin_can_export_audit_log(): void
    {
        $log = $this->createLog();

        $response = $this->export();

        $response
            ->assertOk()
            ->assertDownload(
                'audit-log-20261124-120000.csv'
            )
            ->assertHeader(
                'Content-Type',
                'text/csv; charset=UTF-8'
            )
            ->assertHeader(
                'X-Content-Type-Options',
                'nosniff'
            );

        $rows = $this->csvRows($response);

        $this->assertSame(
            [$log->id],
            $this->csvIds($rows)
        );

        $this->assertSame(
            '24/11/2026 12:00:00',
            $rows[1][1]
        );
    }

    // AL-EX-02
    public function test_export_contains_all_paginated_records(): void
    {
        $expected = [];

        for ($i = 0; $i < 25; $i++) {
            $expected[] = $this->createLog()->id;
        }

        $rows = $this->csvRows(
            $this->export(['page' => 2])
        );

        $this->assertCount(26, $rows);

        $this->assertSame(
            array_reverse($expected),
            $this->csvIds($rows)
        );
    }

    // AL-EX-03
    public function test_export_respects_search_keyword(): void
    {
        $match = $this->createLog([
            'actor_name' => 'Petugas Export Target',
        ]);

        $this->createLog([
            'actor_name' => 'Admin Lain',
        ]);

        $rows = $this->csvRows(
            $this->export([
                'search' => 'Export Target',
            ])
        );

        $this->assertSame(
            [$match->id],
            $this->csvIds($rows)
        );
    }

    // AL-EX-04
    public function test_export_respects_module_filter(): void
    {
        $match = $this->createLog([
            'module' => 'Reservasi',
            'action' => 'reservation_created',
        ]);

        $this->createLog();

        $rows = $this->csvRows(
            $this->export([
                'module' => 'Reservasi',
            ])
        );

        $this->assertSame(
            [$match->id],
            $this->csvIds($rows)
        );

        $this->assertSame(
            'Reservasi',
            $rows[1][4]
        );
    }

    // AL-EX-05
    public function test_export_respects_wib_date_range(): void
    {
        [$before, $start, $last, $next] =
            $this->boundaryLogs();

        $rows = $this->csvRows(
            $this->export([
                'from' => '2026-11-24',
                'until' => '2026-11-24',
            ])
        );

        $this->assertSame(
            [$last->id, $start->id],
            $this->csvIds($rows)
        );

        $this->actingAs($this->account('admin'))
            ->getJson(route('admin.audit-log.export', [
                'from' => '2026-11-25',
                'until' => '2026-11-24',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('until');
    }

    // AL-EX-06
    public function test_csv_format_and_encoding_are_valid(): void
    {
        $description = "Catatan; \"Pengujian\"\nBaris kedua";

        $this->createLog([
            'description' => $description,
            'old_values' => [
                'password' => 'PRIVATE-CSV-PASSWORD',
            ],
            'new_values' => [
                'qr_token' => 'PRIVATE-CSV-QR',
            ],
        ]);

        $rows = $this->csvRows(
            $this->export()
        );

        $this->assertSame([
            'ID Audit Log',
            'Waktu (WIB)',
            'Nama Pengguna',
            'Role',
            'Modul',
            'Aktivitas',
            'Kode Aktivitas',
            'Keterangan',
            'Jenis Objek',
            'ID Objek',
        ], $rows[0]);

        $this->assertCount(10, $rows[1]);

        $this->assertSame(
            $description,
            $rows[1][7]
        );

        $encodedRows = json_encode($rows);

        $this->assertStringNotContainsString(
            'PRIVATE-CSV-PASSWORD',
            $encodedRows
        );

        $this->assertStringNotContainsString(
            'PRIVATE-CSV-QR',
            $encodedRows
        );
    }

    // AL-EX-07
    public function test_csv_neutralizes_formula_prefixes(): void
    {
        $dangerousValues = [
            '=1+1',
            '+1+1',
            '-1+1',
            '@SUM(1)',
            '  =1+1',
            "\t=1+1",
            "\r=1+1",
            "\n=1+1",
        ];

        $expected = [];

        foreach ($dangerousValues as $value) {
            $log = $this->createLog([
                'actor_name' => $value,
                'description' => $value,
            ]);

            $expected[$log->id] = "'".$value;
        }

        $rows = $this->csvRows(
            $this->export()
        );

        $this->assertCount(
            count($dangerousValues) + 1,
            $rows
        );

        foreach (array_slice($rows, 1) as $row) {
            $id = (int) $row[0];

            $this->assertSame(
                $expected[$id],
                $row[2]
            );

            $this->assertSame(
                $expected[$id],
                $row[7]
            );
        }
    }

    // AL-EX-08
    public function test_petugas_cannot_export_audit_log(): void
    {
        $this->actingAs($this->account('petugas'))
            ->get(route('admin.audit-log.export'))
            ->assertForbidden();
    }

    // AL-EX-09
    public function test_wisatawan_cannot_export_audit_log(): void
    {
        $this->actingAs($this->account('wisatawan'))
            ->get(route('admin.audit-log.export'))
            ->assertForbidden();
    }

    // AL-EX-10
    public function test_guest_cannot_export_audit_log(): void
    {
        $this->get(route('admin.audit-log.export'))
            ->assertRedirect(route('login'));
    }
}
