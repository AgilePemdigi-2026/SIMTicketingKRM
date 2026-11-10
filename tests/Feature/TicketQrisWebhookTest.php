<?php

namespace Tests\Feature;

use App\Contracts\TicketQrisGateway;
use App\Models\TicketPaymentGatewayAttempt;
use App\Services\TicketPaymentService;
use App\Services\TicketPurchaseService;
use App\Services\TicketQrisPaymentService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mockery;
use Tests\Support\PostgresTicketTestCase;

class TicketQrisWebhookTest extends PostgresTicketTestCase
{
    private const SERVER_KEY = 'midtrans-test-key-only';

    protected function setUp(): void
    {
        parent::setUp();

        // Tidak boleh ada request HTTP sungguhan.
        Http::preventStrayRequests();

        // Credential khusus test, bukan Server Key asli.
        config()->set('midtrans.server_key', self::SERVER_KEY);
        config()->set('midtrans.is_production', false);
    }

    /**
     * Buat pesanan QRIS beserta attempt dari mock gateway.
     */
    private function createQrisOrder(): array
    {
        $user = $this->account();

        $order = app(TicketPurchaseService::class)
            ->create($user, $this->payload());

        $payment = app(TicketPaymentService::class)
            ->select($user, $order->code, 'qris');

        $gateway = Mockery::mock(TicketQrisGateway::class);

        $gateway->shouldReceive('createQris')
            ->once()
            ->andReturn([
                'transaction_id' => 'midtrans-webhook-test-001',
                'status' => 'pending',
                'qr_image_url' => 'https://example.test/qr.png',
                'qr_string' => 'DUMMY-QR',
                'expires_at' => '2030-01-20 14:45:00 +0700',
            ]);

        app()->instance(TicketQrisGateway::class, $gateway);

        $attempt = app(TicketQrisPaymentService::class)
            ->createFor($user, $order->code);

        return [$order, $payment, $attempt];
    }

    /**
     * Buat payload dengan signature sesuai rumus Midtrans.
     *
     * $changes diterapkan sebelum signature dihitung agar
     * kita bisa menguji payload bertanda tangan valid
     * tetapi memiliki nominal atau data yang salah.
     */
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
        // Tidak login: webhook harus bisa diakses Midtrans.
        return $this->postJson(
            route('ticket-payment.midtrans.webhook'),
            $payload
        );
    }

    public function test_signature_palsu_ditolak(): void
    {
        [$order, $payment, $attempt] =
            $this->createQrisOrder();

        $payload = $this->notification($payment, $attempt);

        $payload['signature_key'] = str_repeat('0', 128);

        $this->sendNotification($payload)
            ->assertStatus(403);

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );

        $this->assertSame(
            'pending_payment',
            $order->fresh()->status
        );
    }

    public function test_nominal_tidak_cocok_ditolak(): void
    {
        [$order, $payment, $attempt] =
            $this->createQrisOrder();

        // Signature valid, tetapi nominal sengaja salah.
        $wrongAmount = ((int) $payment->amount + 1000)
            . '.00';

        $payload = $this->notification(
            $payment,
            $attempt,
            'settlement',
            ['gross_amount' => $wrongAmount]
        );

        $this->sendNotification($payload)
            ->assertStatus(422);

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );

        $this->assertSame(
            'pending_payment',
            $order->fresh()->status
        );
    }

    public function test_qris_settlement_otomatis_lunas(): void
    {
        [$order, $payment, $attempt] =
            $this->createQrisOrder();

        $payload = $this->notification(
            $payment,
            $attempt
        );

        $this->sendNotification($payload)
            ->assertOk()
            ->assertJsonPath(
                'message',
                'Notifikasi berhasil diproses.'
            );

        $this->assertSame(
            'paid',
            $order->fresh()->status
        );

        $this->assertSame(
            'paid',
            $payment->fresh()->status
        );

        $this->assertSame(
            'paid',
            $attempt->fresh()->status
        );

        $this->assertSame(
            'midtrans-webhook-test-001',
            $payment->fresh()->merchant_reference
        );

        // Konfirmasi otomatis, bukan oleh Petugas.
        $this->assertNull(
            $payment->fresh()->confirmed_by
        );

        $this->assertNotNull(
            $payment->fresh()->paid_at
        );

        $this->assertNotNull(
            $attempt->fresh()->paid_at
        );
    }

    public function test_webhook_berulang_tidak_memproses_ulang(): void
    {
        [$order, $payment, $attempt] =
            $this->createQrisOrder();

        $payload = $this->notification(
            $payment,
            $attempt
        );

        // Notifikasi pertama.
        $this->sendNotification($payload)
            ->assertOk();

        $firstPaidAt = $payment->fresh()
            ->getRawOriginal('paid_at');

        // Notifikasi yang sama dikirim lagi.
        $this->sendNotification($payload)
            ->assertOk();

        $this->assertSame(
            'paid',
            $order->fresh()->status
        );

        $this->assertSame(
            'paid',
            $payment->fresh()->status
        );

        // Waktu pembayaran tidak berubah.
        $this->assertSame(
            $firstPaidAt,
            $payment->fresh()->getRawOriginal('paid_at')
        );

        // Tidak ada attempt tambahan.
        $this->assertDatabaseCount(
            'ticket_payment_gateway_attempts',
            1
        );
    }

    public function test_status_pending_tidak_membuat_lunas(): void
    {
        [$order, $payment, $attempt] =
            $this->createQrisOrder();

        $payload = $this->notification(
            $payment,
            $attempt,
            'pending'
        );

        $this->sendNotification($payload)
            ->assertOk();

        $this->assertSame(
            'pending',
            $attempt->fresh()->status
        );

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );

        $this->assertSame(
            'pending_payment',
            $order->fresh()->status
        );
    }

    public function test_id_transaksi_midtrans_berbeda_ditolak(): void
    {
        [$order, $payment, $attempt] =
            $this->createQrisOrder();

        $payload = $this->notification(
            $payment,
            $attempt,
            'settlement',
            ['transaction_id' => 'midtrans-id-berbeda']
        );

        $this->sendNotification($payload)
            ->assertStatus(409);

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );

        $this->assertSame(
            'pending_payment',
            $order->fresh()->status
        );
    }

    public function test_cash_tidak_bisa_dikonfirmasi_webhook_qris(): void
    {
        $user = $this->account();

        $order = app(TicketPurchaseService::class)
            ->create($user, $this->payload());

        $payment = app(TicketPaymentService::class)
            ->select($user, $order->code, 'cash');

        // Simulasi data attempt yang tidak semestinya
        // terhubung ke pembayaran Cash.
        // Webhook tetap wajib menolaknya.
        $attempt = TicketPaymentGatewayAttempt::create([
            'ticket_payment_id' => $payment->id,
            'reference_id' => (string) Str::uuid(),
            'provider_payment_id' => 'midtrans-cash-test',
            'amount' => $payment->amount,
            'currency' => 'IDR',
            'status' => 'pending',
        ]);

        $payload = $this->notification(
            $payment,
            $attempt
        );

        $this->sendNotification($payload)
            ->assertStatus(409);

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );

        $this->assertSame(
            'pending_payment',
            $order->fresh()->status
        );
    }

    public function test_signature_menggunakan_signature_key_khusus_diterima(): void
    {
        config()->set('midtrans.signature_key', 'custom-signature-secret-key');

        [$order, $payment, $attempt] = $this->createQrisOrder();

        $payload = [
            'order_id' => (string) $attempt->reference_id,
            'transaction_id' => (string) $attempt->provider_payment_id,
            'transaction_status' => 'settlement',
            'status_code' => '200',
            'gross_amount' => (string) $payment->amount,
            'payment_type' => 'qris',
            'currency' => 'IDR',
            'fraud_status' => 'accept',
        ];

        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id']
                . $payload['status_code']
                . $payload['gross_amount']
                . 'custom-signature-secret-key'
        );

        $this->sendNotification($payload)->assertOk();

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_fallback_status_api_memverifikasi_notifikasi_jika_signature_lokal_berbeda(): void
    {
        [$order, $payment, $attempt] = $this->createQrisOrder();

        $payload = [
            'order_id' => (string) $attempt->reference_id,
            'transaction_id' => (string) $attempt->provider_payment_id,
            'transaction_status' => 'settlement',
            'status_code' => '200',
            'gross_amount' => (string) $payment->amount,
            'payment_type' => 'qris',
            'currency' => 'IDR',
            'fraud_status' => 'accept',
            'signature_key' => 'signature-dari-map-yang-tidak-cocok-dengan-server-key',
        ];

        Http::fake([
            'api.sandbox.midtrans.com/v2/' . rawurlencode($payload['order_id']) . '/status' => Http::response([
                'order_id' => $payload['order_id'],
                'transaction_id' => $payload['transaction_id'],
                'transaction_status' => 'settlement',
                'status_code' => '200',
                'gross_amount' => $payload['gross_amount'],
                'payment_type' => 'qris',
                'currency' => 'IDR',
            ], 200),
        ]);

        $this->sendNotification($payload)->assertOk();

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $order->fresh()->status);
    }
}
