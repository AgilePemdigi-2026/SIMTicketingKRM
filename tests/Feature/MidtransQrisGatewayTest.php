<?php

namespace Tests\Feature;

use App\Services\MidtransQrisGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class MidtransQrisGatewayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Gunakan konfigurasi dummy, bukan Server Key asli.
        config()->set('midtrans.server_key', 'sandbox-test-key');
        config()->set('midtrans.is_production', false);
        config()->set(
            'midtrans.sandbox_url',
            'https://api.sandbox.midtrans.com'
        );
        config()->set('midtrans.timeout', 15);

        // Larang request HTTP sungguhan selama pengujian.
        Http::preventStrayRequests();
    }

    public function test_berhasil_membuat_qris_sandbox(): void
    {
        Http::fake([
            'api.sandbox.midtrans.com/v2/charge' =>
                Http::response([
                    'order_id' => 'TEST-ORDER-001',
                    'transaction_id' => 'midtrans-transaction-123',
                    'transaction_status' => 'pending',
                    'qr_string' => 'DUMMY-QR-STRING',
                    'actions' => [
                        [
                            'name' => 'generate-qr-code',
                            'method' => 'GET',
                            'url' => 'https://example.test/qr.png',
                        ],
                    ],
                ], 201),
        ]);

        $result = app(MidtransQrisGateway::class)
            ->createQris('TEST-ORDER-001', 50000);

        $this->assertSame(
            'midtrans-transaction-123',
            $result['transaction_id']
        );

        $this->assertSame('pending', $result['status']);

        $this->assertSame(
            'https://example.test/qr.png',
            $result['qr_image_url']
        );

        $this->assertSame(
            'DUMMY-QR-STRING',
            $result['qr_string']
        );

        Http::assertSent(function ($request) {
            return $request->url()
                    === 'https://api.sandbox.midtrans.com/v2/charge'
                && $request['payment_type'] === 'qris'
                && $request['transaction_details']['order_id']
                    === 'TEST-ORDER-001'
                && $request['transaction_details']['gross_amount']
                    === 50000
                && $request->hasHeader(
                    'Authorization',
                    'Basic '.base64_encode('sandbox-test-key:')
                );
        });

        Http::assertSentCount(1);
    }

    public function test_menolak_respons_error_midtrans(): void
    {
        Http::fake([
            'api.sandbox.midtrans.com/v2/charge' =>
                Http::response([
                    'status_message' => 'Unauthorized',
                ], 401),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTP 401');

        app(MidtransQrisGateway::class)
            ->createQris('TEST-ORDER-002', 50000);
    }

    public function test_menolak_respons_qris_tidak_valid(): void
    {
        Http::fake([
            'api.sandbox.midtrans.com/v2/charge' =>
                Http::response([
                    'order_id' => 'TEST-ORDER-003',
                    'transaction_status' => 'pending',
                    'transaction_id' => 'midtrans-transaction-456',
                    'actions' => [],
                ], 201),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Respons transaksi QRIS Midtrans tidak sesuai.'
        );

        app(MidtransQrisGateway::class)
            ->createQris('TEST-ORDER-003', 50000);
    }

    public function test_mencegah_penggunaan_production(): void
    {
        config()->set('midtrans.is_production', true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Integrasi ini hanya diizinkan pada Midtrans Sandbox.'
        );

        try {
            app(MidtransQrisGateway::class)
                ->createQris('TEST-ORDER-004', 50000);
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_mengirimkan_header_override_notification_pada_domain_publik_https(): void
    {
        config()->set('app.url', 'https://mangrotix.caiis.ai.id');

        Http::fake([
            'api.sandbox.midtrans.com/v2/charge' =>
                Http::response([
                    'order_id' => 'TEST-ORDER-OVERRIDE',
                    'transaction_id' => 'midtrans-transaction-999',
                    'transaction_status' => 'pending',
                    'actions' => [
                        [
                            'name' => 'generate-qr-code',
                            'method' => 'GET',
                            'url' => 'https://example.test/qr.png',
                        ],
                    ],
                ], 201),
        ]);

        app(MidtransQrisGateway::class)
            ->createQris('TEST-ORDER-OVERRIDE', 50000);

        Http::assertSent(function ($request) {
            return $request->hasHeader(
                'X-Override-Notification',
                'https://mangrotix.caiis.ai.id/webhooks/midtrans/ticket'
            );
        });
    }
}
