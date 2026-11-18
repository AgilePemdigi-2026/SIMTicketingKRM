<?php

use App\Http\Controllers\MidtransReservationWebhookController;
use App\Http\Middleware\EnsureMasterTicketDatabase;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Wisatawan\FacilityReservationPaymentController;

// Webhook Midtrans khusus pembayaran reservasi.
// Tidak memerlukan login wisatawan.
// Keaslian notifikasi diverifikasi oleh controller.
Route::post(
    'webhooks/midtrans/reservation',
    [MidtransReservationWebhookController::class, 'handle']
)
    ->middleware([
        EnsureMasterTicketDatabase::class,
        'throttle:60,1',
    ])
    ->name('facility-reservation.midtrans.webhook');

/*
|--------------------------------------------------------------------------
| Pembayaran QRIS Reservasi Wisatawan
|--------------------------------------------------------------------------
|
| Hanya wisatawan yang sudah login.
| Verifikasi kepemilikan reservasi dilakukan oleh service.
|
*/

Route::middleware([
    EnsureMasterTicketDatabase::class,
    'web',
    'auth',
    'role:wisatawan',
])
    ->prefix('wisatawan/reservasi/{code}/pembayaran')
    ->whereUuid('code')
    ->name('facility-reservation.payment.')
    ->group(function () {

            Route::get(
            '/',
            [
                FacilityReservationPaymentController::class,
                'show',
            ]
        )->name('show');

        Route::get(
            '/invoice',
            [
                FacilityReservationPaymentController::class,
                'showInvoice',
            ]
        )->name('invoice');

        Route::get(
            '/invoice/pdf',
            [
                FacilityReservationPaymentController::class,
                'downloadInvoice',
            ]
        )->name('invoice.pdf');

        Route::post(
            '/qris',
            [
                FacilityReservationPaymentController::class,
                'createQris',
            ]
        )
            ->middleware('throttle:5,1')
            ->name('qris.create');

        Route::post(
            '/qris/sync',
            [
                FacilityReservationPaymentController::class,
                'syncQris',
            ]
        )
            ->middleware('throttle:5,1')
            ->name('qris.sync');

    });