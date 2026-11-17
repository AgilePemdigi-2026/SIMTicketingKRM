<?php

namespace App\Services;

use App\Models\FacilityReservation;
use App\Models\FacilityReservationPaymentAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class FacilityReservationQrisStatusSyncService
{
    public function __construct(
        private FacilityReservationQrisWebhookService $webhook
    ) {}

    public function syncFor(
        User $user,
        string $reservationCode
    ): FacilityReservationPaymentAttempt {

        // Hanya wisatawan yang dapat melakukan sinkronisasi.
        abort_unless(
            $user->role === 'wisatawan',
            403
        );

        // Integrasi ini hanya menggunakan Midtrans Sandbox.
        abort_unless(
            config('midtrans.is_production') === false,
            503
        );

        $serverKey = config('midtrans.server_key');

        abort_unless(
            is_string($serverKey)
                && trim($serverKey) !== '',
            503,
            'Konfigurasi Midtrans belum lengkap.'
        );

        // Ambil reservasi milik wisatawan yang sedang login.
        $reservation = FacilityReservation::query()
            ->where('code', $reservationCode)
            ->where('user_id', $user->getKey())
            ->with('payment.attempts')
            ->firstOrFail();

        $payment = $reservation->payment;

        abort_unless(
            $payment
                && $payment->method === 'qris',
            409,
            'Reservasi belum memiliki pembayaran QRIS.'
        );

        // Ambil percobaan pembayaran terbaru.
        $attempt = $payment->attempts
            ->sortByDesc('id')
            ->first();

        abort_unless(
            $attempt !== null,
            409,
            'Transaksi QRIS belum dibuat.'
        );

        // Tidak perlu memeriksa ulang pembayaran
        // yang sudah tercatat lunas.
        if (
            $payment->status === 'paid'
            && $attempt->status === 'paid'
        ) {
            return $attempt;
        }

        abort_unless(
            filled($attempt->reference_id),
            409,
            'Referensi transaksi Midtrans belum tersedia.'
        );

        // Ambil status langsung dari Midtrans Sandbox.
        // Gunakan reference ID yang tersimpan,
        // bukan ID yang dikirim pengguna.
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
            'Status pembayaran gagal diperiksa ke Midtrans.'
        );

        $data = $response->json();

        abort_unless(
            is_array($data),
            502,
            'Respons Midtrans tidak valid.'
        );

        // Pastikan informasi penting tersedia.
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

        // Pastikan respons sesuai transaksi reservasi.
        abort_unless(
            $data['order_id'] === $attempt->reference_id
                && $data['payment_type'] === 'qris'
                && ($data['currency'] ?? 'IDR') === 'IDR',
            502,
            'Identitas transaksi Midtrans tidak sesuai.'
        );

        // Jika ID transaksi sudah tersimpan,
        // respons Midtrans harus memiliki ID yang sama.
        abort_if(
            $attempt->provider_payment_id !== null
                && $data['transaction_id']
                    !== $attempt->provider_payment_id,
            502,
            'ID transaksi Midtrans tidak cocok.'
        );

        // Batasi status yang dapat diproses.
        abort_unless(
            in_array(
                $data['transaction_status'],
                [
                    'pending',
                    'settlement',
                    'expire',
                    'deny',
                    'cancel',
                ],
                true
            ),
            502,
            'Status transaksi Midtrans tidak dikenal.'
        );

        // Respons diperoleh langsung dari Midtrans
        // melalui HTTPS dan autentikasi Server Key.
        //
        // Gunakan service webhook untuk memastikan:
        // - nominal pembayaran cocok
        // - transaksi berasal dari reservasi yang benar
        // - settlement memperbarui status menjadi paid
        // - notifikasi berulang tidak menggandakan proses
        $this->webhook->handle($data);

        return $attempt->fresh();
    }
}