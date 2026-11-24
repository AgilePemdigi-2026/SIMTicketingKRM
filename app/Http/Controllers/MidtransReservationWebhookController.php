<?php

namespace App\Http\Controllers;

use App\Services\FacilityReservationQrisWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class MidtransReservationWebhookController extends Controller
{
    public function handle(
        Request $request,
        FacilityReservationQrisWebhookService $service
    ): JsonResponse {

        // Pembayaran hanya boleh menggunakan Sandbox.
        if (config('midtrans.is_production') !== false) {
            return response()->json([
                'message' => 'Gateway tidak tersedia.',
            ], 503);
        }

        $serverKey = config('midtrans.server_key');

        if (!is_string($serverKey) || trim($serverKey) === '') {
            return response()->json([
                'message' => 'Konfigurasi Midtrans belum lengkap.',
            ], 503);
        }

        $payload = $request->json()->all();

        // Validasi field utama webhook.
        $requiredFields = [
            'order_id',
            'status_code',
            'gross_amount',
            'signature_key',
            'transaction_id',
            'transaction_status',
            'payment_type',
        ];

        foreach ($requiredFields as $field) {

            if (
                !isset($payload[$field])
                || !is_string($payload[$field])
                || trim($payload[$field]) === ''
            ) {
                return response()->json([
                    'message' => 'Payload webhook tidak valid.',
                ], 400);
            }
        }

        // Verifikasi signature Midtrans.
        // SHA512(order_id + status_code + gross_amount + key)
        $candidateKeys = array_values(
            array_filter(
                array_unique([
                    config('midtrans.signature_key'),
                    $serverKey,
                ]),
                fn ($key) =>
                    is_string($key) && trim($key) !== ''
            )
        );

        $signatureValid = false;

        foreach ($candidateKeys as $key) {

            $expectedSignature = hash(
                'sha512',
                $payload['order_id']
                    . $payload['status_code']
                    . $payload['gross_amount']
                    . $key
            );

            if (hash_equals(
                $expectedSignature,
                $payload['signature_key']
            )) {
                $signatureValid = true;
                break;
            }
        }

        if (!$signatureValid) {
            if (!$this->verifyWithMidtransStatusApi($payload, $serverKey)) {
                return response()->json([
                    'message' => 'Signature tidak valid.',
                ], 403);
            }
        }

        // Signature sudah diverifikasi.
        // Proses status menggunakan service reservasi,
        // bukan service pembayaran tiket.
        $service->handle($payload);

        return response()->json([
            'message' => 'Notifikasi reservasi berhasil diproses.',
        ]);
    }

    private function verifyWithMidtransStatusApi(array $payload, string $serverKey): bool
    {
        if (
            !str_starts_with($serverKey, 'SB-Mid-server-')
            && !str_starts_with($serverKey, 'Mid-server-')
        ) {
            return false;
        }

        try {
            $baseUrl = rtrim(
                (string) config('midtrans.sandbox_url', 'https://api.sandbox.midtrans.com'),
                '/'
            );

            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->timeout((int) config('midtrans.timeout', 15))
                ->get($baseUrl . '/v2/' . rawurlencode($payload['order_id']) . '/status');

            if (!$response->successful()) {
                return false;
            }

            $data = $response->json();
            if (!is_array($data)) {
                return false;
            }

            return ($data['order_id'] ?? null) === $payload['order_id']
                && ($data['transaction_id'] ?? null) === $payload['transaction_id']
                && ($data['transaction_status'] ?? null) === $payload['transaction_status']
                && (string) ($data['gross_amount'] ?? null) === (string) $payload['gross_amount']
                && (string) ($data['status_code'] ?? null) === (string) $payload['status_code'];
        } catch (Throwable) {
            return false;
        }
    }
}
