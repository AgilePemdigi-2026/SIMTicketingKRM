<?php

namespace Tests\Feature;

use App\Contracts\TicketQrisGateway;
use App\Models\TicketOrder;
use App\Services\TicketPaymentService;
use App\Services\TicketPurchaseService;
use App\Services\TicketQrisPaymentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Http;
use Mockery;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\PostgresTicketTestCase;

class TicketQrisPaymentServiceTest extends PostgresTicketTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mencegah request HTTP sungguhan saat testing.
        Http::preventStrayRequests();
    }

    private function createOrderWithPayment(string $method): array
    {
        $user = $this->account();

        $order = app(TicketPurchaseService::class)
            ->create($user, $this->payload());

        $payment = app(TicketPaymentService::class)
            ->select($user, $order->code, $method);

        return [$user, $order, $payment];
    }

    public function test_qris_berhasil_dibuat_dan_disimpan(): void
    {
        [$user, $order, $payment] =
            $this->createOrderWithPayment('qris');

        $gateway = Mockery::mock(TicketQrisGateway::class);

        $gateway->shouldReceive('createQris')
            ->once()
            ->withArgs(function ($referenceId, $amount) use ($payment) {
                return is_string($referenceId)
                    && $amount === (int) $payment->amount;
            })
            ->andReturn([
                'transaction_id' => 'midtrans-test-123',
                'status' => 'pending',
                'qr_image_url' => 'https://example.test/qr.png',
                'qr_string' => 'DUMMY-QR',
                'expires_at' => '2030-01-20 14:45:00 +0700',
            ]);

        app()->instance(TicketQrisGateway::class, $gateway);

        $attempt = app(TicketQrisPaymentService::class)
            ->createFor($user, $order->code);

        $this->assertSame('pending', $attempt->status);

        $this->assertSame(
            'midtrans-test-123',
            $attempt->provider_payment_id
        );

        $this->assertSame(
            'https://example.test/qr.png',
            $attempt->qr_image_url
        );

        $this->assertNotNull($attempt->expires_at);

        $this->assertSame(
            '2030-01-20 14:45:00',
            $attempt->fresh()
                ->expires_at
                ->timezone('Asia/Jakarta')
                ->format('Y-m-d H:i:s')
        );

        $this->assertSame(
            $payment->id,
            $attempt->ticket_payment_id
        );

        $this->assertDatabaseCount(
            'ticket_payment_gateway_attempts',
            1
        );

        // QRIS dibuat tidak otomatis berarti sudah dibayar.
        $this->assertSame(
            'pending_payment',
            $order->fresh()->status
        );
    }

    public function test_qris_tidak_duplikat_saat_dicoba_ulang(): void
    {
        [$user, $order] =
            $this->createOrderWithPayment('qris');

        $gateway = Mockery::mock(TicketQrisGateway::class);

        // Gateway hanya boleh dipanggil sekali.
        $gateway->shouldReceive('createQris')
            ->once()
            ->andReturn([
                'transaction_id' => 'midtrans-test-456',
                'status' => 'pending',
                'qr_image_url' => 'https://example.test/qr2.png',
                'qr_string' => null,
            ]);

        app()->instance(TicketQrisGateway::class, $gateway);

        $service = app(TicketQrisPaymentService::class);

        $first = $service->createFor($user, $order->code);
        $second = $service->createFor($user, $order->code);

        $this->assertSame($first->id, $second->id);

        $this->assertDatabaseCount(
            'ticket_payment_gateway_attempts',
            1
        );
    }

    public function test_pesanan_pengguna_lain_tidak_bisa_dibayar(): void
    {
        [$user, $order] =
            $this->createOrderWithPayment('qris');

        $otherUser = $this->account();

        $this->expectException(ModelNotFoundException::class);

        app(TicketQrisPaymentService::class)
            ->createFor($otherUser, $order->code);
    }

    public function test_pembayaran_cash_tidak_bisa_membuat_qris(): void
    {
        [$user, $order] =
            $this->createOrderWithPayment('cash');

        try {
            app(TicketQrisPaymentService::class)
                ->createFor($user, $order->code);

            $this->fail('Pembayaran Cash tidak boleh membuat QRIS.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }

        $this->assertDatabaseCount(
            'ticket_payment_gateway_attempts',
            0
        );
    }

    public function test_kegagalan_gateway_tidak_membuat_duplikasi(): void
    {
        [$user, $order] =
            $this->createOrderWithPayment('qris');

        $gateway = Mockery::mock(TicketQrisGateway::class);

        $gateway->shouldReceive('createQris')
            ->once()
            ->andThrow(new RuntimeException('Gateway timeout'));

        app()->instance(TicketQrisGateway::class, $gateway);

        try {
            app(TicketQrisPaymentService::class)
                ->createFor($user, $order->code);

            $this->fail('Gateway seharusnya gagal.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Gateway timeout',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseHas(
            'ticket_payment_gateway_attempts',
            ['status' => 'initiation_unknown']
        );

        $this->assertDatabaseCount(
            'ticket_payment_gateway_attempts',
            1
        );

        $this->assertSame(
            'pending_payment',
            $order->fresh()->status
        );
    }
}
