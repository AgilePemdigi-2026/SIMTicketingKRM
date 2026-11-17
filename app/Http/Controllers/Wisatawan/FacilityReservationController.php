<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use App\Models\FacilityReservation;
use App\Models\User;
use App\Services\FacilityReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacilityReservationController extends Controller
{
    public function availability(Request $request, FacilityReservationService $service): JsonResponse
    {
        $data = $request->validate([
            'id_fasilitas' => ['required', 'integer', 'min:1'],
            'id_sesi' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        return response()->json($service->availability(
            (int) $data['id_fasilitas'],
            (int) $data['id_sesi'],
            $data['tanggal'],
        ));
    }

    public function store(Request $request, FacilityReservationService $service): JsonResponse
    {
        $data = $request->validate([
            'id_fasilitas' => ['required', 'integer', 'min:1'],
            'id_sesi' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'jumlah_peserta' => ['required', 'integer', 'min:1'],
            'nama_pemesan' => ['required', 'string', 'max:100'],
            'nomor_identitas' => ['required', 'string', 'max:32'],
            'nomor_telepon' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:150'],
            'keperluan' => ['required', 'string', 'max:200'],
            'informasi_tambahan' => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $reservation = $service->create($user, $data);

        return response()->json(['reservation' => $this->payload($reservation)], 201);
    }

    public function invoice(Request $request, string $code, FacilityReservationService $service): JsonResponse
    {
        return response()->json([
            'reservation' => $this->payload($service->invoiceFor($request->user(), $code)),
        ]);
    }

    private function payload(FacilityReservation $reservation): array
    {
        return [
            'code' => $reservation->code,
            'invoice_number' => $reservation->invoice_number,
            'facility' => $reservation->facility_name,
            'session' => $reservation->session_name,
            'date' => $reservation->visit_date->toDateString(),
            'start_time' => substr((string) $reservation->start_time, 0, 5),
            'end_time' => substr((string) $reservation->end_time, 0, 5),
            'participants' => (int) $reservation->participant_count,
            'customer' => [
                'name' => $reservation->customer_name,
                'phone' => $reservation->phone,
                'email' => $reservation->email,
            ],
            'purpose' => $reservation->purpose,
            'additional_information' => $reservation->additional_information,
            'unit_price' => (string) $reservation->unit_price,
            'total_amount' => (string) $reservation->total_amount,
            'administrative_status' => $reservation->administrative_status,
            'capacity_hold_until' => $reservation->capacity_hold_until?->toIso8601String(),
        ];
    }
}
