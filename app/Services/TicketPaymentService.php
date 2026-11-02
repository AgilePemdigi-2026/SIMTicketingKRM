<?php

namespace App\Services;

use App\Events\TicketPaymentConfirmed;
use App\Models\TicketOrder;
use App\Models\TicketPayment;
use App\Models\User;
use App\Support\TicketPurchaseDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TicketPaymentService
{
    public function __construct(private TicketPurchaseDatabase $database) {}

    public function select(User $user, string $orderCode, string $method): TicketPayment
    {
        return $this->database->connection()->transaction(function () use ($user, $orderCode, $method) {
            abort_unless($user->role === 'wisatawan', 403);
            abort_unless(in_array($method, ['qris', 'cash'], true), 422);
            $order = TicketOrder::where('code', $orderCode)->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $payment = TicketPayment::where('ticket_order_id', $order->id)->first();
            if ($payment) {
                abort_unless($payment->method === $method, 409, 'Metode pembayaran sudah dikunci.');

                return $payment;
            }
            abort_unless($order->status === 'pending_payment', 409, 'Pesanan tidak dapat dibayar.');
            $payment = TicketPayment::create([
                'code' => (string) Str::uuid(), 'ticket_order_id' => $order->id,
                'method' => $method, 'amount' => $order->total, 'status' => 'pending',
            ]);
            $this->record($payment, $user, 'method_selected');

            return $payment;
        });
    }

    public function submit(User $user, string $orderCode, UploadedFile $proof): TicketPayment
    {
        $path = null;
        try {
            return $this->database->connection()->transaction(function () use ($user, $orderCode, $proof, &$path) {
                abort_unless($user->role === 'wisatawan', 403);
                $order = TicketOrder::where('code', $orderCode)->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
                $payment = TicketPayment::where('ticket_order_id', $order->id)->lockForUpdate()->firstOrFail();
                abort_unless($order->status === 'pending_payment' && $payment->method === 'qris', 409, 'Pesanan tidak menerima bukti QRIS.');
                $hash = hash_file('sha256', $proof->getRealPath());
                $latest = $payment->submissions()->latest('id')->first();
                if ($payment->status === 'under_review') {
                    abort_unless($latest?->file_hash === $hash, 409, 'Bukti sebelumnya sedang diperiksa.');

                    return $payment;
                }
                abort_unless(in_array($payment->status, ['pending', 'rejected'], true), 409, 'Pembayaran tidak menerima bukti baru.');
                $path = $proof->store('ticket-payment-proofs', 'local');
                if (! $path) {
                    throw new \RuntimeException('Bukti gagal disimpan. Silakan coba lagi.');
                }
                $payment->submissions()->create(['file_path' => $path, 'file_hash' => $hash]);
                $payment->update(['status' => 'under_review']);
                $this->record($payment, $user, 'proof_submitted');

                return $payment;
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }

    public function review(User $user, int $paymentId, array $data, bool $approve): TicketPayment
    {
        abort_unless($user->role === 'petugas', 403);
        $connection = $this->database->connection();
        try {
            return $connection->transaction(function () use ($user, $paymentId, $data, $approve) {
                $orderId = TicketPayment::findOrFail($paymentId)->ticket_order_id;
                $order = TicketOrder::whereKey($orderId)->lockForUpdate()->firstOrFail();
                $payment = TicketPayment::whereKey($paymentId)->lockForUpdate()->firstOrFail();
                $reference = strtoupper(trim($data['merchant_reference'] ?? ''));
                $submission = $payment->submissions()->latest('id')->first();
                if ($payment->method === 'qris') {
                    abort_unless($submission && (int) ($data['submission_id'] ?? 0) === $submission->id,
                        409, 'Bukti berubah. Muat ulang halaman sebelum memeriksa.');
                }
                if ($payment->status === 'paid') {
                    abort_unless($approve && ($payment->method === 'cash' || $payment->merchant_reference === $reference),
                        409, 'Pembayaran sudah dikonfirmasi dengan keputusan berbeda.');

                    return $payment;
                }
                if (! $approve && $payment->status === 'rejected') {
                    abort_unless($submission?->rejection_reason === trim($data['reason'] ?? ''), 409, 'Keputusan sebelumnya berbeda.');

                    return $payment;
                }
                abort_unless($order->status === 'pending_payment' && $order->total === $payment->amount,
                    409, 'Status atau nominal pesanan tidak sesuai.');
                if ($payment->method === 'qris') {
                    abort_unless($payment->status === 'under_review' && $submission->status === 'under_review',
                        409, 'Tidak ada bukti yang menunggu pemeriksaan.');
                } else {
                    abort_unless($approve && $payment->status === 'pending', 409, 'Cash hanya dapat dikonfirmasi setelah diterima.');
                }
                if (! $approve) {
                    $reason = trim($data['reason'] ?? '');
                    if ($reason === '') {
                        throw ValidationException::withMessages(['reason' => 'Alasan penolakan wajib diisi.']);
                    }
                    $submission->update(['status' => 'rejected', 'rejection_reason' => $reason,
                        'reviewed_by' => $user->id, 'reviewed_at' => now()]);
                    $payment->update(['status' => 'rejected']);
                    $this->record($payment, $user, 'proof_rejected', $reason);

                    return $payment;
                }
                if ($payment->method === 'qris') {
                    if ($reference === '') {
                        throw ValidationException::withMessages(['merchant_reference' => 'Referensi transaksi merchant wajib diisi.']);
                    }
                    abort_if(TicketPayment::where('merchant_reference', $reference)->where('id', '!=', $payment->id)->exists(),
                        409, 'Referensi transaksi merchant sudah digunakan.');
                    $submission->update(['status' => 'approved', 'reviewed_by' => $user->id, 'reviewed_at' => now()]);
                }
                $payment->update(['status' => 'paid', 'merchant_reference' => $payment->method === 'qris' ? $reference : null,
                    'confirmed_by' => $user->id, 'paid_at' => now()]);
                $order->update(['status' => 'paid']);
                $this->record($payment, $user, 'payment_confirmed');
                TicketPaymentConfirmed::dispatch($order->code, $payment->code, $payment->method,
                    $payment->amount, $payment->paid_at->toIso8601String());

                return $payment;
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23505') {
                abort(409, 'Referensi transaksi merchant sudah digunakan.');
            }
            throw $exception;
        }
    }

    private function record(TicketPayment $payment, User $user, string $action, ?string $reason = null): void
    {
        $payment->events()->create(['actor_id' => $user->id, 'action' => $action, 'reason' => $reason]);
    }
}
