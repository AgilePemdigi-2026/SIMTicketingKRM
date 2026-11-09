<?php

namespace App\Services;

use App\Events\TicketPaymentConfirmed;
use App\Models\TicketOrder;
use App\Models\TicketPayment;
use App\Models\TicketPaymentGatewayAttempt;
use Illuminate\Support\Facades\DB;

class TicketQrisWebhookService
{
    public function handle(array $payload): void
    {
        // Signature diverifikasi terlebih dahulu oleh controller.
        // Di sini kita mencocokkan transaksi dengan database.

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

        $attempt = TicketPaymentGatewayAttempt::query()
            ->where('reference_id', $referenceId)
            ->firstOrFail();

        $payment = TicketPayment::query()
            ->findOrFail($attempt->ticket_payment_id);

        $order = TicketOrder::query()
            ->whereHas('payment', function ($query) use ($payment) {
                $query->whereKey($payment->getKey());
            })
            ->firstOrFail();

        DB::transaction(function () use (
            $order,
            $payment,
            $attempt,
            $payload,
            $providerId,
            $status
        ) {
            // Urutan lock: pesanan -> pembayaran -> attempt.
            $lockedOrder = TicketOrder::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPayment = TicketPayment::query()
                ->whereKey($payment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAttempt = TicketPaymentGatewayAttempt::query()
                ->whereKey($attempt->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (string) $lockedAttempt->ticket_payment_id
                    === (string) $lockedPayment->getKey(),
                409,
                'Relasi pembayaran tidak sesuai.'
            );

            abort_unless(
                $lockedPayment->method === 'qris',
                409,
                'Pesanan bukan pembayaran QRIS.'
            );

            abort_unless(
                $lockedAttempt->currency === 'IDR',
                422,
                'Mata uang transaksi tidak valid.'
            );

            // Periksa nominal secara presisi, tanpa float.
            $amounts = [
                $payload['gross_amount'],
                $lockedAttempt->amount,
                $lockedPayment->amount,
                $lockedOrder->total,
            ];

            $normalized = array_map(
                fn ($amount) => $this->normalizeAmount($amount),
                $amounts
            );

            abort_if(
                in_array(null, $normalized, true)
                    || count(array_unique($normalized)) !== 1,
                422,
                'Nominal transaksi tidak cocok.'
            );

            // Jangan terima ID Midtrans berbeda
            // untuk attempt yang sudah tercatat.
            abort_if(
                $lockedAttempt->provider_payment_id !== null
                    && $lockedAttempt->provider_payment_id
                        !== $providerId,
                409,
                'ID transaksi Midtrans tidak sesuai.'
            );

            // Jika pembayaran sudah lunas, jangan
            // menerbitkan event untuk kedua kalinya.
            if (
                $lockedPayment->status === 'paid'
                || $lockedOrder->status === 'paid'
            ) {
                return;
            }

            // Pending hanya memperbarui attempt.
            if ($status === 'pending') {
                if (
                    !in_array(
                        $lockedAttempt->status,
                        ['expired', 'failed', 'cancelled'],
                        true
                    )
                ) {
                    $lockedAttempt->update([
                        'status' => 'pending',
                        'provider_payment_id' => $providerId,
                    ]);
                }

                return;
            }

            // QRIS gagal atau kedaluwarsa.
            if (in_array($status, ['expire', 'deny', 'cancel'], true)) {
                $attemptStatus = match ($status) {
                    'expire' => 'expired',
                    'deny' => 'failed',
                    'cancel' => 'cancelled',
                };

                $lockedAttempt->update([
                    'status' => $attemptStatus,
                    'provider_payment_id' => $providerId,
                ]);

                // Jangan menandai pesanan lunas.
                return;
            }

            // Status selain settlement tidak boleh
            // mengonfirmasi pembayaran.
            if ($status !== 'settlement') {
                return;
            }

            abort_unless(
                $payload['status_code'] === '200',
                422,
                'Status pembayaran tidak valid.'
            );

            abort_if(
                isset($payload['fraud_status'])
                    && $payload['fraud_status'] !== 'accept',
                422,
                'Status keamanan transaksi tidak valid.'
            );

            // Hanya pembayaran yang masih menunggu
            // yang boleh berubah menjadi lunas.
            abort_unless(
                $lockedPayment->status === 'pending'
                    && $lockedOrder->status === 'pending_payment',
                409,
                'Status pesanan tidak dapat dikonfirmasi.'
            );

            $paidAt = now();

            // 1. Konfirmasi attempt gateway.
            $lockedAttempt->update([
                'status' => 'paid',
                'provider_payment_id' => $providerId,
                'paid_at' => $paidAt,
            ]);

            // 2. Konfirmasi pembayaran.
            // confirmed_by null karena otomatis oleh gateway.
            $lockedPayment->update([
                'status' => 'paid',
                'merchant_reference' => $providerId,
                'confirmed_by' => null,
                'paid_at' => $paidAt,
            ]);

            // 3. Konfirmasi pesanan.
            $lockedOrder->update([
                'status' => 'paid',
            ]);

            // 4. Gunakan event yang sama dengan Cash.
            // Event ini memiliki ShouldDispatchAfterCommit.
            TicketPaymentConfirmed::dispatch(
                $lockedOrder->code,
                $lockedPayment->code,
                $lockedPayment->method,
                (string) $lockedPayment->amount,
                $paidAt->toIso8601String()
            );
        }, 3);
    }

    /**
     * Normalisasi nominal menjadi string dua desimal.
     * Contoh: 15000, 15000.0, 15000.00 -> 15000.00
     */
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