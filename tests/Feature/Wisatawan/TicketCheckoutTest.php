<?php

namespace Tests\Feature\Wisatawan;

use App\Models\LocalDummyTicket;
use App\Models\LocalDummyTicketRate;
use App\Models\TicketOrder;
use App\Models\TicketOrderVisitor;
use App\Models\User;
use Database\Seeders\LocalTicketSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LocalTicketTestCase;

class TicketCheckoutTest extends LocalTicketTestCase
{
    private const ENDPOINT = '/wisatawan/pembelian-tiket';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLocalDatabase();
    }

    public function test_guest_cannot_checkout(): void
    {
        $this->postJson(self::ENDPOINT, $this->purchasePayload())->assertUnauthorized();
        $this->assertDatabaseCount('ticket_orders', 0);
    }

    #[DataProvider('roles')]
    public function test_real_login_and_role_access(string $role, string $email, string $redirect, int $status): void
    {
        $this->post('/login', [
            'login' => $email, 'password' => config("ticket_purchase.passwords.{$role}"),
        ])->assertRedirect($redirect);
        $this->assertAuthenticated();
        $this->postJson(self::ENDPOINT, $this->purchasePayload())->assertStatus($status);
    }

    public static function roles(): array
    {
        return [
            ['wisatawan', 'tester.wisatawan@example.com', '/beranda', 201],
            ['admin', 'admin.testing@example.com', '/admin/dashboard', 403],
            ['petugas', 'petugas.testing@example.com', '/petugas/dashboard', 403],
        ];
    }

    public function test_catalog_exposes_only_active_dummy_tickets(): void
    {
        $this->actingAs($this->tourist())->getJson(self::ENDPOINT)
            ->assertOk()->assertJsonPath('is_dummy', true)->assertJsonCount(2, 'tickets')
            ->assertJsonMissing(['reference' => 'dummy:nonaktif']);
    }

    #[DataProvider('quantities')]
    public function test_checkout_accepts_one_to_four_tickets(int $quantity): void
    {
        $payload = $this->purchasePayload();
        $payload['items'][0]['adult_quantity'] = $quantity;
        $payload['items'][0]['child_quantity'] = 0;
        $payload['items'][0]['visitors'] = array_fill(0, $quantity, $payload['items'][0]['visitors'][0]);
        $this->actingAs($this->tourist())->postJson(self::ENDPOINT, $payload)
            ->assertCreated()->assertJsonPath('order.total', $quantity * 25000);
        $this->assertDatabaseCount('ticket_order_visitors', $quantity);
    }

    public static function quantities(): array
    {
        return [[1], [2], [3], [4]];
    }

    #[DataProvider('visitorTypes')]
    public function test_prices_snapshots_and_ownership_ignore_browser_values(string $type, int $expected): void
    {
        $user = $this->tourist();
        $payload = $this->purchasePayload();
        $payload['visitor_type'] = $type;
        $payload['user_id'] = 1;
        $payload['status'] = 'paid';
        $payload['total'] = 1;
        $payload['items'][0]['adult_unit_price'] = 1;
        $payload['items'][0]['subtotal'] = 1;
        $payload['items'][] = $payload['items'][0];
        $payload['items'][1]['ticket_reference'] = 'dummy:wahana';

        $response = $this->actingAs($user)->postJson(self::ENDPOINT, $payload)
            ->assertCreated()->assertJsonPath('order.total', $expected)
            ->assertJsonPath('order.status', 'pending_payment')
            ->assertJsonPath('order.user_id', $user->id)
            ->assertJsonPath('order.booker_identity_number', 'BOOKER-DEMO')
            ->assertJsonCount(2, 'order.items');
        $this->assertDatabaseCount('ticket_orders', 1);
        $this->assertDatabaseCount('ticket_order_items', 2);
        $this->assertDatabaseCount('ticket_order_visitors', 4);
        $this->assertSame('Wisatawan Demo', $user->fresh()->name);
        $this->assertSame('domestik', $user->fresh()->visitor_type);
        $this->assertSame($user->id, TicketOrder::first()->user->id);

        LocalDummyTicketRate::query()->update(['adult_price' => 99999]);
        LocalDummyTicket::query()->update(['name' => '[DUMMY] Nama Baru']);
        $this->getJson(self::ENDPOINT.'/'.$response->json('order.code'))
            ->assertOk()->assertJsonPath('order.total', $expected)
            ->assertJsonPath('order.items.0.ticket_name', '[DUMMY] Tiket Masuk');
    }

    public static function visitorTypes(): array
    {
        return [['domestik', 70000], ['mancanegara', 140000]];
    }

    #[DataProvider('invalidPayloads')]
    public function test_incomplete_or_inconsistent_checkout_is_rejected(string $case, string $error): void
    {
        $payload = $this->purchasePayload();
        switch ($case) {
            case 'missing booker': unset($payload['booker']);
                break;
            case 'bad email': $payload['booker']['email'] = 'wrong';
                break;
            case 'bad phone': $payload['booker']['phone'] = 'abcdefghi';
                break;
            case 'missing identity': unset($payload['items'][0]['visitors'][1]['identity_number']);
                break;
            case 'wrong age': $payload['items'][0]['visitors'][1]['age_category'] = 'adult';
                break;
            case 'missing visitor': array_pop($payload['items'][0]['visitors']);
                break;
            case 'no reference': unset($payload['items'][0]['ticket_reference']);
                break;
            case 'bad type': $payload['visitor_type'] = 'other';
                break;
            case 'past date': $payload['visit_date'] = '2026-10-20';
                break;
            case 'zero': $payload['items'][0]['adult_quantity'] = $payload['items'][0]['child_quantity'] = 0;
                break;
            case 'over four': $payload['items'] = array_fill(0, 3, $payload['items'][0]);
                break;
        }
        $this->actingAs($this->tourist())->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()->assertJsonValidationErrors($error);
        $this->assertDatabaseCount('ticket_orders', 0);
    }

    public static function invalidPayloads(): array
    {
        return [
            ['missing booker', 'booker.name'], ['bad email', 'booker.email'], ['bad phone', 'booker.phone'],
            ['missing identity', 'items.0.visitors.1.identity_number'], ['wrong age', 'items.0.visitors'],
            ['missing visitor', 'items.0.visitors'], ['no reference', 'items.0.ticket_reference'],
            ['bad type', 'visitor_type'], ['past date', 'visit_date'], ['zero', 'items'], ['over four', 'items'],
        ];
    }

    #[DataProvider('unavailableTickets')]
    public function test_unavailable_tickets_or_tariffs_do_not_create_orders(string $reference, bool $removeTariff): void
    {
        if ($removeTariff) {
            LocalDummyTicketRate::where('visitor_type', 'domestik')->delete();
        }
        $payload = $this->purchasePayload();
        $payload['items'][0]['ticket_reference'] = $reference;
        $this->actingAs($this->tourist())->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('items.0.ticket_reference');
        $this->assertDatabaseCount('ticket_orders', 0);
    }

    public static function unavailableTickets(): array
    {
        return [['dummy:nonaktif', false], ['dummy:missing', false], ['dummy:masuk', true]];
    }

    public function test_insert_failure_rolls_back_header_items_and_visitors(): void
    {
        $created = 0;
        TicketOrderVisitor::creating(function () use (&$created) {
            if (++$created === 2) {
                throw new \RuntimeException('Simulated second visitor failure');
            }
        });
        try {
            $this->actingAs($this->tourist())->postJson(self::ENDPOINT, $this->purchasePayload())
                ->assertStatus(500);
            $this->assertSame(2, $created);
            foreach (['ticket_orders', 'ticket_order_items', 'ticket_order_visitors'] as $table) {
                $this->assertDatabaseCount($table, 0);
            }
        } finally {
            TicketOrderVisitor::flushEventListeners();
        }
    }

    public function test_another_tourist_cannot_read_the_order(): void
    {
        $code = $this->actingAs($this->tourist())->postJson(self::ENDPOINT, $this->purchasePayload())
            ->assertCreated()->json('order.code');
        $other = User::factory()->create()->refresh();
        $this->actingAs($other)->getJson(self::ENDPOINT.'/'.$code)->assertNotFound();
    }

    public function test_seeding_twice_preserves_accounts_and_existing_orders(): void
    {
        $this->actingAs($this->tourist())->postJson(self::ENDPOINT, $this->purchasePayload())->assertCreated();
        $hash = $this->tourist()->password;
        app(LocalTicketSeeder::class)->run();
        app(LocalTicketSeeder::class)->run();
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('local_dummy_tickets', 3);
        $this->assertDatabaseCount('local_dummy_ticket_rates', 6);
        $this->assertDatabaseCount('ticket_orders', 1);
        $this->assertSame($hash, $this->tourist()->password);
        $this->assertTrue(Hash::check(config('ticket_purchase.passwords.wisatawan'), $hash));
    }

    public function test_missing_seed_password_rejects_before_writes(): void
    {
        $before = $this->tourist()->password;
        config(['ticket_purchase.passwords.admin' => null]);
        try {
            app(LocalTicketSeeder::class)->run();
            $this->fail('Seeder should refuse missing password.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('LOCAL_ADMIN_PASSWORD', $exception->getMessage());
        }
        $this->assertSame($before, $this->tourist()->password);
        $this->assertDatabaseCount('users', 3);
    }

    public function test_csrf_is_enforced_when_test_bypass_is_disabled(): void
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
        $this->actingAs($this->tourist())->postJson(self::ENDPOINT, $this->purchasePayload())->assertStatus(419);
        $this->assertDatabaseCount('ticket_orders', 0);
        $this->withSession(['_token' => 'local-test-csrf-token'])
            ->postJson(self::ENDPOINT, $this->purchasePayload(), ['X-CSRF-TOKEN' => 'local-test-csrf-token'])
            ->assertCreated();
    }
}
