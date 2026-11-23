<?php

use App\Support\TicketPurchaseDatabase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $connection = app(TicketPurchaseDatabase::class)->connection();

        if ($connection->getDriverName() !== 'pgsql') {
            return;
        }

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
        $connection = app(TicketPurchaseDatabase::class)->connection();

        if ($connection->getDriverName() !== 'pgsql') {
            return;
        }

        $connection->getSchemaBuilder()
            ->table('ticket_payments', function (Blueprint $table) {
                $table->dropColumn([
                    'cash_received_amount',
                    'cash_change_amount',
                ]);
            });
    }
};
