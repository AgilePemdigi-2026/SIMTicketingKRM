<?php

namespace App\Providers;

use App\Contracts\TicketCatalogProvider;
use App\Services\MasterTicketCatalogProvider;
use App\Support\TicketPurchaseDatabase;
use Illuminate\Support\ServiceProvider;

class TicketPurchaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->masterMode()) {
            $this->app->bind(TicketCatalogProvider::class, MasterTicketCatalogProvider::class);
        }
    }

    public function boot(): void
    {
        if ($this->masterMode()) {
            $this->app->make(TicketPurchaseDatabase::class)->assertMasterConfiguration();
            $this->loadRoutesFrom(base_path('routes/ticket-purchase.php'));
        }
    }

    private function masterMode(): bool
    {
        return config('ticket_purchase.master_enabled')
            && config('database.default') === 'pgsql'
            && $this->app->environment(['local', 'testing']);
    }
}
