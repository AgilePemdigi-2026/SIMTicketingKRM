<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daftar_tiket', function (Blueprint $table) {
            $table->id('id_tiket');
            $table->unsignedBigInteger('id_wahana');

            $table->string('nama_tiket', 100);
            $table->string('kategori_tiket', 50);
            $table->integer('kuota');
            $table->string('kategori_wisatawan', 50);
            $table->string('kategori_usia', 50);
            $table->decimal('harga_dewasa', 12, 2);
            $table->decimal('harga_anak', 12, 2);
            $table->smallInteger('status_aktif');
            $table->text('ketentuan')->nullable();

            $table->foreign('id_wahana')
                ->references('id_wahana')
                ->on('wahana')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daftar_tiket');
    }
};
