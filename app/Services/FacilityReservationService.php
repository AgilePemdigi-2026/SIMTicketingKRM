<?php

namespace App\Services;

use App\Models\FacilityReservation;
use App\Models\User;
use App\Support\TicketPurchaseDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FacilityReservationService
{
    public function __construct(private TicketPurchaseDatabase $database) {}

    /** Create a reservation request with a server-calculated price snapshot. */
    public function create(User $user, array $data): FacilityReservation
    {
        abort_unless($user->role === 'wisatawan', 403);

        $connection = $this->database->connection();
        $reservation = $connection->transaction(function () use ($connection, $user, $data): FacilityReservation {
            $facility = $connection->table('fasilitas')
                ->where('id_fasilitas', $data['id_fasilitas'])
                ->lockForUpdate()
                ->first();
            abort_unless($facility && (int) $facility->status_fasilitas === 1, 404, 'Fasilitas tidak aktif.');

            $session = $connection->table('sesi_fasilitas')
                ->where('id_sesi', $data['id_sesi'])
                ->where('id_fasilitas', $facility->id_fasilitas)
                ->lockForUpdate()
                ->first();
            abort_unless($session && (int) $session->status_aktif === 1, 404, 'Sesi tidak aktif.');

            $visitDate = Carbon::createFromFormat('Y-m-d', $data['tanggal'])->startOfDay();
            abort_if($visitDate->lt(today()), 422, 'Tanggal kunjungan sudah lewat.');

            $participants = (int) $data['jumlah_peserta'];
            abort_if($participants > (int) $facility->kapasitas_fasilitas, 422, 'Jumlah peserta melebihi kapasitas fasilitas.');

            $unitPrice = $this->integerRupiah($session->harga_sesi);
            abort_if($unitPrice <= 0 || $participants > intdiv(PHP_INT_MAX, $unitPrice), 422, 'Nominal reservasi tidak valid.');
            $totalAmount = $unitPrice * $participants;

            $occupied = $connection->table('facility_reservations')
                ->where('id_sesi', $session->id_sesi)
                ->whereDate('visit_date', $visitDate->toDateString())
                ->whereNotIn('administrative_status', ['cancelled', 'rejected'])
                ->where(function ($query): void {
                    $query->whereIn('administrative_status', ['approved', 'confirmed'])
                        ->orWhere('capacity_hold_until', '>', now());
                })
                ->sum('participant_count');

            abort_if(
                $occupied + $participants > (int) $facility->kapasitas_fasilitas,
                409,
                'Kapasitas sesi untuk tanggal tersebut tidak mencukupi.'
            );

            $createdReservation = FacilityReservation::create([
                'code' => (string) Str::uuid(),
                'invoice_number' => 'RSV-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'user_id' => $user->getKey(),
                'id_fasilitas' => $facility->id_fasilitas,
                'id_sesi' => $session->id_sesi,
                'facility_name' => $facility->nama_fasilitas,
                'session_name' => $session->nama_sesi,
                'visit_date' => $visitDate->toDateString(),
                'start_time' => $session->jam_mulai,
                'end_time' => $session->jam_selesai,
                'duration_minutes' => (int) $session->durasi_menit,
                'participant_count' => $participants,
                'customer_name' => $data['nama_pemesan'],
                'identity_number' => $data['nomor_identitas'],
                'phone' => $data['nomor_telepon'],
                'email' => $data['email'],
                'purpose' => $data['keperluan'],
                'additional_information' => $data['informasi_tambahan'] ?? null,
                'unit_price' => sprintf('%d.00', $unitPrice),
                'total_amount' => sprintf('%d.00', $totalAmount),
                'administrative_status' => 'pending',
                'capacity_hold_until' => now()->addDay(),
            ]);

            app(AuditLogService::class)->record(
                actor: $user,
                module: 'Reservasi',
                action: 'reservation_created',
                description: 'Wisatawan berhasil membuat reservasi fasilitas.',
                subjectType: 'FacilityReservation',
                subjectId: $createdReservation->getKey(),
                newValues: [
                    'administrative_status' => $createdReservation->administrative_status,
                    'id_fasilitas' => $createdReservation->id_fasilitas,
                    'id_sesi' => $createdReservation->id_sesi,
                    'visit_date' => $createdReservation->visit_date->toDateString(),
                    'participant_count' => $createdReservation->participant_count,
                    'total_amount' => (string) $createdReservation->total_amount,
                ]
            );

            return $createdReservation;
        }, 3);

        return $reservation;
    }

    public function availability(int $facilityId, int $sessionId, string $date): array
    {
        $connection = $this->database->connection();
        $facility = $connection->table('fasilitas')
            ->where('id_fasilitas', $facilityId)
            ->where('status_fasilitas', 1)
            ->first();
        abort_unless($facility, 404);

        $session = $connection->table('sesi_fasilitas')
            ->where('id_sesi', $sessionId)
            ->where('id_fasilitas', $facilityId)
            ->where('status_aktif', 1)
            ->first();
        abort_unless($session, 404);

        $occupied = $connection->table('facility_reservations')
            ->where('id_sesi', $sessionId)
            ->whereDate('visit_date', $date)
            ->whereNotIn('administrative_status', ['cancelled', 'rejected'])
            ->where(function ($query): void {
                $query->whereIn('administrative_status', ['approved', 'confirmed'])
                    ->orWhere('capacity_hold_until', '>', now());
            })
            ->sum('participant_count');
        $capacity = (int) $facility->kapasitas_fasilitas;

        return [
            'facility_id' => (int) $facility->id_fasilitas,
            'session_id' => (int) $session->id_sesi,
            'date' => $date,
            'capacity' => $capacity,
            'reserved' => (int) $occupied,
            'available' => max(0, $capacity - (int) $occupied),
            'unit_price' => $this->integerRupiah($session->harga_sesi),
        ];
    }

    public function invoiceFor(User $user, string $code): FacilityReservation
    {
        abort_unless($user->role === 'wisatawan', 403);

        return FacilityReservation::query()
            ->where('code', $code)
            ->where('user_id', $user->getKey())
            ->firstOrFail();
    }

    private function integerRupiah(mixed $amount): int
    {
        $value = (string) $amount;
        if (! preg_match('/\A\d+(?:\.00)?\z/', $value)) {
            throw ValidationException::withMessages(['jumlah_peserta' => 'Harga sesi harus berupa rupiah bulat.']);
        }

        return (int) explode('.', $value, 2)[0];
    }
}
