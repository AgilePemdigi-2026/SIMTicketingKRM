<?php

use App\Support\TicketPurchaseDatabase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') !== 'pgsql') {
            return;
        }

        $schema = app(TicketPurchaseDatabase::class)
            ->connection()
            ->getSchemaBuilder();

        $schema->table('issued_tickets', function (Blueprint $table) {
            $table->foreignId('checked_in_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (config('database.default') !== 'pgsql') {
            return;
        }

        $schema = app(TicketPurchaseDatabase::class)
            ->connection()
            ->getSchemaBuilder();

        $schema->table('issued_tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checked_in_by');
        });
    }
};