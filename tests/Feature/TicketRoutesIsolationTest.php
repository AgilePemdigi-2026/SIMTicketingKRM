<?php

namespace Tests\Feature;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TicketRoutesIsolationTest extends TestCase
{
    public function createApplication()
    {
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $this->traitsUsedByTest = class_uses_recursive(static::class);
        $app->afterBootstrapping(LoadConfiguration::class, function ($app) {
            $app['config']->set([
                'ticket_purchase.local_enabled' => false,
                'database.default' => 'sqlite',
                'database.connections.sqlite.url' => null,
                'database.connections.sqlite.database' => ':memory:',
                'session.driver' => 'array',
                'cache.default' => 'array',
            ]);
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function test_dummy_routes_are_not_registered_without_explicit_local_mode(): void
    {
        $this->assertFalse(Route::has('ticket-local.index'));
        $this->assertFalse(Route::has('ticket-local.store'));
        $this->assertFalse(Route::has('ticket-local.show'));
        $this->assertFalse(Route::has('tiket.index'));
        $this->getJson('/wisatawan/pembelian-tiket')->assertNotFound();
    }
}
