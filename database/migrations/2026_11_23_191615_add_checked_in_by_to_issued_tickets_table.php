<?php

use App\Support\TicketPurchaseDatabase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $database = app(TicketPurchaseDatabase::class);
        if (! $database->supportsEnvironment()) {
            return;
        }

        $schema = $database
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
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $database = app(TicketPurchaseDatabase::class);
        if (! $database->supportsEnvironment()) {
            return;
        }

        $schema = $database
            ->connection()
            ->getSchemaBuilder();

        $schema->table('issued_tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checked_in_by');
        });
    }
};