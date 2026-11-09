<?php

namespace Tests\Feature;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Support\Facades\Artisan;
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
                'ticket_purchase.master_enabled' => false,
                'session.driver' => 'array',
                'cache.default' => 'array',
            ]);
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function test_purchase_routes_are_not_registered_when_master_mode_is_disabled(): void
    {
        $this->assertFalse(Route::has('ticket-local.index'));
        $this->assertFalse(Route::has('ticket-local.store'));
        $this->assertFalse(Route::has('ticket-local.show'));
        $this->assertFalse(Route::has('ticket-purchase.index'));
        $this->assertFalse(Route::has('ticket-purchase.store'));
        $this->assertFalse(Route::has('ticket-purchase.show'));
        $this->assertFalse(Route::has('tiket.index'));
        $this->getJson('/wisatawan/pembelian-tiket')->assertNotFound();
    }

    public function test_retired_demo_command_is_not_available(): void
    {
        $this->assertArrayNotHasKey('ticket:local-setup', Artisan::all());
    }
}
