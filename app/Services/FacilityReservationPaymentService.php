<?php

namespace App\Services;

use App\Models\FacilityReservation;
use App\Models\FacilityReservationPayment;
use App\Models\User;
use App\Support\TicketPurchaseDatabase;

class FacilityReservationPaymentService
{
    public function __construct(
        private TicketPurchaseDatabase $database
    ) {}

    /**
     * Menyiapkan catatan pembayaran untuk reservasi.
     *
     * Belum membuat transaksi QRIS di Midtrans.
     */
    public function prepare(
        User $user,
        string $reservationCode
    ): FacilityReservationPayment {

        abort_unless(
            $user->role === 'wisatawan',
            403
        );

        return $this->database->connection()
            ->transaction(function () use (
                $user,
                $reservationCode
            ) {

                // Ambil reservasi milik wisatawan login.
                $reservation = FacilityReservation::query()
                    ->where('code', $reservationCode)
                    ->where('user_id', $user->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                // Reservasi yang ditolak atau dibatalkan
                // tidak boleh disiapkan untuk pembayaran.
                abort_if(
                    in_array(
                        $reservation->administrative_status,
                        ['cancelled', 'rejected'],
                        true
                    ),
                    409,
                    'Reservasi tidak dapat dibayar.'
                );

                // Nominal wajib berasal dari backend,
                // bukan dari input browser.
                $amount = (string) $reservation->total_amount;

                abort_unless(
                    preg_match('/\A\d+\.00\z/D', $amount)
                        && (int) explode('.', $amount, 2)[0] > 0,
                    422,
                    'Nominal reservasi tidak valid.'
                );

                // Cegah pembuatan pembayaran ganda.
                $payment = FacilityReservationPayment::query()
                    ->where(
                        'facility_reservation_id',
                        $reservation->getKey()
                    )
                    ->lockForUpdate()
                    ->first();

                if ($payment) {

                    abort_if(
                        $payment->status === 'paid',
                        409,
                        'Reservasi sudah dibayar.'
                    );

                    abort_unless(
                        (string) $payment->amount === $amount,
                        409,
                        'Nominal pembayaran tidak sesuai.'
                    );

                    return $payment;
                }

                // Satu reservasi hanya memiliki
                // satu catatan pembayaran.
                return FacilityReservationPayment::create([
                    'facility_reservation_id'
                        => $reservation->getKey(),

                    'method' => 'qris',
                    'status' => 'pending',
                    'amount' => $amount,
                ]);

            }, 3);
    }
}
