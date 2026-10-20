<?php

use App\Support\LocalTicketDatabase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    protected $connection = 'sqlite';

    public function up(): void
    {
        $schema = app(LocalTicketDatabase::class)->connection()->getSchemaBuilder();

        $schema->create('local_dummy_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('category', 20);
            $table->boolean('is_active');
            $table->timestamps();
        });

        $schema->create('local_dummy_ticket_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_dummy_ticket_id')->constrained('local_dummy_tickets');
            $table->string('visitor_type', 20);
            $table->bigInteger('adult_price');
            $table->bigInteger('child_price');
            $table->timestamps();
            $table->unique(['local_dummy_ticket_id', 'visitor_type']);
        });

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
            $table->bigInteger('total');
            $table->string('status', 30)->default('pending_payment');
            $table->timestamps();
        });

        $schema->create('ticket_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_order_id')->constrained('ticket_orders');
            // Source-qualified code; deliberately no FK to a future Dana master.
            $table->string('ticket_reference', 64);
            $table->string('ticket_name');
            $table->string('ticket_category', 20);
            $table->unsignedSmallInteger('adult_quantity');
            $table->unsignedSmallInteger('child_quantity');
            $table->bigInteger('adult_unit_price');
            $table->bigInteger('child_unit_price');
            $table->bigInteger('subtotal');
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
        $schema = app(LocalTicketDatabase::class)->connection()->getSchemaBuilder();
        foreach (['ticket_order_visitors', 'ticket_order_items', 'ticket_orders', 'local_dummy_ticket_rates', 'local_dummy_tickets'] as $table) {
            $schema->dropIfExists($table);
        }
    }
};
