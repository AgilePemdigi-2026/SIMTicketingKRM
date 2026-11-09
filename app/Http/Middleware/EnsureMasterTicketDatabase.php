<?php

namespace App\Http\Middleware;

use App\Support\TicketPurchaseDatabase;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMasterTicketDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            app(TicketPurchaseDatabase::class)->connection();
        } catch (\Throwable) {
            abort(503, 'Database pembelian lokal belum tersedia. Tidak ada sumber tarif pengganti yang digunakan.');
        }

        return $next($request);
    }
}
