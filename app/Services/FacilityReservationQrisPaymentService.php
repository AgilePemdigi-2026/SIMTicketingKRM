<?php

namespace App\Services;

use App\Models\FacilityReservation;
use App\Models\FacilityReservationPayment;
use App\Models\FacilityReservationPaymentAttempt;
use App\Models\User;
use App\Support\TicketPurchaseDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class FacilityReservationQrisPaymentService
{
    public function __construct(
        private TicketPurchaseDatabase $database,
        private MidtransQrisGateway $gateway
    ) {}

    public function createFor(
        User $user,
        string $reservationCode
    ): FacilityReservationPaymentAttempt {

        // 1. Siapkan attempt di PostgreSQL.
        // Jangan memanggil Midtrans saat transaksi DB aktif.
        [$attempt, $isNew] = $this->database
            ->connection()
            ->transaction(function () use (
                $user,
                $reservationCode
            ) {

                abort_unless(
                    $user->role === 'wisatawan',
                    403
                );

                $reservation = FacilityReservation::query()
                    ->where('code', $reservationCode)
                    ->where('user_id', $user->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                // Untuk alur pembayaran setelah checkout:
                // pending boleh dibayar selama hold masih aktif.
                // Aturan ini perlu diselaraskan dengan Admin.
                abort_unless(
                    in_array(
                        $reservation->administrative_status,
                        ['pending', 'approved', 'confirmed'],
                        true
                    ),
                    409,
                    'Status reservasi tidak dapat dibayar.'
                );

                if (
                    $reservation->administrative_status
                    === 'pending'
                ) {
                    abort_unless(
                        $reservation->capacity_hold_until
                        && $reservation->capacity_hold_until
                            ->isFuture(),
                        409,
                        'Batas waktu reservasi telah berakhir.'
                    );
                }

                // Harga berasal dari data reservasi backend.
                $amount = (string) $reservation->total_amount;

                abort_unless(
                    preg_match(
                        '/\A[1-9][0-9]*\.00\z/D',
                        $amount
                    ) === 1,
                    422,
                    'Nominal reservasi tidak valid.'
                );

                $rupiah = substr($amount, 0, -3);

                $amountInteger = filter_var(
                    $rupiah,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );

                abort_unless(
                    $amountInteger !== false,
                    422,
                    'Nominal melebihi batas yang didukung.'
                );

                // Satu reservasi hanya punya satu payment.
                $payment = FacilityReservationPayment::query()
                    ->where(
                        'facility_reservation_id',
                        $reservation->getKey()
                    )
                    ->lockForUpdate()
                    ->first();

                if (! $payment) {
                    $payment = FacilityReservationPayment::create([
                        'facility_reservation_id'
                            => $reservation->getKey(),
                        'method' => 'qris',
                        'status' => 'pending',
                        'amount' => $amount,
                    ]);
                }

                abort_unless(
                    $payment->status === 'pending'
                    && $payment->method === 'qris',
                    409,
                    'Pembayaran tidak dapat diproses.'
                );

                abort_unless(
                    (string) $payment->amount === $amount,
                    409,
                    'Nominal pembayaran tidak sesuai.'
                );

                $latest = $payment->attempts()
                    ->latest('id')
                    ->first();

                if ($latest) {

                    // Pakai kembali QRIS yang masih aktif.
                    if (
                        $latest->status === 'pending'
                        && $latest->qr_image_url
                        && $latest->expires_at
                        && $latest->expires_at->isFuture()
                    ) {
                        return [$latest, false];
                    }

                    // Status belum pasti harus disinkronkan.
                    // Jangan membuat charge ganda.
                    abort_unless(
                        in_array(
                            $latest->status,
                            ['expired', 'failed', 'cancelled'],
                            true
                        ),
                        409,
                        'Periksa status QRIS sebelumnya sebelum mencoba lagi.'
                    );
                }

                $attempt = $payment->attempts()->create([
                    'reference_id' => (string) Str::uuid(),
                    'amount' => $amount,
                    'currency' => 'IDR',
                    'status' => 'initiating',
                ]);

                return [$attempt, true];

            }, 3);

        // Jika QRIS aktif sudah ada, gunakan kembali.
        if (! $isNew) {
            return $attempt;
        }

        // 2. Hubungi Midtrans Sandbox.
        // Gunakan webhook khusus reservasi.
        try {

            $result = $this->gateway
                ->createReservationQris(
                    $attempt->reference_id,
                    (int) $attempt->amount
                );

        } catch (Throwable $exception) {

            // Timeout tidak berarti transaksi gagal.
            // Status dibuat unknown agar tidak diulang
            // sebelum dicek ke Midtrans.
            $this->database->connection()
                ->transaction(function () use ($attempt) {

                    $locked = FacilityReservationPaymentAttempt::query()
                        ->whereKey($attempt->getKey())
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($locked->status === 'initiating') {
                        $locked->update([
                            'status' => 'initiation_unknown',
                        ]);
                    }

                }, 3);

            throw $exception;
        }

        // 3. Simpan hasil Midtrans ke PostgreSQL.
        return $this->database->connection()
            ->transaction(function () use (
                $attempt,
                $result
            ) {

                $locked = FacilityReservationPaymentAttempt::query()
                    ->whereKey($attempt->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                // Webhook bisa datang lebih cepat daripada
                // proses penyimpanan hasil charge.
                if ($locked->status === 'paid') {
                    abort_unless(
                        $locked->provider_payment_id
                            === $result['transaction_id'],
                        409,
                        'ID transaksi Midtrans tidak cocok.'
                    );

                    return $locked;
                }

                if ($locked->status !== 'initiating') {
                    throw new RuntimeException(
                        'Status percobaan QRIS tidak sesuai.'
                    );
                }

                abort_unless(
                    ($result['status'] ?? null) === 'pending'
                    && is_string(
                        $result['transaction_id'] ?? null
                    )
                    && is_string(
                        $result['qr_image_url'] ?? null
                    ),
                    502,
                    'Respons QRIS Midtrans tidak valid.'
                );

                $expiryTime = $result['expires_at'] ?? null;

                $locked->update([
                    'provider_payment_id'
                        => $result['transaction_id'],

                    'qr_image_url'
                        => $result['qr_image_url'],

                    'qr_string'
                        => $result['qr_string'] ?? null,

                    'expires_at' =>
                        is_string($expiryTime)
                        && trim($expiryTime) !== ''
                            ? Carbon::parse(
                                $expiryTime,
                                'Asia/Jakarta'
                            )->utc()
                            : null,

                    'status' => 'pending',
                ]);

                return $locked->refresh();

            }, 3);
    }
}
