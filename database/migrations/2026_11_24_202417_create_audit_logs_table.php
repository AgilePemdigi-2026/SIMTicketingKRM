<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Pengguna yang melakukan aktivitas.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Snapshot identitas pengguna saat aktivitas terjadi.
            $table->string('actor_name', 255);
            $table->string('actor_role', 30);

            // Informasi aktivitas sistem.
            $table->string('module', 80);
            $table->string('action', 100);
            $table->text('description');

            // Referensi objek yang berkaitan dengan aktivitas.
            $table->string('subject_type', 100)
                ->nullable();

            $table->string('subject_id', 100)
                ->nullable();

            // Histori perubahan data yang diizinkan.
            $table->json('old_values')
                ->nullable();

            $table->json('new_values')
                ->nullable();

            // Waktu terjadinya aktivitas.
            $table->timestampTz('created_at')
                ->useCurrent();

            // Index untuk kebutuhan monitoring dan filter.
            $table->index('created_at');
            $table->index('action');

            $table->index([
                'module',
                'created_at',
            ]);

            $table->index([
                'user_id',
                'created_at',
            ]);

            $table->index([
                'subject_type',
                'subject_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
