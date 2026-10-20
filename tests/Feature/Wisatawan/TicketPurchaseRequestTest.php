<?php

namespace Tests\Feature\Wisatawan;

use App\Http\Requests\StoreTicketPurchaseRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TicketPurchaseRequestTest extends TestCase
{
    private const ENDPOINT = '/_tests/ticket-purchase-validation';

    protected function setUp(): void
    {
        parent::setUp();

        // Isolasi pengujian: pengguna tidak disimpan dan tidak ada migration/seeding.
        config([
            'database.default' => 'sqlite',
            'database.connections' => [
                'sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        // Di UTC masih 20 Oktober, tetapi di Jakarta sudah 21 Oktober.
        $this->travelTo(Carbon::parse('2026-10-20 17:30:00', 'UTC'));

        Route::post(self::ENDPOINT, function (StoreTicketPurchaseRequest $request) {
            return response()->json($request->validated());
        })->middleware(['web', 'auth', 'role:wisatawan']);
    }

    public function test_guest_cannot_submit_a_purchase(): void
    {
        $this->postJson(self::ENDPOINT, $this->payload())->assertUnauthorized();
    }

    #[DataProvider('roles')]
    public function test_only_wisatawan_can_submit_a_purchase(string $role, int $status): void
    {
        $this->actingAs($this->userWithRole($role))
            ->postJson(self::ENDPOINT, $this->payload())
            ->assertStatus($status);
    }

    public static function roles(): array
    {
        return [
            'wisatawan' => ['wisatawan', 200],
            'admin' => ['admin', 403],
            'petugas' => ['petugas', 403],
            'unknown role' => ['unknown', 403],
        ];
    }

    public function test_form_request_itself_authorizes_only_wisatawan(): void
    {
        foreach ([null, 'admin', 'petugas', 'unknown', 'wisatawan'] as $role) {
            $request = new StoreTicketPurchaseRequest;
            $user = $role === null ? null : $this->userWithRole($role);
            $request->setUserResolver(fn () => $user);

            $this->assertSame($role === 'wisatawan', $request->authorize());
        }
    }

    #[DataProvider('validItems')]
    public function test_valid_ticket_quantities_are_accepted(array $items): void
    {
        $payload = $this->payload(['items' => $items]);

        $this->actingAs($this->userWithRole('wisatawan'))
            ->postJson(self::ENDPOINT, $payload)
            ->assertOk()
            ->assertExactJson($payload);
    }

    public static function validItems(): array
    {
        return [
            'one adult' => [[['adult_quantity' => 1, 'child_quantity' => 0]]],
            'two children' => [[['adult_quantity' => 0, 'child_quantity' => 2]]],
            'three mixed' => [[['adult_quantity' => 2, 'child_quantity' => 1]]],
            'four mixed' => [[['adult_quantity' => 2, 'child_quantity' => 2]]],
            'four across items' => [[
                ['adult_quantity' => 1, 'child_quantity' => 1],
                ['adult_quantity' => 1, 'child_quantity' => 1],
            ]],
            'four separate items' => [array_fill(0, 4, ['adult_quantity' => 1, 'child_quantity' => 0])],
            'HTML form integer strings' => [[['adult_quantity' => '1', 'child_quantity' => '2']]],
        ];
    }

    #[DataProvider('invalidItems')]
    public function test_invalid_ticket_quantities_are_rejected(mixed $items, string $error): void
    {
        $this->actingAs($this->userWithRole('wisatawan'))
            ->postJson(self::ENDPOINT, $this->payload(['items' => $items]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($error);
    }

    public static function invalidItems(): array
    {
        $cases = [
            'empty list' => [[], 'items'],
            'not an array' => ['invalid', 'items'],
            'not a list' => [['ticket' => ['adult_quantity' => 1, 'child_quantity' => 0]], 'items'],
            'invalid item' => [['invalid'], 'items.0'],
            'zero tickets' => [[['adult_quantity' => 0, 'child_quantity' => 0]], 'items'],
            'empty item among valid items' => [[
                ['adult_quantity' => 0, 'child_quantity' => 0],
                ['adult_quantity' => 1, 'child_quantity' => 0],
            ], 'items.0'],
            'five in one item' => [[['adult_quantity' => 3, 'child_quantity' => 2]], 'items'],
            'six across repeated items' => [[
                ['adult_quantity' => 2, 'child_quantity' => 1],
                ['adult_quantity' => 2, 'child_quantity' => 1],
            ], 'items'],
            'five separate items' => [array_fill(0, 5, ['adult_quantity' => 1, 'child_quantity' => 0]), 'items'],
        ];

        foreach (['adult_quantity', 'child_quantity'] as $field) {
            foreach ([-1, 1.5, 'abc', true, null, [], 5] as $index => $value) {
                $item = ['adult_quantity' => 1, 'child_quantity' => 1];
                $item[$field] = $value;
                $cases["{$field} invalid {$index}"] = [[$item], "items.0.{$field}"];
            }

            $item = ['adult_quantity' => 1, 'child_quantity' => 1];
            unset($item[$field]);
            $cases["{$field} missing"] = [[$item], "items.0.{$field}"];
        }

        return $cases;
    }

    public function test_required_top_level_fields_are_rejected_when_missing(): void
    {
        $this->actingAs($this->userWithRole('wisatawan'))
            ->postJson(self::ENDPOINT, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['visit_date', 'items'])
            ->assertJsonPath('errors.visit_date.0', 'Tanggal kunjungan wajib diisi.')
            ->assertJsonPath('errors.items.0', 'Daftar tiket wajib diisi.');
    }

    #[DataProvider('visitDates')]
    public function test_visit_dates_use_jakarta_calendar_days(mixed $date, bool $valid): void
    {
        $timezone = config('app.timezone');
        $response = $this->actingAs($this->userWithRole('wisatawan'))
            ->postJson(self::ENDPOINT, $this->payload(['visit_date' => $date]));

        if ($valid) {
            $response->assertOk()->assertJsonPath('visit_date', $date);
        } else {
            $response->assertUnprocessable()->assertJsonValidationErrors('visit_date');
        }

        $this->assertSame($timezone, config('app.timezone'));
    }

    public static function visitDates(): array
    {
        return [
            'today in Jakarta' => ['2026-10-21', true],
            'future' => ['2026-12-01', true],
            'valid leap day' => ['2028-02-29', true],
            'yesterday in Jakarta but today in UTC' => ['2026-10-20', false],
            'past' => ['2026-10-19', false],
            'wrong format' => ['21/10/2026', false],
            'timestamp' => ['2026-10-21T12:00:00+07:00', false],
            'invalid day' => ['2026-11-31', false],
            'invalid leap day' => ['2027-02-29', false],
            'missing value' => [null, false],
            'invalid type' => [[], false],
        ];
    }

    public function test_total_limit_has_an_indonesian_message(): void
    {
        $this->actingAs($this->userWithRole('wisatawan'))
            ->postJson(self::ENDPOINT, $this->payload([
                'items' => [['adult_quantity' => 3, 'child_quantity' => 2]],
            ]))
            ->assertUnprocessable()
            ->assertJsonPath(
                'errors.items.0',
                'Jumlah seluruh tiket dewasa dan anak dalam satu transaksi harus 1 sampai 4.'
            );
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'visit_date' => '2026-10-21',
            'items' => [['adult_quantity' => 1, 'child_quantity' => 0]],
        ], $overrides);
    }

    private function userWithRole(string $role): User
    {
        $user = new User;
        $user->id = 1;
        $user->role = $role;

        return $user;
    }
}
