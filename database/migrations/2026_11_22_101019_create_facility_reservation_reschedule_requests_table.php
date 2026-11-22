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
        $schema = $connection->getSchemaBuilder();

        $schema->create(
            'facility_reservation_reschedule_requests',
            function (Blueprint $table) {
                $table->id();

                // Reservasi asli dan pemilik pengajuan.
                $table->foreignId('facility_reservation_id')
                    ->constrained(
                        'facility_reservations',
                        'id',
                        'frsr_reservation_fk'
                    )
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained('users', 'id', 'frsr_user_fk');

                // Jadwal lama, disimpan sebagai riwayat.
                $table->date('original_visit_date');

                $table->foreignId('original_id_sesi')
                    ->constrained(
                        'sesi_fasilitas',
                        'id_sesi',
                        'frsr_original_sesi_fk'
                    );

                // Jadwal baru yang diminta wisatawan.
                $table->date('requested_visit_date');

                $table->foreignId('requested_id_sesi')
                    ->constrained(
                        'sesi_fasilitas',
                        'id_sesi',
                        'frsr_requested_sesi_fk'
                    );

                // Alasan pengajuan.
                $table->text('reason');

                // pending, approved, rejected
                $table->string('status', 24)
                    ->default('pending');

                // Disiapkan untuk proses persetujuan Admin.
                $table->text('admin_note')->nullable();

                $table->foreignId('reviewed_by')
                    ->nullable()
                    ->constrained(
                        'users',
                        'id',
                        'frsr_reviewer_fk'
                    )
                    ->nullOnDelete();

                $table->timestampTz('reviewed_at')
                    ->nullable();

                $table->timestampsTz();

                $table->index(
                    ['facility_reservation_id', 'status'],
                    'frsr_reservation_status_idx'
                );

                $table->index(
                    ['requested_id_sesi', 'requested_visit_date'],
                    'frsr_requested_schedule_idx'
                );
            }
        );

        // Satu reservasi hanya boleh memiliki satu
        // pengajuan yang masih menunggu persetujuan.
        $connection->statement(
            "CREATE UNIQUE INDEX frsr_one_pending_per_reservation
             ON facility_reservation_reschedule_requests
             (facility_reservation_id)
             WHERE status = 'pending'"
        );
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

        $database->connection()
            ->getSchemaBuilder()
            ->dropIfExists(
                'facility_reservation_reschedule_requests'
            );
    }
};
