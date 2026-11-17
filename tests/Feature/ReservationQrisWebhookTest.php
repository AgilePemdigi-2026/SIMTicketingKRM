<?php

namespace Tests\Feature;

use App\Models\FacilityReservationPaymentAttempt;
use App\Services\FacilityReservationService;
use App\Services\FacilityReservationPaymentService;
use Illuminate\Support\Facades\Http;
use Tests\Support\PostgresTicketTestCase;

class ReservationQrisWebhookTest extends PostgresTicketTestCase
{
    private const SERVER_KEY = 'midtrans-test-reservasi';

    protected function setUp(): void
    {
        parent::setUp();

        // Tidak boleh menghubungi Midtrans sungguhan.
        Http::preventStrayRequests();

        config()->set('midtrans.is_production', false);
        config()->set('midtrans.server_key', self::SERVER_KEY);
        config()->set('midtrans.signature_key', null);
    }

    private function createPendingPayment(): array
    {
        $user = $this->account();

        // Membuat reservasi pada database testing.
        $reservation = app(FacilityReservationService::class)
            ->create($user, [
                'id_fasilitas' => 1,
                'id_sesi' => 1,
                'tanggal' => now('Asia/Jakarta')
                    ->addDay()
                    ->toDateString(),
                'jumlah_peserta' => 2,
                'nama_pemesan' => 'Pemesan Tes',
                'nomor_identitas' => 'TES123456',
                'nomor_telepon' => '081234567890',
                'email' => 'pemesan@example.test',
                'keperluan' => 'Pengujian QRIS Reservasi',
                'informasi_tambahan' => null,
            ]);

        // Menyiapkan catatan pembayaran.
        $payment = app(FacilityReservationPaymentService::class)
            ->prepare($user, $reservation->code);

        // Simulasi attempt yang sudah dibuat gateway.
        $attempt = FacilityReservationPaymentAttempt::create([
            'facility_reservation_payment_id' => $payment->id,
            'reference_id' => 'RSVPAY-TEST-001',
            'provider_payment_id' => 'MIDTRANS-RES-TEST-001',
            'currency' => 'IDR',
            'amount' => $payment->amount,
            'status' => 'pending',
            'qr_image_url' => 'https://example.test/qr.png',
            'expires_at' => now()->addMinutes(15),
        ]);

        return [$reservation, $payment, $attempt];
    }

    private function notification(
        $payment,
        $attempt,
        string $status = 'settlement',
        array $changes = []
    ): array {
        $payload = [
            'order_id' => (string) $attempt->reference_id,
            'transaction_id' => (string) $attempt->provider_payment_id,
            'transaction_status' => $status,
            'status_code' => $status === 'settlement'
                ? '200'
                : '201',
            'gross_amount' => (string) $payment->amount,
            'payment_type' => 'qris',
            'currency' => 'IDR',
            'fraud_status' => 'accept',
        ];

        $payload = array_replace($payload, $changes);

        // Signature dihitung setelah perubahan payload.
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id']
                . $payload['status_code']
                . $payload['gross_amount']
                . self::SERVER_KEY
        );

        return $payload;
    }

    private function sendNotification(array $payload)
    {
        return $this->postJson(
            route('facility-reservation.midtrans.webhook'),
            $payload
        );
    }

    public function test_settlement_membuat_pembayaran_lunas(): void
    {
        [$reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->sendNotification(
            $this->notification($payment, $attempt)
        )->assertOk();

        $this->assertSame(
            'paid',
            $payment->fresh()->status
        );

        $this->assertSame(
            'paid',
            $attempt->fresh()->status
        );

        $this->assertNotNull($payment->fresh()->paid_at);
        $this->assertNotNull($attempt->fresh()->paid_at);

        // Lunas bukan berarti disetujui Admin.
        $this->assertSame(
            'pending',
            $reservation->fresh()->administrative_status
        );
    }

    public function test_nominal_tidak_cocok_ditolak(): void
    {
        [$reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $payload = $this->notification(
            $payment,
            $attempt,
            'settlement',
            ['gross_amount' => '99999.00']
        );

        $this->sendNotification($payload)
            ->assertStatus(422);

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );

        $this->assertSame(
            'pending',
            $attempt->fresh()->status
        );
    }

    public function test_webhook_berulang_tidak_menggandakan_pembayaran(): void
    {
        [$reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $payload = $this->notification($payment, $attempt);

        $this->sendNotification($payload)
            ->assertOk();

        $firstPaidAt = $payment->fresh()
            ->getRawOriginal('paid_at');

        // Kirim notifikasi settlement kedua kalinya.
        $this->sendNotification($payload)
            ->assertOk();

        $this->assertSame(
            'paid',
            $payment->fresh()->status
        );

        $this->assertSame(
            $firstPaidAt,
            $payment->fresh()->getRawOriginal('paid_at')
        );

        $this->assertDatabaseCount(
            'facility_reservation_payment_attempts',
            1
        );
    }

    public function test_pending_tidak_membuat_pembayaran_lunas(): void
    {
        [$reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->sendNotification(
            $this->notification(
                $payment,
                $attempt,
                'pending'
            )
        )->assertOk();

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );

        $this->assertSame(
            'pending',
            $attempt->fresh()->status
        );

        $this->assertNull(
            $payment->fresh()->paid_at
        );
    }

    public function test_expire_tidak_membuat_pembayaran_lunas(): void
    {
        [$reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->sendNotification(
            $this->notification(
                $payment,
                $attempt,
                'expire'
            )
        )->assertOk();

        $this->assertSame(
            'expired',
            $attempt->fresh()->status
        );

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );
    }

    public function test_id_midtrans_berbeda_ditolak(): void
    {
        [$reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $payload = $this->notification(
            $payment,
            $attempt,
            'settlement',
            ['transaction_id' => 'MIDTRANS-ID-SALAH']
        );

        $this->sendNotification($payload)
            ->assertStatus(409);

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );
    }

    public function test_settlement_setelah_reservasi_dibatalkan_tidak_menyetujui_reservasi(): void
    {
        [$reservation, $payment, $attempt] =
            $this->createPendingPayment();

        // Simulasi Admin membatalkan reservasi
        // setelah transaksi QRIS dibuat.
        $reservation->update([
            'administrative_status' => 'cancelled',
        ]);

        // Midtrans mengonfirmasi pembayaran settlement.
        $this->sendNotification(
            $this->notification($payment, $attempt)
        )->assertOk();

        // Uang yang diterima tetap harus tercatat.
        $this->assertSame(
            'paid',
            $payment->fresh()->status
        );

        $this->assertSame(
            'paid',
            $attempt->fresh()->status
        );

        // Pembayaran tidak membatalkan keputusan Admin.
        $this->assertSame(
            'cancelled',
            $reservation->fresh()->administrative_status
        );
    }

}
