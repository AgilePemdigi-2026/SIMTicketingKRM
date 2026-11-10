<?php

use App\Support\TicketPurchaseDatabase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') !== 'pgsql') {
            return;
        }

        $schema = app(TicketPurchaseDatabase::class)
            ->connection()
            ->getSchemaBuilder();

        $schema->create('issued_tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_order_id')
                ->constrained('ticket_orders')
                ->cascadeOnDelete();

            $table->foreignId('ticket_order_item_id')
                ->constrained('ticket_order_items')
                ->cascadeOnDelete();

            $table->foreignId('ticket_order_visitor_id')
                ->unique()
                ->constrained('ticket_order_visitors')
                ->cascadeOnDelete();

            $table->uuid('ticket_code')->unique();
            $table->string('qr_token', 64)->unique();

            $table->string('status', 20)->default('active');
            $table->timestampTz('used_at')->nullable();

            $table->timestampsTz();

            $table->index(['ticket_order_id', 'status']);
        });
    }

    public function down(): void
    {
        if (config('database.default') !== 'pgsql') {
            return;
        }

        app(TicketPurchaseDatabase::class)
            ->connection()
            ->getSchemaBuilder()
            ->dropIfExists('issued_tickets');
    }
};
