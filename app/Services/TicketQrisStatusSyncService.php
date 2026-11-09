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

    public function syncFor(
        User $user,
        string $orderCode
    ): TicketPaymentGatewayAttempt {
        abort_unless($user->role === 'wisatawan', 403);

        // Sinkronisasi ini khusus Sandbox.
        abort_unless(
            config('midtrans.is_production') === false,
            503
        );

        $serverKey = config('midtrans.server_key');

        abort_unless(
            is_string($serverKey) && $serverKey !== '',
            503,
            'Konfigurasi Midtrans belum lengkap.'
        );

        // Wisatawan hanya boleh mengecek pesanannya sendiri.
        $order = TicketOrder::query()
            ->where('code', $orderCode)
            ->where('user_id', $user->id)
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

        // Pembayaran yang sudah lunas tidak perlu diproses lagi.
        if ($attempt->status === 'paid') {
            return $attempt;
        }

        abort_unless(
            filled($attempt->reference_id)
                && filled($attempt->provider_payment_id),
            409,
            'Identitas transaksi gateway belum lengkap.'
        );

        // Ambil status langsung dari server Midtrans Sandbox.
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

        // Pastikan respons memiliki semua informasi wajib.
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

        // Respons harus sesuai dengan transaksi yang disimpan.
        abort_unless(
            $data['order_id'] === $attempt->reference_id
                && $data['transaction_id']
                    === $attempt->provider_payment_id
                && $data['payment_type'] === 'qris'
                && ($data['currency'] ?? 'IDR') === 'IDR',
            502,
            'Identitas transaksi Midtrans tidak cocok.'
        );

        // Jangan memproses status yang tidak didukung.
        abort_unless(
            in_array(
                $data['transaction_status'],
                ['pending', 'settlement', 'expire', 'deny', 'cancel'],
                true
            ),
            502,
            'Status transaksi Midtrans tidak dikenal.'
        );
        // Respons ini berasal dari GET Status API Midtrans
        // melalui HTTPS dan Basic Authentication Server Key.
        //
        // Signature SHA-512 tetap WAJIB diverifikasi untuk
        // webhook POST yang masuk dari luar aplikasi.
        //
        // Identitas transaksi, metode pembayaran, mata uang,
        // dan nominal tetap diperiksa sebelum perubahan status.

        // Gunakan mekanisme pembaruan status yang sama
        // dengan webhook, termasuk penguncian database,
        // pemeriksaan nominal, dan pencegahan duplikasi.
        $this->webhook->handle($data);

        return $attempt->fresh();
    }
}
