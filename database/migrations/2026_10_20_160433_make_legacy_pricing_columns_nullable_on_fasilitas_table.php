<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fasilitas', function (Blueprint $table) {
            $table->decimal('harga_sewa', 12, 2)
                ->nullable()
                ->change();

            $table->time('slot_waktu')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        // Sengaja tidak mengembalikan kolom menjadi NOT NULL.
        // Ini mencegah rollback gagal jika ada data bernilai NULL.
    }
};
