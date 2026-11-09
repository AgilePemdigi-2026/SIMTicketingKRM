<?php

namespace App\Http\Controllers;

use App\Services\TicketQrisWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        // SHA512(order_id + status_code + gross_amount + server_key)
        $expectedSignature = hash(
            'sha512',
            $payload['order_id']
                . $payload['status_code']
                . $payload['gross_amount']
                . $serverKey
        );

        if (!hash_equals(
            $expectedSignature,
            $payload['signature_key']
        )) {
            return response()->json([
                'message' => 'Signature tidak valid.',
            ], 403);
        }

        // Service akan mencocokkan ID transaksi,
        // nominal, dan status pembayaran di database.
        // Response sukses hanya dikirim setelah service selesai.
        $service->handle($payload);

        return response()->json([
            'message' => 'Notifikasi berhasil diproses.',
        ]);
    }
}
