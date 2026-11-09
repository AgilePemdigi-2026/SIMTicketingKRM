<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daftar_tiket', function (Blueprint $table) {
            $table->dropForeign(['id_wahana']);
        });

        Schema::table('daftar_tiket', function (Blueprint $table) {
            $table->unsignedBigInteger('id_wahana')
                ->nullable()
                ->change();

            $table->foreign('id_wahana')
                ->references('id_wahana')
                ->on('wahana')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('daftar_tiket')
            ->whereNull('id_wahana')
            ->delete();

        Schema::table('daftar_tiket', function (Blueprint $table) {
            $table->dropForeign(['id_wahana']);
        });

        Schema::table('daftar_tiket', function (Blueprint $table) {
            $table->unsignedBigInteger('id_wahana')
                ->nullable(false)
                ->change();

            $table->foreign('id_wahana')
                ->references('id_wahana')
                ->on('wahana')
                ->cascadeOnDelete();
        });
    }
};
