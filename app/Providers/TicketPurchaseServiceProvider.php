<?php

namespace App\Providers;

use App\Contracts\TicketCatalogProvider;
use App\Services\LocalTicketCatalogProvider;
use App\Support\LocalTicketDatabase;
use Illuminate\Support\ServiceProvider;

class TicketPurchaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (config('ticket_purchase.local_enabled')
            && $this->app->environment(['ticket-local', 'testing'])) {
            $this->app->bind(TicketCatalogProvider::class, LocalTicketCatalogProvider::class);
        }
    }

    public function boot(LocalTicketDatabase $database): void
    {
        if (! config('ticket_purchase.local_enabled')
            || ! $this->app->environment(['ticket-local', 'testing'])) {
            return;
        }

        $database->assertConfiguration(allowMissingFile: true);
        config(['database.connections' => ['sqlite' => config('database.connections.sqlite')]]);

        if ($this->app->environment('ticket-local')) {
            config([
                'session.driver' => 'file',
                'session.files' => storage_path('framework/ticket-local/sessions'),
                'session.cookie' => 'simticketingkrm_ticket_local_session',
                'session.connection' => null,
                'session.store' => null,
                'cache.default' => 'file',
                'cache.stores.file.path' => storage_path('framework/ticket-local/cache'),
                'cache.stores.file.lock_path' => storage_path('framework/ticket-local/locks'),
                'queue.default' => 'sync',
            ]);
        }

        $this->loadRoutesFrom(base_path('routes/ticket-local.php'));
    }
}
