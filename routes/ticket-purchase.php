<?php

use App\Http\Controllers\Wisatawan\TicketPurchaseController;
use App\Http\Middleware\EnsureMasterTicketDatabase;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureMasterTicketDatabase::class, 'web', 'auth', 'role:wisatawan'])
    ->prefix('wisatawan/pembelian-tiket')->name('ticket-purchase.')
    ->group(function () {
        Route::get('/', [TicketPurchaseController::class, 'index'])->name('index');
        Route::post('/', [TicketPurchaseController::class, 'store'])->name('store');
        Route::get('/{code}', [TicketPurchaseController::class, 'show'])->whereUuid('code')->name('show');
    });
