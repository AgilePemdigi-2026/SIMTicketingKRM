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
        $schema = app(TicketPurchaseDatabase::class)->connection()->getSchemaBuilder();
        $schema->create('ticket_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            $table->foreignId('ticket_order_id')->unique()->constrained('ticket_orders');
            $table->string('method', 10);
            $table->decimal('amount', 14, 2);
            $table->string('status', 20)->default('pending')->index();
            $table->string('merchant_reference', 100)->nullable()->unique();
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->timestampTz('paid_at')->nullable();
            $table->timestampsTz();
        });
        $schema->create('ticket_payment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_payment_id')->constrained('ticket_payments');
            $table->string('file_path');
            $table->string('file_hash', 64);
            $table->string('status', 20)->default('under_review');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampsTz();
        });
        $schema->create('ticket_payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_payment_id')->constrained('ticket_payments');
            $table->foreignId('actor_id')->constrained('users');
            $table->string('action', 30);
            $table->text('reason')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }
        $schema = app(TicketPurchaseDatabase::class)->connection()->getSchemaBuilder();
        foreach (['ticket_payment_events', 'ticket_payment_submissions', 'ticket_payments'] as $table) {
            $schema->dropIfExists($table);
        }
    }
};
