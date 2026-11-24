<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use App\Models\FacilityReservation;
use App\Models\FacilityReservationRescheduleRequest;
use App\Models\SesiFasilitas;
use App\Services\FacilityReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FacilityReservationRescheduleController extends Controller
{
    public function create(Request $request, string $code): View
    {
        $reservation = FacilityReservation::query()
            ->where('code', $code)
            ->where('user_id', $request->user()->getKey())
            ->with('payment')
            ->firstOrFail();

        $this->ensureEligible($reservation);

        $sessions = SesiFasilitas::query()
            ->where('id_fasilitas', $reservation->id_fasilitas)
            ->where('status_aktif', 1)
            ->orderBy('jam_mulai')
            ->get();

        $pendingRequest = FacilityReservationRescheduleRequest::query()
            ->where('facility_reservation_id', $reservation->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('wisatawan.reservasi.reschedule-request', [
            'reservation' => $reservation,
            'sessions' => $sessions,
            'pendingRequest' => $pendingRequest,
        ]);
    }

    public function store(
        Request $request,
        string $code,
        FacilityReservationService $service
    ): RedirectResponse {
        $data = $request->validate([
            'requested_visit_date' => [
                'required',
                'date_format:Y-m-d',
                'after:' . now('Asia/Jakarta')->toDateString(),
            ],
            'requested_id_sesi' => [
                'required',
                'integer',
            ],
            'reason' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ],
        ]);

        $reservation = FacilityReservation::query()
            ->where('code', $code)
            ->where('user_id', $request->user()->getKey())
            ->with('payment')
            ->firstOrFail();

        $this->ensureEligible($reservation);

        $session = SesiFasilitas::query()
            ->where('id_sesi', $data['requested_id_sesi'])
            ->where('id_fasilitas', $reservation->id_fasilitas)
            ->where('status_aktif', 1)
            ->first();

        if (! $session) {
            throw ValidationException::withMessages([
                'requested_id_sesi' => 'Sesi tidak valid untuk fasilitas ini.',
            ]);
        }

        if (
            $reservation->visit_date->toDateString()
                === $data['requested_visit_date']
            && (int) $reservation->id_sesi
                === (int) $data['requested_id_sesi']
        ) {
            throw ValidationException::withMessages([
                'requested_visit_date' =>
                    'Pilih tanggal atau sesi yang berbeda dari jadwal awal.',
            ]);
        }

        $availability = $service->availability(
            (int) $reservation->id_fasilitas,
            (int) $session->id_sesi,
            $data['requested_visit_date']
        );

        if (
            (int) $availability['available']
                < (int) $reservation->participant_count
        ) {
            throw ValidationException::withMessages([
                'requested_id_sesi' =>
                    'Kapasitas pada jadwal baru tidak mencukupi.',
            ]);
        }

        DB::connection('pgsql')->transaction(function () use (
            $request,
            $code,
            $data
        ): void {
            $reservation = FacilityReservation::query()
                ->where('code', $code)
                ->where('user_id', $request->user()->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $reservation->load('payment');
            $this->ensureEligible($reservation);

            $alreadyPending = FacilityReservationRescheduleRequest::query()
                ->where('facility_reservation_id', $reservation->id)
                ->where('status', 'pending')
                ->exists();

            if ($alreadyPending) {
                throw ValidationException::withMessages([
                    'requested_visit_date' =>
                        'Masih ada pengajuan Reschedule yang menunggu persetujuan.',
                ]);
            }

            FacilityReservationRescheduleRequest::create([
                'facility_reservation_id' => $reservation->id,
                'user_id' => $request->user()->getKey(),
                'original_visit_date' => $reservation->visit_date,
                'original_id_sesi' => $reservation->id_sesi,
                'requested_visit_date' => $data['requested_visit_date'],
                'requested_id_sesi' => $data['requested_id_sesi'],
                'reason' => $data['reason'],
                'status' => 'pending',
            ]);
        }, 3);

        return redirect()
            ->route('wisatawan.reservasi.reschedule.create', [
                'code' => $code,
            ])
            ->with(
                'success',
                'Pengajuan Reschedule berhasil dikirim dan menunggu persetujuan Admin.'
            );
    }

    private function ensureEligible(
        FacilityReservation $reservation
    ): void {
        if (
            $reservation->visit_date->toDateString()
                <= now('Asia/Jakarta')->toDateString()
        ) {
            abort(403, 'Tanggal reservasi sudah tidak memenuhi syarat Reschedule.');
        }

        if (
            in_array(
                $reservation->administrative_status,
                ['cancelled', 'rejected'],
                true
            )
        ) {
            abort(403, 'Reservasi ini tidak dapat dijadwalkan ulang.');
        }

        if ($reservation->payment?->status !== 'paid') {
            abort(403, 'Reschedule hanya tersedia untuk reservasi yang sudah Lunas.');
        }
    }
}
