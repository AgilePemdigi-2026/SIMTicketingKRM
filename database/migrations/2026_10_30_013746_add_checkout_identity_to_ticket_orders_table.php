<?php

use App\Support\TicketPurchaseDatabase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }
        app(TicketPurchaseDatabase::class)->connection()->getSchemaBuilder()
            ->table('ticket_orders', function (Blueprint $table) {
                $table->uuid('checkout_token')->nullable();
                $table->string('checkout_fingerprint', 64)->nullable();
                $table->unique(['user_id', 'checkout_token']);
            });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }
        app(TicketPurchaseDatabase::class)->connection()->getSchemaBuilder()
            ->table('ticket_orders', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'checkout_token']);
                $table->dropColumn(['checkout_token', 'checkout_fingerprint']);
            });
    }
};
