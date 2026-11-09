<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wahana', function (Blueprint $table) {
            $table->dropColumn(['durasi', 'jam_operasional']);
        });

        Schema::table('wahana', function (Blueprint $table) {
            $table->integer('durasi');
            $table->time('jam_buka')->nullable();
            $table->time('jam_tutup')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wahana', function (Blueprint $table) {
            $table->dropColumn(['durasi', 'jam_buka', 'jam_tutup']);
        });

        Schema::table('wahana', function (Blueprint $table) {
            $table->time('durasi');
            $table->time('jam_operasional')->nullable();
        });
    }
};
