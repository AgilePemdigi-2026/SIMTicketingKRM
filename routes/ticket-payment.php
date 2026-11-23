<?php

use App\Http\Controllers\TicketPaymentController;
use App\Http\Controllers\Petugas\CounterQrisController;
use App\Http\Controllers\Petugas\CounterCashController;
use App\Http\Middleware\EnsureMasterTicketDatabase;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransTicketWebhookController;
use App\Http\Controllers\Petugas\CashBookingSuccessController;

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

    // Halaman E-Tiket asli setelah pembayaran berhasil.
    Route::get(
        '/e-tiket',
        [TicketPaymentController::class, 'eTicketPlaceholder']
    )->name('e-ticket-placeholder');

    // E-Tiket Sementara untuk pesanan Cash yang belum lunas.
    Route::get(
        '/e-tiket-sementara',
        [TicketPaymentController::class, 'temporaryETicket']
    )->name('e-ticket-temporary');

    // Download PDF E-Tiket setelah pembayaran lunas.
    Route::get(
        '/e-tiket/download',
        [TicketPaymentController::class, 'downloadETicket']
    )->name('e-ticket.download');

    Route::post('/bukti', [TicketPaymentController::class, 'submit'])
        ->middleware('throttle:20,1')
        ->name('submit');

    });
    Route::middleware('role:petugas')
        ->prefix('petugas/pembayaran')
        ->name('staff.')
        ->group(function () {

            Route::get('/', [TicketPaymentController::class, 'index'])
                ->name('index');

            // Halaman sukses Bayar Cash Booking.
            Route::get(
                '/{payment}/berhasil',
                [CashBookingSuccessController::class, 'show']
            )
                ->whereNumber('payment')
                ->name('booking-success');

            // Cetak E-Tiket setelah pembayaran berhasil.
            Route::get(
                '/{payment}/cetak',
                [CashBookingSuccessController::class, 'print']
            )
                ->whereNumber('payment')
                ->name('booking-print');

            Route::get('/{payment}', [TicketPaymentController::class, 'detail'])
                ->whereNumber('payment')
                ->name('show');

            Route::post('/{payment}/konfirmasi', [TicketPaymentController::class, 'confirm'])
                ->whereNumber('payment')
                ->name('confirm');

            Route::post('/{payment}/tolak', [TicketPaymentController::class, 'reject'])
                ->whereNumber('payment')
                ->name('reject');
                });
    Route::get('/pembayaran/bukti/{submission}', [TicketPaymentController::class, 'proof'])->whereNumber('submission')->name('proof');
});

// QRIS loket menggunakan service Midtrans yang sama dengan Wisatawan,
// tetapi endpoint dan otorisasi Petugas dibuat terpisah.
Route::middleware(['web', 'auth', 'role:petugas', EnsureMasterTicketDatabase::class])
    ->prefix('petugas/loket/transaksi/{payment}/qris')
    ->name('petugas.loket.qris.')
    ->group(function () {
        Route::get('/', [CounterQrisController::class, 'show'])
            ->whereNumber('payment')
            ->name('show');

        Route::post('/buat', [CounterQrisController::class, 'create'])
            ->whereNumber('payment')
            ->middleware('throttle:5,1')
            ->name('create');

        Route::post('/sinkronisasi', [CounterQrisController::class, 'sync'])
            ->whereNumber('payment')
            ->middleware('throttle:5,1')
            ->name('sync');
    });


// Terima pembayaran tunai khusus pembelian walk-in oleh Petugas.
Route::get(
    'petugas/loket/transaksi/{payment}/cash',
    [CounterCashController::class, 'show']
)
    ->middleware(['web', 'auth', 'role:petugas', EnsureMasterTicketDatabase::class])
    ->whereNumber('payment')
    ->name('petugas.loket.cash.show');
