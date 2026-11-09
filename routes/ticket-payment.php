<?php

use App\Http\Controllers\TicketPaymentController;
use App\Http\Middleware\EnsureMasterTicketDatabase;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransTicketWebhookController;

// Webhook Midtrans: publik, tanpa auth.
// Controller tetap memverifikasi signature SHA-512.
Route::post(
    'webhooks/midtrans/ticket',
    [MidtransTicketWebhookController::class, 'handle']
)
    ->middleware([
        EnsureMasterTicketDatabase::class,
        'throttle:60,1',
    ])
    ->name('ticket-payment.midtrans.webhook');

Route::middleware([EnsureMasterTicketDatabase::class, 'web', 'auth'])->name('ticket-payment.')->group(function () {
    Route::middleware('role:wisatawan')->prefix('wisatawan/pembelian-tiket/{code}/pembayaran')->whereUuid('code')->group(function () {
    Route::get('/', [TicketPaymentController::class, 'show'])->name('show');
    Route::post('/', [TicketPaymentController::class, 'select'])->name('select');
    Route::post('/qris', [TicketPaymentController::class, 'createQris'])
        ->middleware('throttle:5,1')
        ->name('qris.create');

    Route::post('/qris/sync', [TicketPaymentController::class, 'syncQris'])
        ->middleware('throttle:5,1')
        ->name('qris.sync');

    // Halaman sementara setelah pembayaran berhasil.
    // QR E-Tiket asli akan diintegrasikan dengan modul Dana.
    Route::get(
        '/e-tiket-sementara',
        [TicketPaymentController::class, 'eTicketPlaceholder']
    )->name('e-ticket-placeholder');

    Route::post('/bukti', [TicketPaymentController::class, 'submit'])
        ->middleware('throttle:20,1')
        ->name('submit');

    });
    Route::middleware('role:petugas')->prefix('petugas/pembayaran')->name('staff.')->group(function () {
        Route::get('/', [TicketPaymentController::class, 'index'])->name('index');
        Route::get('/{payment}', [TicketPaymentController::class, 'detail'])->whereNumber('payment')->name('show');
        Route::post('/{payment}/konfirmasi', [TicketPaymentController::class, 'confirm'])->whereNumber('payment')->name('confirm');
        Route::post('/{payment}/tolak', [TicketPaymentController::class, 'reject'])->whereNumber('payment')->name('reject');
    });
    Route::get('/pembayaran/bukti/{submission}', [TicketPaymentController::class, 'proof'])->whereNumber('submission')->name('proof');
});
