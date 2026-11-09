<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_destinasi', function (Blueprint $table) {

            // Primary key
            $table->id('id_destinasi');

            // Identitas destinasi
            $table->string('nama_destinasi', 150);

            // Foto utama destinasi
            $table->string('foto_utama', 255)->nullable();

            // Informasi dan deskripsi destinasi
            $table->text('deskripsi_singkat')->nullable();
            $table->longText('deskripsi_lengkap')->nullable();

            // Jam operasional
            $table->time('jam_buka')->nullable();
            $table->time('jam_tutup')->nullable();

            // Lokasi / alamat destinasi
            $table->text('alamat')->nullable();

            // Laravel timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_destinasi');
    }
};