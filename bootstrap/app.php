<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        then: function () {
            if (config('ticket_purchase.master_enabled')) {
                require __DIR__.'/../routes/ticket-purchase.php';
            }

            // Route pembayaran tiket
            require __DIR__.'/../routes/ticket-payment.php';

            // Route pembayaran reservasi
            require __DIR__.'/../routes/reservation-payment.php';
        },

    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Khusus notifikasi server-to-server Midtrans.
        // Route lain tetap dilindungi CSRF.
        $middleware->preventRequestForgery(except: [
            'webhooks/midtrans/ticket',
            'webhooks/midtrans/reservation',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
