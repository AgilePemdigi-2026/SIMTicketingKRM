<?php

namespace Tests\Feature;

use App\Services\MidtransQrisGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReservationQrisGatewayTest extends TestCase
{
    public function test_qris_reservasi_memakai_webhook_reservasi(): void
    {
        config()->set('midtrans.is_production', false);
        config()->set('midtrans.server_key', 'sandbox-test-key');
        config()->set(
            'midtrans.sandbox_url',
            'https://api.sandbox.midtrans.com'
        );
        config()->set(
            'app.url',
            'https://simticketing-unitcheck.org'
        );

        Http::preventStrayRequests();

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge'
                => Http::response([
                    'order_id' => 'RSVPAY-TEST-001',
                    'transaction_id' => 'MIDTRANS-TEST-001',
                    'transaction_status' => 'pending',
                    'qr_string' => 'QRIS-TEST',
                    'actions' => [
                        [
                            'name' => 'generate-qr-code',
                            'url' => 'https://example.org/qr-test.png',
                        ],
                    ],
                ], 200),
        ]);

        $result = app(MidtransQrisGateway::class)
            ->createReservationQris('RSVPAY-TEST-001', 30000);

        $this->assertSame('pending', $result['status']);
        $this->assertSame(
            'MIDTRANS-TEST-001',
            $result['transaction_id']
        );

        Http::assertSent(function ($request) {
            return $request->url()
                    === 'https://api.sandbox.midtrans.com/v2/charge'
                && $request['transaction_details']['order_id']
                    === 'RSVPAY-TEST-001'
                && $request['transaction_details']['gross_amount']
                    === 30000
                && $request->hasHeader(
                    'X-Override-Notification',
                    'https://simticketing-unitcheck.org/webhooks/midtrans/reservation'
                );
        });
    }
}
