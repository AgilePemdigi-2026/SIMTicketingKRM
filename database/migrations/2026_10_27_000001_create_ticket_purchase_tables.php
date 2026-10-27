<?php

use App\Support\TicketPurchaseDatabase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Transaction storage is supported only on the verified local PostgreSQL.
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }
        $schema = app(TicketPurchaseDatabase::class)->connection()->getSchemaBuilder();
        $schema->create('ticket_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->date('visit_date');
            $table->string('visitor_type', 20);
            $table->string('booker_name');
            $table->string('booker_identity_number', 50);
            $table->string('booker_phone', 25);
            $table->string('booker_email');
            $table->decimal('total', 14, 2);
            $table->string('status', 30)->default('pending_payment');
            $table->timestamps();
        });
        $schema->create('ticket_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_order_id')->constrained('ticket_orders');
            $table->string('ticket_reference', 64);
            $table->string('ticket_name');
            $table->string('ticket_category', 50);
            $table->unsignedSmallInteger('adult_quantity');
            $table->unsignedSmallInteger('child_quantity');
            $table->decimal('adult_unit_price', 12, 2);
            $table->decimal('child_unit_price', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
        $schema->create('ticket_order_visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_order_item_id')->constrained('ticket_order_items');
            $table->string('name');
            $table->string('identity_number', 50);
            $table->string('age_category', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }
        $schema = app(TicketPurchaseDatabase::class)->connection()->getSchemaBuilder();
        foreach (['ticket_order_visitors', 'ticket_order_items', 'ticket_orders'] as $table) {
            $schema->dropIfExists($table);
        }
    }
};
