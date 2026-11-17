<?php

namespace Tests\Feature\Wisatawan;

use App\Models\FacilityReservation;
use Tests\Support\PostgresTicketTestCase;

class FacilityReservationControllerTest extends PostgresTicketTestCase
{
    public function test_wisatawan_can_create_a_reservation_and_receive_a_stable_invoice(): void
    {
        $user = $this->account();
        $payload = $this->reservationPayload(['jumlah_peserta' => 2]);
        $payload['total_amount'] = '1.00';
        $payload['administrative_status'] = 'approved';

        $response = $this->actingAs($user)
            ->postJson(route('wisatawan.reservasi.checkout'), $payload)
            ->assertCreated()
            ->assertJsonPath('reservation.facility', 'Gazebo Uji Coba')
            ->assertJsonPath('reservation.session', 'Pagi')
            ->assertJsonPath('reservation.participants', 2)
            ->assertJsonPath('reservation.unit_price', '50000.00')
            ->assertJsonPath('reservation.total_amount', '100000.00')
            ->assertJsonPath('reservation.administrative_status', 'pending');

        $code = $response->json('reservation.code');
        $invoiceNumber = $response->json('reservation.invoice_number');
        $reservation = FacilityReservation::where('invoice_number', $invoiceNumber)->firstOrFail();

        $this->assertSame($user->id, $reservation->user_id);
        $this->assertSame('100000.00', $reservation->total_amount);
        $this->assertDatabaseCount('facility_reservations', 1);

        $this->actingAs($user)
            ->getJson(route('wisatawan.reservasi.invoice', $code))
            ->assertOk()
            ->assertJsonPath('reservation.invoice_number', $invoiceNumber)
            ->assertJsonPath('reservation.total_amount', '100000.00');
    }

    public function test_availability_subtracts_pending_reservations_from_capacity(): void
    {
        $date = now('Asia/Jakarta')->addDay()->toDateString();

        $this->actingAs($this->account())
            ->postJson(route('wisatawan.reservasi.checkout'), $this->reservationPayload([
                'tanggal' => $date,
                'jumlah_peserta' => 3,
            ]))
            ->assertCreated();

        $this->actingAs($this->account())
            ->getJson(route('wisatawan.reservasi.availability', [
                'id_fasilitas' => 1,
                'id_sesi' => 1,
                'tanggal' => $date,
            ]))
            ->assertOk()
            ->assertJsonPath('capacity', 10)
            ->assertJsonPath('reserved', 3)
            ->assertJsonPath('available', 7)
            ->assertJsonPath('unit_price', 50000);
    }

    public function test_checkout_returns_409_without_writing_when_capacity_is_exceeded(): void
    {
        $date = now('Asia/Jakarta')->addDay()->toDateString();

        $this->actingAs($this->account())
            ->postJson(route('wisatawan.reservasi.checkout'), $this->reservationPayload([
                'tanggal' => $date,
                'jumlah_peserta' => 9,
            ]))
            ->assertCreated();

        $this->actingAs($this->account())
            ->postJson(route('wisatawan.reservasi.checkout'), $this->reservationPayload([
                'tanggal' => $date,
                'jumlah_peserta' => 2,
            ]))
            ->assertConflict()
            ->assertJsonPath('message', 'Kapasitas sesi untuk tanggal tersebut tidak mencukupi.');

        $this->assertDatabaseCount('facility_reservations', 1);
    }

    public function test_only_reservation_owner_can_view_invoice(): void
    {
        $owner = $this->account();
        $code = $this->actingAs($owner)
            ->postJson(route('wisatawan.reservasi.checkout'), $this->reservationPayload())
            ->assertCreated()
            ->json('reservation.code');

        $this->actingAs($this->account())
            ->getJson(route('wisatawan.reservasi.invoice', $code))
            ->assertNotFound();
    }

    public function test_guest_and_non_wisatawan_roles_cannot_create_reservations(): void
    {
        $this->postJson(route('wisatawan.reservasi.checkout'), $this->reservationPayload())
            ->assertUnauthorized();

        $this->actingAs($this->account('petugas'))
            ->postJson(route('wisatawan.reservasi.checkout'), $this->reservationPayload())
            ->assertForbidden();

        $this->assertDatabaseCount('facility_reservations', 0);
    }

    public function test_checkout_rejects_a_session_that_belongs_to_another_facility(): void
    {
        $this->actingAs($this->account())
            ->postJson(route('wisatawan.reservasi.checkout'), $this->reservationPayload([
                'id_sesi' => 2,
            ]))
            ->assertNotFound();

        $this->assertDatabaseCount('facility_reservations', 0);
    }

    private function reservationPayload(array $overrides = []): array
    {
        return array_replace([
            'id_fasilitas' => 1,
            'id_sesi' => 1,
            'tanggal' => now('Asia/Jakarta')->addDay()->toDateString(),
            'jumlah_peserta' => 1,
            'nama_pemesan' => 'Pemesan Tes',
            'nomor_identitas' => 'TES-123456',
            'nomor_telepon' => '081234567890',
            'email' => 'pemesan@example.test',
            'keperluan' => 'Kegiatan tes',
            'informasi_tambahan' => null,
        ], $overrides);
    }
}
