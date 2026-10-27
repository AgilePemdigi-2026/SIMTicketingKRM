<?php

namespace Tests\Feature;

use App\Support\TicketPurchaseDatabase;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class TicketPurchaseDatabaseTest extends TestCase
{
    public function createApplication()
    {
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $this->traitsUsedByTest = class_uses_recursive(static::class);
        $app->afterBootstrapping(LoadConfiguration::class, function ($app) {
            $app->instance('env', 'testing');
            $app['config']->set([
                'ticket_purchase.master_enabled' => false,
                'database.default' => 'pgsql',
                'database.connections' => ['pgsql' => [
                    'driver' => 'pgsql', 'host' => '127.0.0.1', 'port' => '5433',
                    'database' => 'simticketingkrm_purchase_test', 'url' => null, 'prefix' => '',
                ]],
            ]);
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function test_expected_test_configuration_passes_without_opening_a_connection(): void
    {
        DB::shouldReceive('connection')->never();
        app(TicketPurchaseDatabase::class)->assertMasterConfiguration();
        $this->assertTrue(true);
    }

    #[DataProvider('unsafeTargets')]
    public function test_unsafe_target_is_rejected_before_opening_a_connection(array $overrides): void
    {
        config($overrides);
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);
        app(TicketPurchaseDatabase::class)->connection();
    }

    public static function unsafeTargets(): array
    {
        return [
            'sqlite default' => [['database.default' => 'sqlite']],
            'sqlite driver' => [['database.connections.pgsql.driver' => 'sqlite']],
            'remote host' => [['database.connections.pgsql.host' => 'remote.example.invalid']],
            'different port' => [['database.connections.pgsql.port' => '5432']],
            'application database during tests' => [['database.connections.pgsql.database' => 'simticketingkrm']],
            'other database' => [['database.connections.pgsql.database' => 'other_database']],
            'connection URL' => [['database.connections.pgsql.url' => 'pgsql://remote.example.invalid/database']],
            'read override' => [['database.connections.pgsql.read' => ['host' => 'remote.example.invalid']]],
            'write override' => [['database.connections.pgsql.write' => ['host' => 'remote.example.invalid']]],
            'table prefix' => [['database.connections.pgsql.prefix' => 'other_']],
        ];
    }

    public function test_cached_configuration_is_rejected_before_opening_a_connection(): void
    {
        $this->app->instance('config_loaded_from_cache', true);
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);
        app(TicketPurchaseDatabase::class)->connection();
    }

    public function test_unsupported_environment_is_rejected_before_opening_a_connection(): void
    {
        $this->app->instance('env', 'production');
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);
        app(TicketPurchaseDatabase::class)->connection();
    }
}
