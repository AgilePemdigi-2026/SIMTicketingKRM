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

        $schema = $database->connection()->getSchemaBuilder();

        // Status pembayaran per reservasi
        $schema->create('facility_reservation_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('facility_reservation_id')
                ->unique()
                ->constrained('facility_reservations');

            $table->string('method', 20)->default('qris');
            $table->string('status', 24)->default('pending')->index();

            $table->decimal('amount', 14, 2);

            $table->timestampTz('paid_at')->nullable();
            $table->timestampsTz();
        });

        // Percobaan pembayaran Midtrans QRIS
        $schema->create('facility_reservation_payment_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('facility_reservation_payment_id')
                ->constrained('facility_reservation_payments');

            $table->string('reference_id', 100)->unique();
            $table->string('provider_payment_id', 100)->nullable()->index();

            $table->string('currency', 3)->default('IDR');
            $table->decimal('amount', 14, 2);

            $table->string('status', 24)->default('creating')->index();

            $table->text('qr_image_url')->nullable();
            $table->text('qr_string')->nullable();

            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('paid_at')->nullable();

            $table->timestampsTz();
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

        $schema = $database->connection()->getSchemaBuilder();

        $schema->dropIfExists('facility_reservation_payment_attempts');
        $schema->dropIfExists('facility_reservation_payments');
    }
};
