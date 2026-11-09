<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiket_wahana', function (Blueprint $table) {
            $table->id('id_tiket_wahana');

            $table->unsignedBigInteger('id_tiket');
            $table->unsignedBigInteger('id_wahana');

            $table->foreign('id_tiket')
                ->references('id_tiket')
                ->on('daftar_tiket')
                ->cascadeOnDelete();

            $table->foreign('id_wahana')
                ->references('id_wahana')
                ->on('wahana')
                ->cascadeOnDelete();

            $table->unique(['id_tiket', 'id_wahana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiket_wahana');
    }
};