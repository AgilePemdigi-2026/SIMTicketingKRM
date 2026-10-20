<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daftar_tiket', function (Blueprint $table) {
            $table->dropColumn('kategori_usia');
        });
    }

    public function down(): void
    {
        Schema::table('daftar_tiket', function (Blueprint $table) {
            $table->string('kategori_usia', 50)->nullable();
        });
    }
};
