<?php

namespace Tests\Feature;

use App\Models\FacilityReservationPaymentAttempt;
use App\Services\FacilityReservationPaymentService;
use App\Services\FacilityReservationQrisStatusSyncService;
use App\Services\FacilityReservationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\PostgresTicketTestCase;

class ReservationQrisStatusSyncTest extends PostgresTicketTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mencegah request HTTP sungguhan.
        Http::preventStrayRequests();

        config()->set('midtrans.is_production', false);
        config()->set(
            'midtrans.server_key',
            'midtrans-test-sync-key'
        );
    }

    private function createPendingPayment(): array
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
                'keperluan' => 'Pengujian Sinkronisasi QRIS',
                'informasi_tambahan' => null,
            ]);

        $payment = app(FacilityReservationPaymentService::class)
            ->prepare($user, $reservation->code);

        $attempt = FacilityReservationPaymentAttempt::create([
            'facility_reservation_payment_id' => $payment->id,
            'reference_id' => (string) Str::uuid(),
            'provider_payment_id' => 'MIDTRANS-SYNC-TEST-001',
            'currency' => 'IDR',
            'amount' => $payment->amount,
            'status' => 'pending',
            'qr_image_url' => 'https://example.test/qr.png',
            'expires_at' => now()->addMinutes(15),
        ]);

        return [$user, $reservation, $payment, $attempt];
    }

    private function fakeMidtrans(
        $payment,
        $attempt,
        string $status,
        array $changes = []
    ): void {

        $payload = [
            'order_id' => $attempt->reference_id,
            'transaction_id' => $attempt->provider_payment_id,
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

        $url = 'https://api.sandbox.midtrans.com/v2/'
            . rawurlencode($attempt->reference_id)
            . '/status';

        Http::fake([
            $url => Http::response($payload, 200),
        ]);
    }

    public function test_settlement_berhasil_disinkronkan(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->fakeMidtrans(
            $payment,
            $attempt,
            'settlement'
        );

        $result = app(
            FacilityReservationQrisStatusSyncService::class
        )->syncFor($user, $reservation->code);

        $this->assertSame('paid', $result->status);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);

        // Pembayaran lunas tidak otomatis
        // menyetujui reservasi secara administratif.
        $this->assertSame(
            'pending',
            $reservation->fresh()->administrative_status
        );

        Http::assertSentCount(1);
    }

    public function test_pending_tidak_dianggap_lunas(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->fakeMidtrans(
            $payment,
            $attempt,
            'pending'
        );

        $result = app(
            FacilityReservationQrisStatusSyncService::class
        )->syncFor($user, $reservation->code);

        $this->assertSame('pending', $result->status);
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
    }

    public function test_nominal_midtrans_salah_ditolak(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->fakeMidtrans(
            $payment,
            $attempt,
            'settlement',
            ['gross_amount' => '99999.00']
        );

        try {
            app(
                FacilityReservationQrisStatusSyncService::class
            )->syncFor($user, $reservation->code);

            $this->fail(
                'Nominal yang salah seharusnya ditolak.'
            );

        } catch (HttpException $exception) {
            $this->assertSame(
                422,
                $exception->getStatusCode()
            );
        }

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );

        $this->assertNull($payment->fresh()->paid_at);
    }

    public function test_id_midtrans_berbeda_ditolak(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->fakeMidtrans(
            $payment,
            $attempt,
            'settlement',
            ['transaction_id' => 'MIDTRANS-ID-BERBEDA']
        );

        try {
            app(
                FacilityReservationQrisStatusSyncService::class
            )->syncFor($user, $reservation->code);

            $this->fail(
                'ID Midtrans berbeda seharusnya ditolak.'
            );

        } catch (HttpException $exception) {
            $this->assertSame(
                502,
                $exception->getStatusCode()
            );
        }

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );
    }

    public function test_wisatawan_lain_tidak_bisa_sinkronisasi(): void
    {
        [$owner, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $otherUser = $this->account();

        try {
            app(
                FacilityReservationQrisStatusSyncService::class
            )->syncFor($otherUser, $reservation->code);

            $this->fail(
                'Reservasi milik pengguna lain harus ditolak.'
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $this->assertTrue(true);
        }

        // Tidak boleh ada request ke Midtrans.
        Http::assertNothingSent();

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );
    }

    public function test_endpoint_sinkronisasi_berhasil_memperbarui_pembayaran(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        // Simulasi Midtrans mengembalikan settlement.
        $this->fakeMidtrans(
            $payment,
            $attempt,
            'settlement'
        );

        $this->actingAs($user)
            ->postJson(
                route(
                    'facility-reservation.payment.qris.sync',
                    ['code' => $reservation->code]
                )
            )
            ->assertOk()
            ->assertJsonPath('payment.status', 'paid')
            ->assertJsonPath('payment.attempt_status', 'paid')
            ->assertJsonPath('payment.amount', '100000.00');

        $this->assertSame(
            'paid',
            $payment->fresh()->status
        );

        $this->assertNotNull(
            $payment->fresh()->paid_at
        );

        Http::assertSentCount(1);
    }

    public function test_endpoint_sinkronisasi_menolak_akses_tidak_sah(): void
    {
        [$owner, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $url = route(
            'facility-reservation.payment.qris.sync',
            ['code' => $reservation->code]
        );

        // Pengguna belum login.
        $this->postJson($url)
            ->assertUnauthorized();

        // Petugas tidak boleh menggunakan endpoint wisatawan.
        $this->actingAs($this->account('petugas'))
            ->postJson($url)
            ->assertForbidden();

        // Wisatawan lain bukan pemilik reservasi.
        $this->actingAs($this->account())
            ->postJson($url)
            ->assertNotFound();

        // Seluruh permintaan ditolak sebelum menghubungi Midtrans.
        Http::assertNothingSent();

        $this->assertSame(
            'pending',
            $payment->fresh()->status
        );
    }
}