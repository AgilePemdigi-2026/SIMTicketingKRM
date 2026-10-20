<?php

namespace Tests\Feature;

use App\Support\LocalTicketDatabase;
use Database\Seeders\LocalTicketSeeder;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\LocalTicketTestCase;

class LocalTicketDatabaseTest extends LocalTicketTestCase
{
    public function test_real_pdo_is_sqlite_memory_with_foreign_keys(): void
    {
        $connection = app(LocalTicketDatabase::class)->connection();
        $this->assertSame('sqlite', $connection->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME));
        $this->assertSame('', $connection->selectOne('PRAGMA database_list')->file);
        $this->assertSame(1, (int) $connection->selectOne('PRAGMA foreign_keys')->foreign_keys);
        $this->assertSame(['sqlite'], array_keys(config('database.connections')));
    }

    #[DataProvider('unsafeConfiguration')]
    public function test_unsafe_configuration_fails_before_opening_any_connection(array $override): void
    {
        config($override);
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);
        app(LocalTicketDatabase::class)->connection();
    }

    public static function unsafeConfiguration(): array
    {
        return [
            'disabled' => [['ticket_purchase.local_enabled' => false]],
            'postgres default' => [['database.default' => 'pgsql']],
            'postgres driver under sqlite name' => [['database.connections.sqlite.driver' => 'pgsql']],
            'redirecting URL' => [['database.connections.sqlite.url' => 'pgsql://invalid.example.invalid/demo']],
            'read override' => [['database.connections.sqlite.read' => ['host' => 'invalid.example.invalid']]],
            'write override' => [['database.connections.sqlite.write' => ['host' => 'invalid.example.invalid']]],
            'disk forbidden in tests' => [['database.connections.sqlite.database' => '/tmp/simticketingkrm_local.sqlite']],
            'foreign keys disabled' => [['database.connections.sqlite.foreign_key_constraints' => false]],
            'table prefix' => [['database.connections.sqlite.prefix' => 'other_']],
        ];
    }

    public function test_production_is_refused_even_with_dummy_flag(): void
    {
        $this->app->instance('env', 'production');
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);
        app(LocalTicketDatabase::class)->connection();
    }

    public function test_local_environment_rejects_a_different_file_before_connection(): void
    {
        $this->app->instance('env', 'ticket-local');
        config(['database.connections.sqlite.database' => database_path('other.sqlite')]);
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);
        app(LocalTicketDatabase::class)->connection();
    }

    public function test_configuration_cache_blocks_setup_before_connection(): void
{
    $this->app->instance('env', 'ticket-local');

    config([
        'database.connections.sqlite.database' =>
            database_path('simticketingkrm_local.sqlite'),
    ]);

    // Simulasikan bahwa konfigurasi Laravel sudah di-cache.
    $this->app->instance('config_loaded_from_cache', true);

    DB::shouldReceive('connection')->never();

    try {
        app(LocalTicketDatabase::class)->connection();
        $this->fail('Configuration cache should be rejected.');
    } catch (RuntimeException $exception) {
        $this->assertStringContainsString(
            'cache',
            strtolower($exception->getMessage())
        );
    }
}

    public function test_active_connection_cannot_be_silently_retargeted(): void
    {
        $connection = app(LocalTicketDatabase::class)->connection();
        // Resolve an intentionally different connection without opening its PDO.
        DB::purge('sqlite');
        config(['database.connections.sqlite.database' => '/tmp/wrong-demo.sqlite']);
        DB::connection('sqlite');
        config(['database.connections.sqlite.database' => ':memory:']);
        $this->expectException(RuntimeException::class);
        app(LocalTicketDatabase::class)->connection();
    }

    public function test_seeder_direct_invocation_refuses_postgres_before_connection(): void
    {
        config(['database.default' => 'pgsql']);
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);
        app(LocalTicketSeeder::class)->run();
    }

    public function test_local_migration_direct_invocation_refuses_postgres(): void
    {
        config(['database.default' => 'pgsql']);
        DB::shouldReceive('connection')->never();
        $migration = require database_path('local/migrations/2026_10_20_000001_create_local_ticket_tables.php');
        $this->expectException(RuntimeException::class);
        $migration->up();
    }

    public function test_setup_refuses_wrong_connection_before_migration_or_seed(): void
    {
        config(['database.default' => 'pgsql']);
        DB::shouldReceive('connection')->never();
        $this->artisan('ticket:local-setup')->assertFailed();
    }

    public function test_main_migration_path_does_not_install_dummy_tables(): void
    {
        $connection = app(LocalTicketDatabase::class)->connection();
        $this->artisan('migrate', [
            '--database' => 'sqlite', '--path' => ['database/migrations'], '--force' => true,
        ])->assertSuccessful();
        $this->assertTrue($connection->getSchemaBuilder()->hasTable('users'));
        $this->assertFalse($connection->getSchemaBuilder()->hasTable('local_dummy_tickets'));
        $this->assertFalse($connection->getSchemaBuilder()->hasTable('ticket_orders'));
    }
}
