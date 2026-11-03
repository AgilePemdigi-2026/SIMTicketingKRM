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

        $schema = app(TicketPurchaseDatabase::class)
            ->connection()
            ->getSchemaBuilder();

        $schema->table('ticket_payment_gateway_attempts', function (Blueprint $table) {
            $table->text('qr_image_url')->nullable();
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $schema = app(TicketPurchaseDatabase::class)
            ->connection()
            ->getSchemaBuilder();

        $schema->table('ticket_payment_gateway_attempts', function (Blueprint $table) {
            $table->dropColumn('qr_image_url');
        });
    }
};
