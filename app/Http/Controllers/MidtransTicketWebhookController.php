<?php

namespace App\Http\Controllers;

use App\Services\TicketQrisWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class MidtransTicketWebhookController extends Controller
{
    public function handle(
        Request $request,
        TicketQrisWebhookService $service
    ): JsonResponse {
        // Integrasi ini khusus Midtrans Sandbox.
        if (config('midtrans.is_production') !== false) {
            return response()->json([
                'message' => 'Gateway tidak tersedia.',
            ], 503);
        }

        $serverKey = config('midtrans.server_key');

        if (!is_string($serverKey) || $serverKey === '') {
            return response()->json([
                'message' => 'Konfigurasi gateway belum lengkap.',
            ], 503);
        }

        $payload = $request->json()->all();

        $requiredFields = [
            'order_id',
            'status_code',
            'gross_amount',
            'signature_key',
            'transaction_id',
            'transaction_status',
        ];

        foreach ($requiredFields as $field) {
            if (
                !isset($payload[$field]) ||
                !is_string($payload[$field]) ||
                trim($payload[$field]) === ''
            ) {
                return response()->json([
                    'message' => 'Payload tidak valid.',
                ], 400);
            }
        }

        // Signature resmi Midtrans:
        // SHA512(order_id + status_code + gross_amount + key)
        // Kunci dapat berupa Server Key atau Signature Secret Key dari MAP.
        $candidateKeys = array_values(array_filter(array_unique([
            config('midtrans.signature_key'),
            $serverKey,
        ]), fn ($k) => is_string($k) && trim($k) !== ''));

        $signatureValid = false;
        foreach ($candidateKeys as $key) {
            $expectedSignature = hash(
                'sha512',
                $payload['order_id']
                    . $payload['status_code']
                    . $payload['gross_amount']
                    . $key
            );

            if (hash_equals($expectedSignature, $payload['signature_key'])) {
                $signatureValid = true;
                break;
            }
        }

        // Fallback resmi: jika signature lokal tidak cocok (misal MAP menggunakan Signature Secret Key baru),
        // verifikasi integritas transaksi langsung ke Midtrans Get Status API via HTTPS Basic Auth.
        if (! $signatureValid) {
            if (! $this->verifyWithMidtransStatusApi($payload, $serverKey)) {
                return response()->json([
                    'message' => 'Signature tidak valid.',
                ], 403);
            }
        }

        // Service akan mencocokkan ID transaksi,
        // nominal, dan status pembayaran di database.
        // Response sukses hanya dikirim setelah service selesai.
        $service->handle($payload);

        return response()->json([
            'message' => 'Notifikasi berhasil diproses.',
        ]);
    }

    private function verifyWithMidtransStatusApi(array $payload, string $serverKey): bool
    {
        try {
            $baseUrl = rtrim(
                (string) (config('midtrans.is_production')
                    ? config('midtrans.production_url', 'https://api.midtrans.com')
                    : config('midtrans.sandbox_url', 'https://api.sandbox.midtrans.com')),
                '/'
            );

            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->timeout((int) config('midtrans.timeout', 15))
                ->get($baseUrl . '/v2/' . rawurlencode($payload['order_id']) . '/status');

            if (! $response->successful()) {
                return false;
            }

            $data = $response->json();
            if (! is_array($data)) {
                return false;
            }

            return ($data['order_id'] ?? null) === $payload['order_id']
                && ($data['transaction_id'] ?? null) === $payload['transaction_id']
                && ($data['transaction_status'] ?? null) === $payload['transaction_status']
                && (string) ($data['status_code'] ?? null) === (string) $payload['status_code'];
        } catch (Throwable) {
            return false;
        }
    }
}
