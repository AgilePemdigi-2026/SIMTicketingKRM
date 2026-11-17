<?php

namespace Tests\Feature;

use App\Models\FacilityReservationPaymentAttempt;
use App\Services\FacilityReservationQrisPaymentService;
use App\Services\FacilityReservationService;
use App\Services\MidtransQrisGateway;
use Illuminate\Support\Facades\Http;
use Mockery;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\PostgresTicketTestCase;

class ReservationQrisPaymentServiceTest extends PostgresTicketTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tidak boleh menghubungi Midtrans sungguhan.
        Http::preventStrayRequests();

        config()->set('midtrans.is_production', false);
        config()->set('midtrans.server_key', 'test-only-key');
    }

    private function createReservation(): array
    {
        $user = $this->account();

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

        return [$user, $reservation];
    }

    public function test_qris_berhasil_dibuat_dan_tidak_digandakan(): void
    {
        [$user, $reservation] = $this->createReservation();

        $gateway = Mockery::mock(MidtransQrisGateway::class);

        $gateway->shouldReceive('createReservationQris')
            ->once()
            ->with(Mockery::type('string'), 100000)
            ->andReturn([
                'transaction_id' => 'MIDTRANS-RES-001',
                'status' => 'pending',
                'qr_image_url' => 'https://example.test/qr.png',
                'qr_string' => 'QR-TEST-RESERVASI',
                'expires_at' => now('Asia/Jakarta')
                    ->addMinutes(15)
                    ->toIso8601String(),
            ]);

        app()->instance(MidtransQrisGateway::class, $gateway);

        $service = app(FacilityReservationQrisPaymentService::class);

        // Pembuatan QRIS pertama.
        $first = $service->createFor(
            $user,
            $reservation->code
        );

        $this->assertSame('pending', $first->status);
        $this->assertSame('100000.00', $first->amount);
        $this->assertSame(
            'MIDTRANS-RES-001',
            $first->provider_payment_id
        );
        $this->assertNotNull($first->qr_image_url);
        $this->assertNotNull($first->expires_at);

        // Permintaan kedua harus menggunakan QRIS lama.
        $second = $service->createFor(
            $user,
            $reservation->code
        );

        $this->assertSame($first->id, $second->id);

        $this->assertDatabaseCount(
            'facility_reservation_payments',
            1
        );

        $this->assertDatabaseCount(
            'facility_reservation_payment_attempts',
            1
        );
    }

    public function test_reservasi_dengan_hold_kedaluwarsa_ditolak(): void
    {
        [$user, $reservation] = $this->createReservation();

        $reservation->update([
            'capacity_hold_until' => now()->subMinute(),
        ]);

        $gateway = Mockery::mock(MidtransQrisGateway::class);

        $gateway->shouldNotReceive('createReservationQris');

        app()->instance(MidtransQrisGateway::class, $gateway);

        try {
            app(FacilityReservationQrisPaymentService::class)
                ->createFor($user, $reservation->code);

            $this->fail(
                'Reservasi kedaluwarsa seharusnya ditolak.'
            );
        } catch (HttpException $exception) {
            $this->assertSame(
                409,
                $exception->getStatusCode()
            );
        }

        $this->assertDatabaseCount(
            'facility_reservation_payment_attempts',
            0
        );
    }

    public function test_kegagalan_gateway_tidak_langsung_membuat_qris_baru(): void
    {
        [$user, $reservation] = $this->createReservation();

        $gateway = Mockery::mock(MidtransQrisGateway::class);

        $gateway->shouldReceive('createReservationQris')
            ->once()
            ->andThrow(
                new RuntimeException('Simulasi timeout gateway')
            );

        app()->instance(MidtransQrisGateway::class, $gateway);

        $service = app(FacilityReservationQrisPaymentService::class);

        try {
            $service->createFor($user, $reservation->code);

            $this->fail(
                'Simulasi timeout seharusnya menghasilkan error.'
            );
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Simulasi timeout gateway',
                $exception->getMessage()
            );
        }

        $attempt = FacilityReservationPaymentAttempt::firstOrFail();

        $this->assertSame(
            'initiation_unknown',
            $attempt->status
        );

        // Tidak boleh langsung membuat transaksi kedua.
        try {
            $service->createFor($user, $reservation->code);

            $this->fail(
                'Percobaan ulang seharusnya ditolak.'
            );
        } catch (HttpException $exception) {
            $this->assertSame(
                409,
                $exception->getStatusCode()
            );
        }

        $this->assertDatabaseCount(
            'facility_reservation_payment_attempts',
            1
        );
    }
}
