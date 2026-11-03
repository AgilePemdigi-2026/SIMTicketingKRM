<?php

namespace App\Services;

use App\Contracts\TicketQrisGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransQrisGateway implements TicketQrisGateway
{
    public function createQris(
        string $referenceId,
        int $amount
    ): array {
        // Selama pengembangan, hanya izinkan Sandbox.
        if (config('midtrans.is_production') !== false) {
            throw new RuntimeException(
                'Integrasi ini hanya diizinkan pada Midtrans Sandbox.'
            );
        }

        $serverKey = config('midtrans.server_key');

        if (!is_string($serverKey) || trim($serverKey) === '') {
            throw new RuntimeException(
                'Server Key Midtrans belum dikonfigurasi.'
            );
        }

        if ($amount <= 0) {
            throw new RuntimeException(
                'Nominal pembayaran harus lebih dari nol.'
            );
        }

        $baseUrl = rtrim(
            config('midtrans.sandbox_url'),
            '/'
        );

        // Mengirim permintaan QRIS ke Midtrans.
        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('midtrans.timeout', 15))
            ->post($baseUrl . '/v2/charge', [
                'payment_type' => 'qris',

                'transaction_details' => [
                    'order_id' => $referenceId,
                    'gross_amount' => $amount,
                ],

                'qris' => [
                    'acquirer' => 'gopay',
                ],

                'custom_expiry' => [
                    'expiry_duration' => 15,
                    'unit' => 'minute',
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Midtrans Sandbox gagal membuat QRIS. HTTP '
                . $response->status()
            );
        }

        $data = $response->json();

        // Ambil URL gambar QR dari respons Midtrans.
        $qrAction = collect($data['actions'] ?? [])
            ->firstWhere('name', 'generate-qr-code');

        if (
            ($data['order_id'] ?? null) !== $referenceId
            || ($data['transaction_status'] ?? null) !== 'pending'
            || !is_string($data['transaction_id'] ?? null)
            || !is_string($qrAction['url'] ?? null)
        ) {
            throw new RuntimeException(
                'Respons transaksi QRIS Midtrans tidak sesuai.'
            );
        }
        $expiryTime = $data['expiry_time'] ?? null;

        // QRIS Midtrans bisa mengembalikan transaction_time
        // tanpa expiry_time. Karena request menggunakan
        // custom_expiry 15 menit, hitung dari waktu transaksi.
        if (
            !$expiryTime &&
            !empty($data['transaction_time'])
        ) {
            $expiryTime = \Illuminate\Support\Carbon::parse(
                $data['transaction_time'],
                'Asia/Jakarta'
            )
                ->addMinutes(15)
                ->toIso8601String();
        }

        return [
            'transaction_id' => $data['transaction_id'],
            'status' => $data['transaction_status'],
            'qr_image_url' => $qrAction['url'],
            'qr_string' => $data['qr_string'] ?? null,
            'expires_at' => $expiryTime,
        ];

    }
}
