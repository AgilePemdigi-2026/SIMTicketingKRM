<?php

namespace Tests\Support;

use App\Models\User;
use App\Support\TicketPurchaseDatabase;
use Database\Seeders\PostgreSqlDemoSeeder;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

abstract class PostgresTicketTestCase extends TestCase
{
    protected ?Connection $testConnection = null;

    public function createApplication()
    {
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $this->traitsUsedByTest = class_uses_recursive(static::class);
        if (is_file($app->getCachedConfigPath()) || is_file($app->getCachedRoutesPath())) {
            throw new \RuntimeException('Pengujian PostgreSQL menolak cache konfigurasi/route.');
        }
        $app->afterBootstrapping(LoadConfiguration::class, function ($app) {
            $file = $app->basePath('.env.postgres.local');
            if (! is_file($file)) {
                throw new \RuntimeException('Siapkan .env.postgres.local untuk tes PostgreSQL.');
            }
            $env = Dotenv::parse(file_get_contents($file));
            if (($env['DB_HOST'] ?? '') !== '127.0.0.1' || ($env['DB_PORT'] ?? '') !== '5433'
                || ($env['DB_CONNECTION'] ?? '') !== 'pgsql' || ! empty($env['DB_URL'])) {
                throw new \RuntimeException('Environment pengujian PostgreSQL harus lokal.');
            }
            $app->instance('env', 'testing');
            $app['config']->set([
                'app.env' => 'testing', 'app.key' => 'base64:'.base64_encode(random_bytes(32)),
                'database.default' => 'pgsql',
                'database.connections' => ['pgsql' => [
                    'driver' => 'pgsql', 'host' => '127.0.0.1', 'port' => '5433',
                    'database' => 'simticketingkrm_purchase_test', 'username' => $env['DB_USERNAME'],
                    'password' => $env['DB_PASSWORD'], 'url' => null, 'prefix' => '',
                    'charset' => 'utf8', 'search_path' => 'public', 'sslmode' => 'disable',
                ]],
                'ticket_purchase.master_enabled' => true,
                'session.driver' => 'array', 'session.connection' => null, 'cache.default' => 'array',
                'queue.default' => 'sync', 'logging.default' => 'null', 'hashing.bcrypt.rounds' => 4,
            ]);
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->testConnection = app(TicketPurchaseDatabase::class)->connection();
        // Guard confirms this exact test database before any migration or write.
        $this->assertSame('simticketingkrm_purchase_test', $this->testConnection->getDatabaseName());
        $this->assertSame(0, Artisan::call('migrate', ['--database' => 'pgsql', '--no-interaction' => true]));
        $this->testConnection->beginTransaction();
        // Use the group's own snapshot seeder, never copy the SQLite catalog.
        // Its local-only environment check is preserved. Only this seeding call
        // runs in a local context, after the exact test PDO has been verified.
        $environment = app()->environment();
        try {
            app()->instance('env', 'local');
            app(PostgreSqlDemoSeeder::class)->run();
        } finally {
            app()->instance('env', $environment);
        }
        $this->assertSame(3, $this->testConnection->table('daftar_tiket')->count());
    }

    protected function tearDown(): void
    {
        if ($this->testConnection !== null) {
            while ($this->testConnection->transactionLevel() > 0) {
                $this->testConnection->rollBack();
            }
        }
        parent::tearDown();
    }

    protected function account(string $role = 'wisatawan'): User
    {
        $user = new User;
        $user->fill([
            'name' => 'Pengguna Tes', 'email' => bin2hex(random_bytes(8)).'@example.com',
            'password' => Hash::make('test-only-password'), 'visitor_type' => 'domestik',
        ]);
        $user->role = $role;
        $user->save();

        return $user;
    }

    protected function payload(): array
    {
        return [
            'visit_date' => now('Asia/Jakarta')->addDay()->toDateString(), 'visitor_type' => 'domestik',
            'booker' => ['name' => 'Pemesan Tes', 'identity_number' => 'TES-PEMESAN',
                'phone' => '080000009999', 'email' => 'pemesan@example.com'],
            'items' => [['ticket_reference' => 'master:5', 'adult_quantity' => 1, 'child_quantity' => 1,
                'visitors' => [
                    ['name' => 'Dewasa Tes', 'identity_number' => 'TES-1', 'age_category' => 'adult'],
                    ['name' => 'Anak Tes', 'identity_number' => 'TES-2', 'age_category' => 'child'],
                ]]],
        ];
    }
}
