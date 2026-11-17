<?php

namespace Tests\Feature;

use App\Services\FacilityReservationService;
use App\Services\MidtransQrisGateway;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\Support\PostgresTicketTestCase;

class ReservationQrisEndpointTest extends PostgresTicketTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tidak menghubungi Midtrans sungguhan.
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
                'keperluan' => 'Pengujian endpoint QRIS',
                'informasi_tambahan' => null,
            ]);

        return [$user, $reservation];
    }

    private function mockGatewaySuccess(): void
    {
        $gateway = Mockery::mock(MidtransQrisGateway::class);

        $gateway->shouldReceive('createReservationQris')
            ->once()
            ->with(Mockery::type('string'), 100000)
            ->andReturn([
                'transaction_id' => 'MIDTRANS-ENDPOINT-001',
                'status' => 'pending',
                'qr_image_url' => 'https://example.test/qr.png',
                'qr_string' => 'DUMMY-QR',
                'expires_at' => now('Asia/Jakarta')
                    ->addMinutes(15)
                    ->toIso8601String(),
            ]);

        app()->instance(MidtransQrisGateway::class, $gateway);
    }

    public function test_pemilik_reservasi_dapat_membuat_qris(): void
    {
        [$user, $reservation] = $this->createReservation();

        $this->mockGatewaySuccess();

        $url = route(
            'facility-reservation.payment.qris.create',
            $reservation->code
        );

        $this->actingAs($user)
            ->postJson($url)
            ->assertOk()
            ->assertJsonPath(
                'reservation_code',
                $reservation->code
            )
            ->assertJsonPath(
                'payment.status',
                'pending'
            )
            ->assertJsonPath(
                'payment.amount',
                '100000.00'
            )
            ->assertJsonPath(
                'payment.qr_image_url',
                'https://example.test/qr.png'
            );

        // Permintaan kedua memakai QRIS yang sama.
        $this->actingAs($user)
            ->postJson($url)
            ->assertOk();

        $this->assertDatabaseCount(
            'facility_reservation_payments',
            1
        );

        $this->assertDatabaseCount(
            'facility_reservation_payment_attempts',
            1
        );
    }

    public function test_guest_dan_petugas_tidak_dapat_membuat_qris(): void
    {
        [$owner, $reservation] = $this->createReservation();

        $url = route(
            'facility-reservation.payment.qris.create',
            $reservation->code
        );

        // Tanpa login.
        $this->postJson($url)
            ->assertUnauthorized();

        // Login menggunakan role Petugas.
        $this->actingAs($this->account('petugas'))
            ->postJson($url)
            ->assertForbidden();

        $this->assertDatabaseCount(
            'facility_reservation_payment_attempts',
            0
        );
    }

    public function test_wisatawan_lain_tidak_dapat_membayar_reservasi(): void
    {
        [$owner, $reservation] = $this->createReservation();

        $otherUser = $this->account();

        $gateway = Mockery::mock(MidtransQrisGateway::class);

        $gateway->shouldNotReceive('createReservationQris');

        app()->instance(MidtransQrisGateway::class, $gateway);

        $this->actingAs($otherUser)
            ->postJson(
                route(
                    'facility-reservation.payment.qris.create',
                    $reservation->code
                )
            )
            ->assertNotFound();

        $this->assertDatabaseCount(
            'facility_reservation_payment_attempts',
            0
        );
    }

    public function test_halaman_pembayaran_menampilkan_data_reservasi_asli(): void
    {
        [$user, $reservation] = $this->createReservation();

        $this->actingAs($user)
            ->get(route(
                'facility-reservation.payment.show',
                ['code' => $reservation->code]
            ))
            ->assertOk()
            ->assertSee($reservation->invoice_number)
            ->assertSee($reservation->customer_name)
            ->assertSee('100.000');
    }

    public function test_halaman_pembayaran_hanya_bisa_dilihat_pemilik(): void
    {
        [$owner, $reservation] = $this->createReservation();

        $url = route(
            'facility-reservation.payment.show',
            ['code' => $reservation->code]
        );

        // Pengguna belum login.
        $this->getJson($url)
            ->assertUnauthorized();

        // Wisatawan lain.
        $this->actingAs($this->account())
            ->getJson($url)
            ->assertNotFound();
    }


public function test_invoice_final_hanya_tersedia_setelah_pembayaran_lunas(): void
{
    [$user, $reservation] = $this->createReservation();

    $url = route(
        'facility-reservation.payment.invoice',
        ['code' => $reservation->code]
    );

    // Belum membayar: invoice final ditolak.
    $this->actingAs($user)
        ->get($url)
        ->assertForbidden();

    // Simulasi pembayaran sudah terkonfirmasi.
    // Hanya mengubah data di database testing.
    $payment = app(
        \App\Services\FacilityReservationPaymentService::class
    )->prepare($user, $reservation->code);

    $payment->update([
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    // Setelah lunas, invoice dapat dibuka.
    $this->actingAs($user)
        ->get($url)
        ->assertOk()
        ->assertSee($reservation->invoice_number)
        ->assertSee($reservation->customer_name)
        ->assertSee('Lunas')
        ->assertSee('100.000');
    }

    public function test_invoice_final_tidak_bisa_dibuka_wisatawan_lain(): void
    {
        [$owner, $reservation] = $this->createReservation();

        $payment = app(
            \App\Services\FacilityReservationPaymentService::class
        )->prepare($owner, $reservation->code);

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $url = route(
            'facility-reservation.payment.invoice',
            ['code' => $reservation->code]
        );

        // Pengguna yang belum login ditolak.
        $this->getJson($url)
            ->assertUnauthorized();

        // Wisatawan lain bukan pemilik reservasi.
        $this->actingAs($this->account())
            ->getJson($url)
            ->assertNotFound();
    }

    public function test_riwayat_reservasi_hanya_menampilkan_milik_pengguna(): void
    {
        [$owner, $reservation] = $this->createReservation();

        $url = route('ticket-purchase.orders');

        // Pemilik dapat melihat reservasinya sendiri.
        $this->actingAs($owner)
            ->get($url)
            ->assertOk()
            ->assertSee($reservation->invoice_number)
            ->assertSee($reservation->facility_name);

        // Wisatawan lain tidak dapat melihat reservasi tersebut.
        $otherUser = $this->account();

        $this->actingAs($otherUser)
            ->get($url)
            ->assertOk()
            ->assertDontSee($reservation->invoice_number);
    }

    public function test_riwayat_reservasi_lunas_memiliki_link_invoice(): void
    {
        [$user, $reservation] = $this->createReservation();

        $payment = app(
            \App\Services\FacilityReservationPaymentService::class
        )->prepare($user, $reservation->code);

        // Simulasi pembayaran lunas di database testing.
        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $invoiceUrl = route(
            'facility-reservation.payment.invoice',
            ['code' => $reservation->code]
        );

        $this->actingAs($user)
            ->get(route('ticket-purchase.orders'))
            ->assertOk()
            ->assertSee($reservation->invoice_number)
            ->assertSee('Lunas')
            ->assertSee('Lihat Invoice')
            ->assertSee($invoiceUrl, false);
    }
}