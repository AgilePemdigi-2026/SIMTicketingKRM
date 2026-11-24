<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notification_reads', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('audit_log_id')
                ->constrained('audit_logs')
                ->cascadeOnDelete();

            $table->timestampTz('read_at');

            $table->primary([
                'user_id',
                'audit_log_id',
            ]);

            $table->index('audit_log_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notification_reads');
    }
};
