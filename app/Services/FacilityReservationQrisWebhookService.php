<?php

namespace App\Services;

use App\Models\FacilityReservation;
use App\Models\FacilityReservationPayment;
use App\Models\FacilityReservationPaymentAttempt;
use App\Support\TicketPurchaseDatabase;

class FacilityReservationQrisWebhookService
{
    public function __construct(
        private TicketPurchaseDatabase $database
    ) {}

    /**
     * Memproses notifikasi pembayaran reservasi.
     *
     * PENTING:
     * Signature webhook wajib diverifikasi oleh
     * controller sebelum method ini dipanggil.
     */
    public function handle(array $payload): void
    {
        abort_unless(
            ($payload['payment_type'] ?? null) === 'qris',
            422,
            'Metode pembayaran tidak sesuai.'
        );

        abort_unless(
            ($payload['currency'] ?? 'IDR') === 'IDR',
            422,
            'Mata uang tidak sesuai.'
        );

        $referenceId = $payload['order_id'];
        $providerId = $payload['transaction_id'];
        $status = $payload['transaction_status'];

        // Cari transaksi di tabel pembayaran reservasi,
        // bukan tabel pembayaran tiket.
        $attempt = FacilityReservationPaymentAttempt::query()
            ->where('reference_id', $referenceId)
            ->firstOrFail();

        $payment = $attempt->payment()->firstOrFail();

        $this->database->connection()->transaction(
            function () use (
                $attempt,
                $payment,
                $payload,
                $providerId,
                $status
            ) {
                // Lock berurutan untuk menjaga konsistensi.
                $reservation = FacilityReservation::query()
                    ->whereKey($payment->facility_reservation_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedPayment = FacilityReservationPayment::query()
                    ->whereKey($payment->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedAttempt = FacilityReservationPaymentAttempt::query()
                    ->whereKey($attempt->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_unless(
                    (string) $lockedPayment->facility_reservation_id
                        === (string) $reservation->getKey()
                    &&
                    (string) $lockedAttempt->facility_reservation_payment_id
                        === (string) $lockedPayment->getKey(),
                    409,
                    'Relasi pembayaran tidak sesuai.'
                );

                abort_unless(
                    $lockedPayment->method === 'qris'
                        && $lockedAttempt->currency === 'IDR',
                    422,
                    'Metode atau mata uang tidak valid.'
                );

                // Nominal harus cocok di seluruh catatan.
                $amounts = [
                    $payload['gross_amount'],
                    $lockedAttempt->amount,
                    $lockedPayment->amount,
                    $reservation->total_amount,
                ];

                $normalized = array_map(
                    fn ($amount) => $this->normalizeAmount($amount),
                    $amounts
                );

                abort_if(
                    in_array(null, $normalized, true)
                        || count(array_unique($normalized)) !== 1,
                    422,
                    'Nominal pembayaran tidak sesuai.'
                );

                // Jangan menerima ID Midtrans berbeda.
                abort_if(
                    $lockedAttempt->provider_payment_id !== null
                        && $lockedAttempt->provider_payment_id
                            !== $providerId,
                    409,
                    'ID transaksi Midtrans tidak sesuai.'
                );

                // Pembayaran yang sudah lunas tidak
                // boleh diproses ulang.
                if ($lockedPayment->status === 'paid') {
                    return;
                }

                if ($status === 'pending') {
                    if (!in_array(
                        $lockedAttempt->status,
                        ['expired', 'failed', 'cancelled', 'paid'],
                        true
                    )) {
                        $lockedAttempt->update([
                            'status' => 'pending',
                            'provider_payment_id' => $providerId,
                        ]);
                    }

                    return;
                }

                // Pembayaran gagal, dibatalkan, atau kedaluwarsa.
                if (in_array(
                    $status,
                    ['expire', 'deny', 'cancel'],
                    true
                )) {
                    $attemptStatus = match ($status) {
                        'expire' => 'expired',
                        'deny' => 'failed',
                        'cancel' => 'cancelled',
                    };

                    $lockedAttempt->update([
                        'status' => $attemptStatus,
                        'provider_payment_id' => $providerId,
                    ]);

                    return;
                }

                // Hanya settlement yang boleh dianggap lunas.
                if ($status !== 'settlement') {
                    return;
                }

                abort_unless(
                    (string) $payload['status_code'] === '200',
                    422,
                    'Status pembayaran tidak valid.'
                );

                abort_if(
                    isset($payload['fraud_status'])
                        && $payload['fraud_status'] !== 'accept',
                    422,
                    'Status keamanan transaksi tidak valid.'
                );

                abort_unless(
                    $lockedPayment->status === 'pending',
                    409,
                    'Status pembayaran tidak dapat dikonfirmasi.'
                );

                $paidAt = now();

                // Konfirmasi percobaan pembayaran.
                $lockedAttempt->update([
                    'status' => 'paid',
                    'provider_payment_id' => $providerId,
                    'paid_at' => $paidAt,
                ]);

                // Konfirmasi pembayaran reservasi.
                $lockedPayment->update([
                    'status' => 'paid',
                    'paid_at' => $paidAt,
                ]);

                // Status administratif reservasi tidak
                // diubah otomatis menjadi approved.
            },
            3
        );
    }

    private function normalizeAmount(mixed $value): ?string
    {
        if (!is_string($value) && !is_int($value)) {
            return null;
        }

        $amount = (string) $value;

        if (!preg_match('/\A\d+(?:\.\d{1,2})?\z/D', $amount)) {
            return null;
        }

        [$whole, $decimal] = array_pad(
            explode('.', $amount, 2),
            2,
            ''
        );

        return (ltrim($whole, '0') ?: '0')
            . '.'
            . str_pad($decimal, 2, '0');
    }
}
