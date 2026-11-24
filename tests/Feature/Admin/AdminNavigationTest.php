<?php

namespace Tests\Feature\Admin;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\Support\PostgresTicketTestCase;

class AdminNavigationTest extends PostgresTicketTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(
            'simticketingkrm_purchase_test',
            DB::connection()->getDatabaseName()
        );

        Http::preventStrayRequests();
    }

    private function page(
        string $routeName,
        array $parameters = []
    ): DOMXPath {
        $response = $this->actingAs(
            $this->account('admin')
        )->get(route($routeName, $parameters));

        $response->assertOk();

        $document = new DOMDocument();

        $previous = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="UTF-8">'
                .$response->getContent()
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    private function assertActiveMenu(
        string $routeName,
        string $menuRoute,
        array $parameters = []
    ): void {
        $xpath = $this->page(
            $routeName,
            $parameters
        );

        foreach ([
            'Navigasi Admin Desktop',
            'Navigasi Admin Mobile',
        ] as $label) {
            $links = $xpath->query(
                '//nav[@aria-label="'.$label.'"]'
                .'//a[@aria-current="page"]'
            );

            $this->assertSame(1, $links->length);

            $this->assertSame(
                route($menuRoute),
                $links->item(0)->getAttribute('href')
            );
        }
    }

    // NAV-01
    public function test_admin_has_both_navigation_menus(): void
    {
        $xpath = $this->page('admin.dashboard');

        $this->assertSame(
            1,
            $xpath->query(
                '//nav[@aria-label="Navigasi Admin Desktop"]'
            )->length
        );

        $this->assertSame(
            1,
            $xpath->query(
                '//nav[@aria-label="Navigasi Admin Mobile"]'
            )->length
        );
    }

    // NAV-02
    public function test_dashboard_menu_is_active(): void
    {
        $this->assertActiveMenu(
            'admin.dashboard',
            'admin.dashboard'
        );
    }

    // NAV-03
    public function test_audit_log_menu_is_active(): void
    {
        $this->assertActiveMenu(
            'admin.audit-log.index',
            'admin.audit-log.index'
        );
    }

    // NAV-04
    public function test_report_menu_is_active(): void
    {
        $this->assertActiveMenu(
            'admin.reports.index',
            'admin.reports.index'
        );
    }

    // NAV-05
    public function test_report_detail_preserves_active_menu(): void
    {
        $this->assertActiveMenu(
            'admin.reports.index',
            'admin.reports.index',
            ['view' => 'detail']
        );
    }

    // NAV-06
    public function test_report_category_preserves_active_menu(): void
    {
        $this->assertActiveMenu(
            'admin.reports.index',
            'admin.reports.index',
            ['category' => 'revenue']
        );
    }

    // NAV-07
    public function test_ticket_management_menu_is_active(): void
    {
        $this->assertActiveMenu(
            'admin.daftar-tiket.index',
            'admin.daftar-tiket.index'
        );
    }

    // NAV-08
    public function test_attraction_management_menu_is_active(): void
    {
        $this->assertActiveMenu(
            'admin.wahana.index',
            'admin.wahana.index'
        );
    }

    // NAV-09
    public function test_facility_management_menu_is_active(): void
    {
        $this->assertActiveMenu(
            'admin.fasilitas.index',
            'admin.fasilitas.index'
        );
    }

    // NAV-10
    public function test_content_menu_covers_all_content_routes(): void
    {
        foreach ([
            'admin.konten-berita.index',
            'admin.konten-galeri.index',
            'admin.konten-destinasi.index',
            'admin.konten-kontak.index',
        ] as $name) {
            $this->assertActiveMenu(
                $name,
                'admin.konten-berita.index'
            );
        }
    }

    // NAV-11
    public function test_available_navigation_links_are_valid(): void
    {
        $xpath = $this->page('admin.dashboard');

        $routes = [
            'admin.dashboard',
            'admin.daftar-tiket.index',
            'admin.wahana.index',
            'admin.reservasi.preview',
            'admin.fasilitas.index',
            'admin.konten-berita.index',
            'admin.reports.index',
            'admin.audit-log.index',
            'admin.revenue-monitoring.index',
        ];

        foreach ($routes as $name) {
            $this->assertTrue(Route::has($name));

            foreach ([
                'Navigasi Admin Desktop',
                'Navigasi Admin Mobile',
            ] as $label) {
                $links = $xpath->query(
                    '//nav[@aria-label="'.$label.'"]'
                    .'//a[@href="'.route($name).'"]'
                );

                $this->assertSame(1, $links->length);
            }
        }
    }

    // NAV-12
public function test_monitoring_pendapatan_is_available_navigation_link(): void
{
    $xpath = $this->page('admin.dashboard');

    $this->assertTrue(
        Route::has('admin.revenue-monitoring.index')
    );

    foreach ([
        'Navigasi Admin Desktop',
        'Navigasi Admin Mobile',
    ] as $label) {
        $links = $xpath->query(
            '//nav[@aria-label="'.$label.'"]'
            .'//a[@href="'.route('admin.revenue-monitoring.index').'"]'
        );

        $this->assertSame(
            1,
            $links->length,
            'Monitoring Pendapatan harus menjadi link aktif pada '.$label
        );

        $disabled = $xpath->query(
            '//nav[@aria-label="'.$label.'"]'
            .'//span[@aria-disabled="true"]'
        );

        foreach ($disabled as $node) {
            $this->assertStringNotContainsString(
                'Monitoring Pendapatan',
                $node->textContent
            );
        }
    }
}

    // NAV-13
    public function test_mobile_menu_has_accessible_structure(): void
    {
        $xpath = $this->page('admin.dashboard');

        $this->assertSame(
            1,
            $xpath->query(
                '//details[@id="admin-mobile-menu"]/summary'
            )->length
        );

        $this->assertSame(
            1,
            $xpath->query(
                '//main[@id="admin-main-content"]'
            )->length
        );

        $this->assertSame(
            1,
            $xpath->query(
                '//meta[@name="viewport"]'
            )->length
        );
    }

    // NAV-14
    public function test_logout_forms_use_post_and_csrf(): void
    {
        $xpath = $this->page('admin.dashboard');

        $forms = $xpath->query(
            '//form[@method="POST"]'
            .'[@action="'.route('logout').'"]'
        );

        $this->assertSame(2, $forms->length);

        foreach ($forms as $form) {
            $this->assertSame(
                1,
                $xpath->query(
                    './/input[@name="_token"]',
                    $form
                )->length
            );
        }
    }

    // NAV-15
    public function test_report_and_audit_tables_have_scroll_regions(): void
    {
        $audit = $this->page(
            'admin.audit-log.index'
        );

        $this->assertGreaterThanOrEqual(
            1,
            $audit->query(
                '//div[@role="region"][@tabindex="0"]'
                .'[contains(@class,"overflow-x-auto")]'
            )->length
        );

        $report = $this->page(
            'admin.reports.index',
            ['view' => 'detail']
        );

        $this->assertGreaterThanOrEqual(
            3,
            $report->query(
                '//div[@role="region"][@tabindex="0"]'
                .'[contains(@class,"overflow-x-auto")]'
            )->length
        );

        $category = $this->page(
            'admin.reports.index',
            ['category' => 'revenue']
        );

        $this->assertSame(
            1,
            $category->query(
                '//section[@aria-label="Tabel laporan kategori"]'
                .'[@tabindex="0"]'
                .'[contains(@class,"overflow-x-auto")]'
            )->length
        );
    }
}
