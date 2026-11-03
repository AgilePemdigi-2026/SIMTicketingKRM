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

    public function createFor(
        User $user,
        string $orderCode
    ): TicketPaymentGatewayAttempt {

        // 1. Periksa pesanan dan siapkan percobaan QRIS.
        // Transaksi database selesai sebelum memanggil Midtrans.
        [$attempt, $isNew] = $this->database
            ->connection()
            ->transaction(function () use ($user, $orderCode) {

                abort_unless(
                    $user->role === 'wisatawan',
                    403
                );

                $order = TicketOrder::where('code', $orderCode)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_unless(
                    $order->status === 'pending_payment',
                    409,
                    'Pesanan tidak dapat dibayar.'
                );

                $payment = TicketPayment::where(
                    'ticket_order_id',
                    $order->id
                )->lockForUpdate()->first();

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

                // Jangan membuat transaksi Midtrans berulang
                // untuk pembayaran yang masih diproses.
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

                    abort_unless(
                        in_array(
                            $latest->status,
                            ['expire', 'cancel', 'deny', 'failed'],
                            true
                        ),
                        409,
                        'Transaksi QRIS masih diproses atau perlu diperiksa.'
                    );
                }

                // Harga tiket harus berupa rupiah bulat.
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
            });

        // QRIS lama yang masih pending digunakan kembali.
        if (! $isNew) {
            return $attempt;
        }

        // 2. Hubungi Midtrans di luar transaksi database.
        try {
            $result = $this->gateway->createQris(
                $attempt->reference_id,
                (int) $attempt->amount
            );
        } catch (Throwable $exception) {

            // Jika request gagal atau timeout, statusnya
            // dianggap belum pasti agar tidak membuat
            // transaksi gateway kedua secara otomatis.
            $this->database->connection()->transaction(
                function () use ($attempt) {
                    $locked = TicketPaymentGatewayAttempt::whereKey(
                        $attempt->id
                    )->lockForUpdate()->firstOrFail();

                    if ($locked->status === 'initiating') {
                        $locked->update([
                            'status' => 'initiation_unknown',
                        ]);
                    }
                }
            );

            throw $exception;
        }

        // 3. Simpan hasil transaksi Midtrans.
        return $this->database->connection()->transaction(
            function () use ($attempt, $result) {

                $locked = TicketPaymentGatewayAttempt::whereKey(
                    $attempt->id
                )->lockForUpdate()->firstOrFail();

                if (
                    $locked->status !== 'initiating'
                    && $locked->status !== 'paid'
                    && $locked->status !== 'settlement'
                ) {
                    throw new RuntimeException(
                        'Status percobaan QRIS tidak sesuai.'
                    );
                }

                $locked->provider_payment_id =
                    $result['transaction_id'];

                $locked->qr_image_url =
                    $result['qr_image_url'];

                $locked->qr_string =
                    $result['qr_string'];

                // Simpan waktu kedaluwarsa dari Midtrans.
                // Jika tidak ada informasi zona waktu,
                // gunakan Asia/Jakarta (WIB).
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
