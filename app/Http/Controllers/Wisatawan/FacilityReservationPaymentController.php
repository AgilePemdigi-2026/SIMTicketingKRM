<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use App\Services\FacilityReservationQrisPaymentService;
use App\Services\FacilityReservationQrisStatusSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\FacilityReservation;
use App\Models\Fasilitas;

class FacilityReservationPaymentController extends Controller
{
    public function show(
        Request $request,
        string $code
    ): \Illuminate\View\View {

        // Hanya pemilik reservasi yang bisa melihat pembayaran.
        $reservasi = FacilityReservation::query()
            ->where('code', $code)
            ->where('user_id', $request->user()->getKey())
            ->with('payment.attempts')
            ->firstOrFail();

        // Ambil data fasilitas dan sesi untuk menjaga
        // kompatibilitas dengan tampilan Blade yang sudah ada.
        $fasilitas = Fasilitas::query()
            ->where('id_fasilitas', $reservasi->id_fasilitas)
            ->firstOrFail();

        $sesi = $fasilitas->sesiFasilitas()
            ->where('id_sesi', $reservasi->id_sesi)
            ->firstOrFail();

        return view('wisatawan.reservasi.pembayaran', [
            'reservasi' => $reservasi,
            'fasilitas' => $fasilitas,
            'sesi' => $sesi,
            'tanggal' => $reservasi->visit_date->format('Y-m-d'),
        ]);
    }

    /**
     * Membuat atau mengambil kembali QRIS reservasi.
     */
    public function createQris(
        Request $request,
        string $code,
        FacilityReservationQrisPaymentService $service
    ): JsonResponse {

        $attempt = $service->createFor(
            $request->user(),
            $code
        );

        return response()->json([
            'reservation_code' => $code,
            'payment' => [
                'status' => $attempt->status,
                'amount' => (string) $attempt->amount,
                'currency' => $attempt->currency,
                'qr_image_url' => $attempt->qr_image_url,
                'expires_at' =>
                    $attempt->expires_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Memeriksa status pembayaran reservasi
     * langsung ke Midtrans Sandbox.
     */
    public function syncQris(
        Request $request,
        string $code,
        FacilityReservationQrisStatusSyncService $service
    ): JsonResponse {

        $attempt = $service->syncFor(
            $request->user(),
            $code
        );

        // Ambil status pembayaran terbaru dari PostgreSQL.
        $payment = $attempt->payment()->firstOrFail();

        return response()->json([
            'reservation_code' => $code,
            'payment' => [
                'status' => $payment->status,
                'attempt_status' => $attempt->status,
                'amount' => (string) $payment->amount,
                'currency' => $attempt->currency,
                'paid_at' =>
                    $payment->paid_at?->toIso8601String(),
                'expires_at' =>
                    $attempt->expires_at?->toIso8601String(),
            ],
        ]);
    }


    public function showInvoice(
        Request $request,
        string $code
    ): \Illuminate\View\View {

        // Invoice hanya bisa dibuka oleh pemilik reservasi.
        $reservasi = FacilityReservation::query()
            ->where('code', $code)
            ->where('user_id', $request->user()->getKey())
            ->with('payment')
            ->firstOrFail();

        // Invoice final hanya tersedia setelah pembayaran lunas.
        abort_unless(
            $reservasi->payment?->status === 'paid',
            403,
            'Invoice final hanya tersedia setelah pembayaran lunas.'
        );

        // Sementara menyediakan variabel yang dipakai Blade lama.
        $fasilitas = Fasilitas::query()
            ->where('id_fasilitas', $reservasi->id_fasilitas)
            ->firstOrFail();

        $sesi = $fasilitas->sesiFasilitas()
            ->where('id_sesi', $reservasi->id_sesi)
            ->firstOrFail();

        return view('wisatawan.reservasi.invoice', [
            'reservasi' => $reservasi,
            'fasilitas' => $fasilitas,
            'sesi' => $sesi,
            'tanggal' => $reservasi->visit_date->format('Y-m-d'),
        ]);
    }

}