<?php

use App\Http\Controllers\Wisatawan\TicketPurchaseController;
use App\Http\Middleware\EnsureLocalTicketDatabase;
use Illuminate\Support\Facades\Route;

// Loaded only by the isolated demo/testing provider; never named tiket.index.
Route::middleware([EnsureLocalTicketDatabase::class, 'web', 'auth', 'role:wisatawan'])
    ->prefix('wisatawan/pembelian-tiket')
    ->name('ticket-local.')
    ->group(function () {
        Route::get('/', [TicketPurchaseController::class, 'index'])->name('index');
        Route::post('/', [TicketPurchaseController::class, 'store'])->name('store');
        Route::get('/{code}', [TicketPurchaseController::class, 'show'])->whereUuid('code')->name('show');
    });
