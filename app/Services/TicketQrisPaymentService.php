<?php

namespace App\Services;

use App\Contracts\TicketQrisGateway;
use App\Models\TicketOrder;
use App\Models\TicketPayment;
use App\Models\TicketPaymentGatewayAttempt;
use App\Models\User;
use App\Support\TicketPurchaseDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class TicketQrisPaymentService
{
    public function __construct(
        private TicketPurchaseDatabase $database,
        private TicketQrisGateway $gateway
    ) {}

    /**
     * QRIS untuk transaksi walk-in yang dibuat oleh Petugas.
     */
    public function createForCounter(
        User $staff,
        string $orderCode
    ): TicketPaymentGatewayAttempt {
        abort_unless($staff->role === 'petugas', 403);

        return $this->createAuthorized($staff, $orderCode, true);
    }

    /**
     * QRIS Wisatawan tetap hanya dapat diakses pemilik pesanan.
     */
    public function createFor(
        User $user,
        string $orderCode
    ): TicketPaymentGatewayAttempt {
        abort_unless($user->role === 'wisatawan', 403);

        return $this->createAuthorized($user, $orderCode, false);
    }

    /**
     * Jalur Midtrans bersama dengan pembatasan kepemilikan berdasarkan role.
     */
    private function createAuthorized(
        User $user,
        string $orderCode,
        bool $counter
    ): TicketPaymentGatewayAttempt {
        // 1. Periksa pesanan dan buat attempt di dalam transaksi database.
        // Request eksternal ke Midtrans dilakukan setelah transaksi DB selesai.
        [$attempt, $isNew] = $this->database->connection()->transaction(
            function () use ($user, $orderCode, $counter) {
                $query = TicketOrder::query()->where('code', $orderCode);

                if ($counter) {
                    // Petugas tidak boleh menggunakan pesanan Petugas lain
                    // ataupun transaksi pembelian online Wisatawan.
                    $query->where('sale_channel', 'counter')
                        ->where('staff_user_id', $user->id)
                        ->whereNull('user_id');
                } else {
                    // Alur online Wisatawan tetap terbatas pada pemilik akun.
                    $query->where('user_id', $user->id);
                }

                $order = $query->lockForUpdate()->firstOrFail();

                abort_unless(
                    $order->status === 'pending_payment',
                    409,
                    'Pesanan tidak dapat dibayar.'
                );

                $payment = TicketPayment::where('ticket_order_id', $order->id)
                    ->lockForUpdate()
                    ->first();

                abort_unless(
                    $payment
                        && $payment->method === 'qris'
                        && $payment->status === 'pending',
                    409,
                    'Pilih metode pembayaran QRIS terlebih dahulu.'
                );

                abort_unless(
                    $payment->amount === $order->total,
                    409,
                    'Nominal pesanan tidak sesuai.'
                );

                // Hindari pembuatan transaksi Midtrans ganda.
                $latest = $payment->gatewayAttempts()
                    ->latest('id')
                    ->first();

                if ($latest) {
                    if (
                        $latest->status === 'pending'
                        && $latest->qr_image_url
                    ) {
                        return [$latest, false];
                    }

                    // Gunakan status yang benar-benar disimpan webhook.
                    abort_unless(
                        in_array(
                            $latest->status,
                            ['expired', 'cancelled', 'failed'],
                            true
                        ),
                        409,
                        'Transaksi QRIS masih diproses atau perlu diperiksa.'
                    );
                }

                $amount = (string) $payment->amount;

                abort_unless(
                    preg_match('/^\d+\.00$/', $amount) === 1
                        && (int) $amount > 0,
                    422,
                    'Nominal QRIS tidak valid.'
                );

                $attempt = $payment->gatewayAttempts()->create([
                    'reference_id' => (string) Str::uuid(),
                    'amount' => $payment->amount,
                    'currency' => 'IDR',
                    'status' => 'initiating',
                ]);

                return [$attempt, true];
            }
        );

        if (! $isNew) {
            return $attempt;
        }

        // 2. Panggil Midtrans di luar transaksi database.
        try {
            $result = $this->gateway->createQris(
                $attempt->reference_id,
                (int) $attempt->amount
            );
        } catch (Throwable $exception) {
            // Hasil request timeout/tidak pasti tidak boleh otomatis
            // membuat transaksi baru dengan referensi berbeda.
            $this->database->connection()->transaction(
                function () use ($attempt) {
                    $locked = TicketPaymentGatewayAttempt::whereKey($attempt->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($locked->status === 'initiating') {
                        $locked->update(['status' => 'initiation_unknown']);
                    }
                }
            );

            throw $exception;
        }

        // 3. Simpan respons Midtrans.
        return $this->database->connection()->transaction(
            function () use ($attempt, $result) {
                $locked = TicketPaymentGatewayAttempt::whereKey($attempt->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->status !== 'initiating'
                    && $locked->status !== 'paid'
                    && $locked->status !== 'settlement'
                ) {
                    throw new RuntimeException('Status percobaan QRIS tidak sesuai.');
                }

                $locked->provider_payment_id = $result['transaction_id'];
                $locked->qr_image_url = $result['qr_image_url'];
                $locked->qr_string = $result['qr_string'];

                // Waktu Midtrans tanpa timezone ditafsirkan sebagai WIB.
                $expiryTime = $result['expires_at'] ?? null;
                $locked->expires_at =
                    is_string($expiryTime) && trim($expiryTime) !== ''
                        ? \Illuminate\Support\Carbon::parse(
                            $expiryTime,
                            'Asia/Jakarta'
                        )->utc()
                        : null;

                if ($locked->status === 'initiating') {
                    $locked->status = 'pending';
                }

                $locked->save();

                return $locked->refresh();
            }
        );
    }
}
