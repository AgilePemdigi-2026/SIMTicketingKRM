<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_kontak', function (Blueprint $table) {

            // Primary key
            $table->id('id_kontak');

            // Informasi kontak resmi
            $table->text('alamat')->nullable();
            $table->string('nomor_telepon', 30)->nullable();
            $table->string('email', 255)->nullable();

            // Informasi jam operasional
            $table->text('jam_operasional')->nullable();

            // Google Maps Embed URL
            $table->text('google_maps_url')->nullable();

            // Media sosial
            $table->text('instagram')->nullable();
            $table->text('facebook')->nullable();
            $table->text('youtube')->nullable();
            $table->text('twitter')->nullable();

            // Laravel timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_kontak');
    }
};