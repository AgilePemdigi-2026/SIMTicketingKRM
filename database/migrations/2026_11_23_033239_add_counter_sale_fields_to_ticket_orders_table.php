<?php

use App\Support\TicketPurchaseDatabase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $connection = app(TicketPurchaseDatabase::class)->connection();

        if ($connection->getDriverName() !== 'pgsql') {
            return;
        }

        $schema = $connection->getSchemaBuilder();

        $schema->table('ticket_orders', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->change();

            $table->string('sale_channel', 20)
                ->default('online');

            $table->foreignId('staff_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });

        // Cegah token checkout duplikat pada pesanan tanpa akun.
        $connection->statement(
            'CREATE UNIQUE INDEX ticket_orders_guest_checkout_token_unique
             ON ticket_orders (checkout_token)
             WHERE user_id IS NULL AND checkout_token IS NOT NULL'
        );
    }

    public function down(): void
    {
        $connection = app(TicketPurchaseDatabase::class)->connection();

        if ($connection->getDriverName() !== 'pgsql') {
            return;
        }

        // Jangan rollback jika sudah ada pesanan tanpa akun.
        if ($connection->table('ticket_orders')
            ->whereNull('user_id')
            ->exists()) {
            throw new RuntimeException(
                'Rollback dibatalkan: masih ada pesanan loket tanpa akun.'
            );
        }

        $schema = $connection->getSchemaBuilder();

        $connection->statement(
            'DROP INDEX IF EXISTS ticket_orders_guest_checkout_token_unique'
        );

        $schema->table('ticket_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('staff_user_id');
            $table->dropColumn('sale_channel');

            $table->foreignId('user_id')
                ->nullable(false)
                ->change();
        });
    }
};
