<?php

namespace Tests\Feature\Wisatawan;

use App\Contracts\TicketCatalogProvider;
use App\Models\TicketOrder;
use App\Models\TicketOrderVisitor;
use App\Services\MasterTicketCatalogProvider;
use App\Support\TicketPurchaseDatabase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\PostgresTicketTestCase;

class PostgresTicketPurchaseTest extends PostgresTicketTestCase
{
    private const ENDPOINT = '/wisatawan/pembelian-tiket';

    public function test_catalog_uses_master_data_and_exclusive_routes(): void
    {
        $this->assertInstanceOf(MasterTicketCatalogProvider::class, app(TicketCatalogProvider::class));
        $this->assertTrue(Route::has('ticket-purchase.index'));
        $this->assertFalse(Route::has('ticket-local.index'));
        $this->assertFalse(Route::has('tiket.index'));
        $this->actingAs($this->account())->getJson(self::ENDPOINT)->assertOk()
            ->assertJsonPath('is_dummy', false)->assertJsonCount(3, 'tickets')
            ->assertJsonPath('tickets.0.reference', 'master:1')
            ->assertJsonPath('tickets.0.category', 'Reguler')
            ->assertJsonPath('tickets.0.rates.0.adult_price', '50000.00')
            ->assertJsonCount(1, 'tickets.0.wahana');
    }

    public function test_checkout_persists_exact_money_snapshots_and_visitors(): void
    {
        $user = $this->account();
        $payload = $this->payload();
        $payload['total'] = 1;
        $payload['status'] = 'paid';
        $payload['user_id'] = 999;
        $payload['items'][0]['adult_unit_price'] = 1;
        $response = $this->actingAs($user)->postJson(self::ENDPOINT, $payload)->assertCreated()
            ->assertJsonPath('is_dummy', false)->assertJsonPath('order.total', '30000.00')
            ->assertJsonPath('order.status', 'pending_payment')->assertJsonPath('order.user_id', $user->id)
            ->assertJsonPath('order.items.0.adult_unit_price', '20000.00')
            ->assertJsonPath('order.items.0.child_unit_price', '10000.00')
            ->assertJsonCount(2, 'order.items.0.visitors');
        $this->assertDatabaseCount('ticket_orders', 1);
        $this->assertDatabaseCount('ticket_order_items', 1);
        $this->assertDatabaseCount('ticket_order_visitors', 2);
        $this->assertSame('30000.00', TicketOrder::first()->total);
        DB::table('daftar_tiket')->where('id_tiket', 5)->update(['harga_dewasa' => '99999.00', 'nama_tiket' => 'Diubah']);
        $this->getJson(self::ENDPOINT.'/'.$response->json('order.code'))->assertOk()
            ->assertJsonPath('order.total', '30000.00')->assertJsonPath('order.items.0.ticket_name', 'Tiket Masuk Saja');
        $this->assertSame(10, DB::table('daftar_tiket')->where('id_tiket', 5)->value('kuota'));
    }

    public function test_wni_mapping_and_free_child(): void
    {
        // Negative/edge-case variation is temporary in the rolled-back test,
        // not a new replacement catalog or an edit to the group's source data.
        DB::table('daftar_tiket')->where('id_tiket', 5)->update(['harga_anak' => '0.00']);
        $payload = $this->payload();
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $payload)->assertCreated()
            ->assertJsonPath('order.total', '20000.00')->assertJsonPath('order.items.0.child_unit_price', '0.00');
    }

    public function test_mancanegara_mapping_and_decimal_total(): void
    {
        DB::table('daftar_tiket')->where('id_tiket', 5)->update([
            'kategori_wisatawan' => 'Mancanegara (WNA)',
            'harga_dewasa' => '50000.25', 'harga_anak' => '25000.50',
        ]);
        $payload = $this->payload();
        $payload['visitor_type'] = 'mancanegara';
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $payload)->assertCreated()
            ->assertJsonPath('order.total', '75000.75');
    }

    #[DataProvider('invalidPurchases')]
    public function test_invalid_purchase_is_rejected_without_writes(string $case, string $error): void
    {
        $payload = $this->payload();
        switch ($case) {
            case 'nonactive': DB::table('daftar_tiket')->where('id_tiket', 5)->update(['status_aktif' => 0]);
                break;
            case 'unknown': $payload['items'][0]['ticket_reference'] = 'master:999999';
                break;
            case 'dummy': $payload['items'][0]['ticket_reference'] = 'dummy:masuk';
                break;
            case 'wrong type': $payload['visitor_type'] = 'mancanegara';
                break;
            case 'over four': $payload['items'] = array_fill(0, 3, $payload['items'][0]);
                break;
            case 'past': $payload['visit_date'] = now('Asia/Jakarta')->subDay()->toDateString();
                break;
            case 'missing identity': unset($payload['items'][0]['visitors'][1]['identity_number']);
                break;
            case 'wrong visitors': array_pop($payload['items'][0]['visitors']);
                break;
            case 'all zero': DB::table('daftar_tiket')->where('id_tiket', 5)->update(['harga_dewasa' => 0, 'harga_anak' => 0]);
                break;
        }
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $payload)->assertUnprocessable()
            ->assertJsonValidationErrors($error);
        $this->assertDatabaseCount('ticket_orders', 0);
    }

    public static function invalidPurchases(): array
    {
        return [
            ['nonactive', 'items.0.ticket_reference'], ['unknown', 'items.0.ticket_reference'],
            ['dummy', 'items.0.ticket_reference'], ['wrong type', 'items.0.ticket_reference'],
            ['over four', 'items'], ['past', 'visit_date'],
            ['missing identity', 'items.0.visitors.1.identity_number'], ['wrong visitors', 'items.0.visitors'],
            ['all zero', 'items'],
        ];
    }

    public function test_guest_and_other_roles_cannot_checkout(): void
    {
        $this->postJson(self::ENDPOINT, $this->payload())->assertUnauthorized();
        foreach (['admin', 'petugas'] as $role) {
            $this->actingAs($this->account($role))->postJson(self::ENDPOINT, $this->payload())->assertForbidden();
        }
    }

    #[DataProvider('loginRoles')]
    public function test_existing_login_flow_works_on_postgres(string $role, string $redirect, int $checkoutStatus): void
    {
        $user = $this->account($role);
        $this->post('/login', ['login' => $user->email, 'password' => 'test-only-password'])
            ->assertRedirect($redirect);
        $this->assertAuthenticatedAs($user);
        $this->postJson(self::ENDPOINT, $this->payload())->assertStatus($checkoutStatus);
    }

    public static function loginRoles(): array
    {
        return [
            ['wisatawan', '/beranda', 201],
            ['admin', '/admin/dashboard', 403],
            ['petugas', '/petugas/dashboard', 403],
        ];
    }

    public function test_four_tickets_across_items_keep_exact_total(): void
    {
        $payload = $this->payload();
        $payload['items'][] = $payload['items'][0];
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $payload)->assertCreated()
            ->assertJsonPath('order.total', '60000.00')->assertJsonCount(2, 'order.items');
        $this->assertDatabaseCount('ticket_order_visitors', 4);
    }

    public function test_order_is_only_visible_to_its_owner(): void
    {
        $code = $this->actingAs($this->account())->postJson(self::ENDPOINT, $this->payload())
            ->assertCreated()->json('order.code');
        $this->actingAs($this->account())->getJson(self::ENDPOINT.'/'.$code)->assertNotFound();
    }

    public function test_second_visitor_failure_rolls_back_the_entire_order(): void
    {
        $inserted = 0;
        TicketOrderVisitor::creating(function () use (&$inserted) {
            if (++$inserted === 2) {
                throw new \RuntimeException('Simulated PostgreSQL visitor failure');
            }
        });
        try {
            $this->actingAs($this->account())->postJson(self::ENDPOINT, $this->payload())->assertStatus(500);
            $this->assertSame(2, $inserted);
            foreach (['ticket_orders', 'ticket_order_items', 'ticket_order_visitors'] as $table) {
                $this->assertDatabaseCount($table, 0);
            }
        } finally {
            TicketOrderVisitor::flushEventListeners();
        }
    }

    public function test_csrf_is_enforced(): void
    {
        $this->app->bind(PreventRequestForgery::class, function ($app) {
            return new class($app, $app['encrypter']) extends PreventRequestForgery
            {
                protected function runningUnitTests()
                {
                    return false;
                }
            };
        });
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $this->payload())->assertStatus(419);
        $this->withSession(['_token' => 'test-postgres-csrf'])->postJson(self::ENDPOINT,
            $this->payload(), ['X-CSRF-TOKEN' => 'test-postgres-csrf'])->assertCreated();
    }

    public function test_wrong_database_is_refused_before_any_connection(): void
    {
        config(['database.connections.pgsql.database' => 'simticketingkrm']);
        DB::shouldReceive('connection')->never();
        $this->expectException(\RuntimeException::class);
        app(TicketPurchaseDatabase::class)->connection();
    }

    public function test_connection_failure_has_no_dummy_fallback(): void
    {
        $user = $this->account();
        DB::shouldReceive('connection')->andThrow(new \PDOException('Simulated unavailable database'));
        $this->actingAs($user)->getJson(self::ENDPOINT)->assertStatus(503);
        $this->assertInstanceOf(MasterTicketCatalogProvider::class, app(TicketCatalogProvider::class));
    }
}
