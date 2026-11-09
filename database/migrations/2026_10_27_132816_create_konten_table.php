<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konten', function (Blueprint $table) {

            // Primary key
            $table->id('id_konten');

            // Admin pembuat konten
            $table->foreignId('id_pengguna')
                ->constrained('users')
                ->restrictOnDelete();

            // Jenis: berita atau galeri
            $table->string('jenis_konten', 20);

            // Identitas konten
            $table->string('judul', 150)->nullable();
            $table->string('slug', 180)->nullable()->unique();
            $table->string('kategori', 30)->nullable();

            // Isi berita atau keterangan galeri
            $table->longText('isi')->nullable();
            $table->string('penulis', 100)->nullable();

            // Foto / thumbnail
            $table->string('gambar', 255)->nullable();

            // Pengaturan publikasi
            $table->timestamp('waktu_publikasi')->nullable();

            $table->string('status_publikasi', 15)
                ->default('draft');

            // Laravel timestamps
            $table->timestamps();

            // Index untuk daftar dan filter konten
            $table->index(
                ['jenis_konten', 'status_publikasi', 'waktu_publikasi'],
                'konten_publikasi_index'
            );

            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konten');
    }
};