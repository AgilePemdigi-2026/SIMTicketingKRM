<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi_fasilitas', function (Blueprint $table) {
            $table->id('id_sesi');

            $table->foreignId('id_fasilitas')
                ->constrained('fasilitas', 'id_fasilitas')
                ->cascadeOnDelete();

            $table->string('nama_sesi', 100);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('durasi_menit');
            $table->decimal('harga_sesi', 12, 2);
            $table->smallInteger('status_aktif')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_fasilitas');
    }
};
