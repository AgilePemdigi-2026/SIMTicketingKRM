<?php

use App\Http\Controllers\TicketPaymentController;
use App\Http\Middleware\EnsureMasterTicketDatabase;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureMasterTicketDatabase::class, 'web', 'auth'])->name('ticket-payment.')->group(function () {
    Route::middleware('role:wisatawan')->prefix('wisatawan/pembelian-tiket/{code}/pembayaran')->whereUuid('code')->group(function () {
        Route::get('/', [TicketPaymentController::class, 'show'])->name('show');
        Route::post('/', [TicketPaymentController::class, 'select'])->name('select');
        Route::post('/bukti', [TicketPaymentController::class, 'submit'])->middleware('throttle:20,1')->name('submit');
    });
    Route::middleware('role:petugas')->prefix('petugas/pembayaran')->name('staff.')->group(function () {
        Route::get('/', [TicketPaymentController::class, 'index'])->name('index');
        Route::get('/{payment}', [TicketPaymentController::class, 'detail'])->whereNumber('payment')->name('show');
        Route::post('/{payment}/konfirmasi', [TicketPaymentController::class, 'confirm'])->whereNumber('payment')->name('confirm');
        Route::post('/{payment}/tolak', [TicketPaymentController::class, 'reject'])->whereNumber('payment')->name('reject');
    });
    Route::get('/pembayaran/bukti/{submission}', [TicketPaymentController::class, 'proof'])->whereNumber('submission')->name('proof');
});
