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
        $schema->create('facility_reservations', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            $table->string('invoice_number', 40)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('id_fasilitas')->constrained('fasilitas', 'id_fasilitas');
            $table->foreignId('id_sesi')->constrained('sesi_fasilitas', 'id_sesi');
            $table->string('facility_name', 120);
            $table->string('session_name', 100);
            $table->date('visit_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('duration_minutes');
            $table->unsignedInteger('participant_count');
            $table->string('customer_name', 100);
            $table->string('identity_number', 32);
            $table->string('phone', 20);
            $table->string('email', 150);
            $table->string('purpose', 200);
            $table->text('additional_information')->nullable();
            $table->decimal('unit_price', 14, 2);
            $table->decimal('total_amount', 14, 2);
            $table->string('administrative_status', 24)->default('pending')->index();
            $table->timestampTz('capacity_hold_until')->index();
            $table->timestampsTz();
            $table->index(['id_sesi', 'visit_date', 'administrative_status'], 'facility_reservation_capacity_idx');
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        if (! app(TicketPurchaseDatabase::class)->supportsEnvironment()) {
            return;
        }

        $schema = app(TicketPurchaseDatabase::class)->connection()->getSchemaBuilder();
        $schema->dropIfExists('facility_reservations');
    }
};
