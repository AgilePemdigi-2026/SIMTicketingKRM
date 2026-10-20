<?php

namespace Tests\Support;

use App\Models\User;
use App\Support\LocalTicketDatabase;
use Database\Seeders\LocalTicketSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

abstract class LocalTicketTestCase extends TestCase
{
    public function createApplication()
    {
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $this->traitsUsedByTest = class_uses_recursive(static::class);
        if (is_file($app->getCachedConfigPath()) || is_file($app->getCachedRoutesPath())) {
            throw new \RuntimeException('Tes tiket menolak cache konfigurasi/route.');
        }
        // Pin configuration before providers boot or any connection is resolved.
        $app->afterBootstrapping(LoadConfiguration::class, function ($app) {
            $app->instance('env', 'testing');
            $app['config']->set([
                'app.env' => 'testing',
                'app.key' => 'base64:'.base64_encode(random_bytes(32)),
                'database.default' => 'sqlite',
                'database.connections' => [
                    'sqlite' => [
                        'driver' => 'sqlite', 'url' => null, 'database' => ':memory:',
                        'prefix' => '', 'foreign_key_constraints' => true,
                    ],
                ],
                'ticket_purchase.local_enabled' => true,
                // Ephemeral test-only secrets; never the real demo account passwords.
                'ticket_purchase.passwords' => array_fill_keys(
                    ['admin', 'wisatawan', 'petugas'], bin2hex(random_bytes(16))
                ),
                'session.driver' => 'array',
                'session.encrypt' => false,
                'session.connection' => null,
                'cache.default' => 'array',
                'queue.default' => 'sync',
                'logging.default' => 'null',
                'hashing.bcrypt.rounds' => 4,
            ]);
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-20 17:30:00', 'UTC'));
    }

    protected function seedLocalDatabase(): void
    {
        $guard = app(LocalTicketDatabase::class);
        $guard->connection();
        foreach (['database/migrations', 'database/local/migrations'] as $path) {
            $this->assertSame(0, Artisan::call('migrate', [
                '--database' => 'sqlite', '--path' => [$path], '--force' => true,
            ]));
        }
        app(LocalTicketSeeder::class)->run();
    }

    protected function tourist(): User
    {
        return User::where('email', 'tester.wisatawan@example.com')->firstOrFail();
    }

    protected function purchasePayload(): array
    {
        return [
            'visit_date' => '2026-10-21',
            'visitor_type' => 'domestik',
            'booker' => [
                'name' => 'Pemesan Demo', 'identity_number' => 'BOOKER-DEMO',
                'phone' => '080000000004', 'email' => 'pemesan@example.com',
            ],
            'items' => [[
                'ticket_reference' => 'dummy:masuk',
                'adult_quantity' => 1, 'child_quantity' => 1,
                'visitors' => [
                    ['name' => 'Dewasa Demo', 'identity_number' => 'VISITOR-1', 'age_category' => 'adult'],
                    ['name' => 'Anak Demo', 'identity_number' => 'VISITOR-2', 'age_category' => 'child'],
                ],
            ]],
        ];
    }
}
