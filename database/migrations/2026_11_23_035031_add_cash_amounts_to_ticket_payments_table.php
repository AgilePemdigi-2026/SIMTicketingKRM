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

        $database = app(TicketPurchaseDatabase::class);
        if (! $database->supportsEnvironment()) {
            return;
        }

        $connection = $database->connection();

        $connection->getSchemaBuilder()
            ->table('ticket_payments', function (Blueprint $table) {
                $table->decimal('cash_received_amount', 14, 2)
                    ->nullable();

                $table->decimal('cash_change_amount', 14, 2)
                    ->nullable();
            });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $database = app(TicketPurchaseDatabase::class);
        if (! $database->supportsEnvironment()) {
            return;
        }

        $connection = $database->connection();

        $connection->getSchemaBuilder()
            ->table('ticket_payments', function (Blueprint $table) {
                $table->dropColumn([
                    'cash_received_amount',
                    'cash_change_amount',
                ]);
            });
    }
};
