<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureMasterTicketDatabase;
use App\Services\FacilityReservationQrisWebhookService;
use Mockery\MockInterface;
use Tests\TestCase;

class ReservationWebhookSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('midtrans.is_production', false);
        config()->set(
            'midtrans.server_key',
            'sandbox-test-server-key'
        );
        config()->set('midtrans.signature_key', null);

        // Isolasi pengujian controller dari pemeriksaan
        // koneksi PostgreSQL. Tidak menguji middleware DB.
        $this->withoutMiddleware(
            EnsureMasterTicketDatabase::class
        );
    }

    private function payload(): array
    {
        $data = [
            'order_id' => 'RSVPAY-TEST-001',
            'status_code' => '200',
            'gross_amount' => '30000.00',
            'transaction_id' => 'MIDTRANS-TEST-001',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'currency' => 'IDR',
        ];

        $data['signature_key'] = hash(
            'sha512',
            $data['order_id']
                . $data['status_code']
                . $data['gross_amount']
                . config('midtrans.server_key')
        );

        return $data;
    }

    public function test_signature_valid_diterima(): void
    {
        $this->mock(
            FacilityReservationQrisWebhookService::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('handle')
                    ->once();
            }
        );

        $this->postJson(
            '/webhooks/midtrans/reservation',
            $this->payload()
        )->assertOk();
    }

    public function test_signature_salah_ditolak(): void
    {
        $this->mock(
            FacilityReservationQrisWebhookService::class,
            function (MockInterface $mock) {
                $mock->shouldNotReceive('handle');
            }
        );

        $payload = $this->payload();

        // Ubah nominal setelah signature dibuat.
        $payload['gross_amount'] = '99999.00';

        $this->postJson(
            '/webhooks/midtrans/reservation',
            $payload
        )->assertForbidden();
    }

    public function test_payload_tidak_lengkap_ditolak(): void
    {
        $this->mock(
            FacilityReservationQrisWebhookService::class,
            function (MockInterface $mock) {
                $mock->shouldNotReceive('handle');
            }
        );

        $payload = $this->payload();

        unset($payload['transaction_id']);

        $this->postJson(
            '/webhooks/midtrans/reservation',
            $payload
        )->assertStatus(400);
    }
}
