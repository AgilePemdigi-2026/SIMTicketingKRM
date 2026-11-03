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

        $schema = app(TicketPurchaseDatabase::class)
            ->connection()
            ->getSchemaBuilder();

        $schema->create('ticket_payment_gateway_attempts', function (Blueprint $table) {
            $table->id();

            // Relasi dengan pembayaran yang dibuat Naufal
            $table->foreignId('ticket_payment_id')
                ->constrained('ticket_payments')
                ->cascadeOnDelete();

            // Identitas transaksi untuk Xendit
            $table->uuid('reference_id')->unique();

            $table->string('provider_payment_request_id', 100)
                ->nullable()
                ->unique();

            $table->string('provider_payment_id', 100)
                ->nullable()
                ->unique();

            // Informasi pembayaran
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('IDR');

            $table->string('status', 30)->default('initiating');

            // QRIS dinamis dari payment gateway
            $table->text('qr_string')->nullable();

            // Masa berlaku dan konfirmasi pembayaran
            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('paid_at')->nullable();

            $table->timestampsTz();

            $table->index([
                'ticket_payment_id',
                'status',
            ], 'payment_gateway_attempt_status_idx');
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        app(TicketPurchaseDatabase::class)
            ->connection()
            ->getSchemaBuilder()
            ->dropIfExists('ticket_payment_gateway_attempts');
    }
};
