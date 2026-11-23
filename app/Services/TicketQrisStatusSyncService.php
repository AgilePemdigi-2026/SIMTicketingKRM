<?php

namespace App\Services;

use App\Models\TicketOrder;
use App\Models\TicketPaymentGatewayAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class TicketQrisStatusSyncService
{
    public function __construct(
        private TicketQrisWebhookService $webhook
    ) {}

    /**
     * Sinkronisasi QRIS untuk pesanan online milik Wisatawan.
     */
    public function syncFor(
        User $user,
        string $orderCode
    ): TicketPaymentGatewayAttempt {
        abort_unless($user->role === 'wisatawan', 403);

        return $this->syncAuthorized($user, $orderCode, false);
    }

    /**
     * Sinkronisasi QRIS untuk pesanan loket yang dibuat Petugas terkait.
     */
    public function syncForCounter(
        User $staff,
        string $orderCode
    ): TicketPaymentGatewayAttempt {
        abort_unless($staff->role === 'petugas', 403);

        return $this->syncAuthorized($staff, $orderCode, true);
    }

    private function syncAuthorized(
        User $user,
        string $orderCode,
        bool $counter
    ): TicketPaymentGatewayAttempt {
        // Sinkronisasi manual menggunakan Status API di Sandbox.
        abort_unless(
            config('midtrans.is_production') === false,
            503,
            'Sinkronisasi manual hanya tersedia di Midtrans Sandbox.'
        );

        $serverKey = config('midtrans.server_key');

        abort_unless(
            is_string($serverKey) && $serverKey !== '',
            503,
            'Konfigurasi Midtrans belum lengkap.'
        );

        // Pembatasan kepemilikan tidak boleh dihapus untuk Wisatawan.
        $query = TicketOrder::query()->where('code', $orderCode);

        if ($counter) {
            $query->where('sale_channel', 'counter')
                ->where('staff_user_id', $user->id)
                ->whereNull('user_id');
        } else {
            $query->where('user_id', $user->id);
        }

        $order = $query
            ->with('payment.gatewayAttempts')
            ->firstOrFail();

        $payment = $order->payment;

        abort_unless(
            $payment?->method === 'qris',
            409,
            'Pesanan bukan pembayaran QRIS.'
        );

        $attempt = $payment->gatewayAttempts
            ->sortByDesc('id')
            ->first();

        abort_unless(
            $attempt !== null,
            409,
            'Transaksi QRIS belum dibuat.'
        );

        // Pembayaran lunas tidak perlu diubah atau diproses ulang.
        if ($attempt->status === 'paid') {
            return $attempt;
        }

        abort_unless(
            filled($attempt->reference_id)
                && filled($attempt->provider_payment_id),
            409,
            'Identitas transaksi gateway belum lengkap.'
        );

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->timeout(15)
            ->get(
                'https://api.sandbox.midtrans.com/v2/'
                . rawurlencode($attempt->reference_id)
                . '/status'
            );

        abort_unless(
            $response->successful(),
            502,
            'Status transaksi gagal diperiksa ke Midtrans.'
        );

        $data = $response->json();

        abort_unless(
            is_array($data),
            502,
            'Respons Midtrans tidak valid.'
        );

        foreach ([
            'order_id',
            'transaction_id',
            'transaction_status',
            'status_code',
            'gross_amount',
            'payment_type',
        ] as $field) {
            abort_unless(
                isset($data[$field])
                    && is_string($data[$field])
                    && trim($data[$field]) !== '',
                502,
                'Data transaksi Midtrans tidak lengkap.'
            );
        }

        abort_unless(
            $data['order_id'] === $attempt->reference_id
                && $data['transaction_id'] === $attempt->provider_payment_id
                && $data['payment_type'] === 'qris'
                && ($data['currency'] ?? 'IDR') === 'IDR',
            502,
            'Identitas transaksi Midtrans tidak cocok.'
        );

        abort_unless(
            in_array(
                $data['transaction_status'],
                ['pending', 'settlement', 'expire', 'deny', 'cancel'],
                true
            ),
            502,
            'Status transaksi Midtrans tidak dikenal.'
        );

        // Respons Status API berasal dari HTTPS + autentikasi Server Key.
        // Webhook POST tetap wajib melalui verifikasi signature pada controller.
        // Service webhook menangani pemeriksaan nominal, lock, dan idempotensi.
        $this->webhook->handle($data);

        return $attempt->fresh();
    }
}
