<?php

namespace App\Http\Middleware;

use App\Support\LocalTicketDatabase;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLocalTicketDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        app(LocalTicketDatabase::class)->connection();

        return $next($request);
    }
}
