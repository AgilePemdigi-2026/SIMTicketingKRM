<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\TicketPayment;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\TicketPaymentService;
use App\Services\TicketPurchaseService;
use App\Services\TicketQrisWebhookService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\PostgresTicketTestCase;

class TicketPaymentAuditLogTest extends PostgresTicketTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    private function createPayment(
        string $method = 'cash',
        ?User $staff = null
    ): TicketPayment {
        $purchase = app(TicketPurchaseService::class);
        $service = app(TicketPaymentService::class);

        if ($staff !== null) {
            $order = $purchase->createForCounter(
                $staff,
                $this->payload()
            );

            return $service->selectForCounter(
                $staff,
                $order->code,
                $method
            );
        }

        $user = $this->account('wisatawan');

        $order = $purchase->create(
            $user,
            $this->payload()
        );

        return $service->select(
            $user,
            $order->code,
            $method
        );
    }

    private function confirmCash(
        TicketPayment $payment,
        User $staff,
        array $data = []
    ): TicketPayment {
        return app(TicketPaymentService::class)->review(
            $staff,
            $payment->id,
            $data,
            true
        );
    }

    private function qrisPayload(
        TicketPayment $payment,
        string $status = 'settlement'
    ): array {
        $attempt = $payment->gatewayAttempts()->create([
            'reference_id' => (string) Str::uuid(),
            'provider_payment_id' => (string) Str::uuid(),
            'amount' => $payment->amount,
            'currency' => 'IDR',
            'status' => 'pending',
            'qr_string' => 'TEST-PRIVATE-QR',
            'qr_image_url' => 'https://example.test/qr.png',
        ]);

        return [
            'order_id' => $attempt->reference_id,
            'transaction_id' => $attempt->provider_payment_id,
            'transaction_status' => $status,
            'status_code' => $status === 'settlement'
                ? '200'
                : '201',
            'gross_amount' => (string) $payment->amount,
            'payment_type' => 'qris',
            'currency' => 'IDR',
            'fraud_status' => 'accept',
            'signature_key' => 'TEST-PRIVATE-SIGNATURE',
        ];
    }

    private function paymentLogs(
        TicketPayment $payment
    ) {
        return AuditLog::query()
            ->where('module', 'Pembayaran')
            ->where('subject_type', 'TicketPayment')
            ->where('subject_id', (string) $payment->id);
    }

    // PAY-AL-01: Cash Booking menghasilkan audit log.
    public function test_cash_booking_confirmation_creates_audit_log(): void
    {
        $payment = $this->createPayment();
        $staff = $this->account('petugas');

        $this->confirmCash($payment, $staff);

        $this->assertSame(
            'paid',
            $payment->fresh()->status
        );

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $staff->id,
            'actor_name' => $staff->name,
            'actor_role' => 'petugas',
            'module' => 'Pembayaran',
            'action' => 'payment_confirmed',
            'subject_type' => 'TicketPayment',
            'subject_id' => (string) $payment->id,
        ]);

        $this->assertSame(
            1,
            $this->paymentLogs($payment)->count()
        );
    }

    // PAY-AL-02: Cash Loket mencatat Petugas dan kanal.
    public function test_counter_cash_confirmation_records_staff_and_channel(): void
    {
        $staff = $this->account('petugas');
        $payment = $this->createPayment('cash', $staff);

        $this->confirmCash($payment, $staff, [
            'cash_received_amount' => (int) $payment->amount,
        ]);

        $log = $this->paymentLogs($payment)->firstOrFail();

        $this->assertSame($staff->id, $log->user_id);
        $this->assertSame('petugas', $log->actor_role);
        $this->assertSame('counter', $log->new_values['sale_channel']);
        $this->assertSame('cash', $log->new_values['method']);
        $this->assertSame('paid', $payment->fresh()->status);
    }

    // PAY-AL-03: Histori status dan nominal Cash.
    public function test_cash_log_stores_status_and_amount(): void
    {
        $payment = $this->createPayment();
        $staff = $this->account('petugas');

        $this->confirmCash($payment, $staff);

        $log = $this->paymentLogs($payment)->firstOrFail();

        $this->assertSame('pending', $log->old_values['status']);
        $this->assertSame('paid', $log->new_values['status']);
        $this->assertSame('cash', $log->new_values['method']);
        $this->assertSame(
            (string) $payment->amount,
            $log->new_values['amount']
        );

        $this->assertNotEmpty($log->new_values['paid_at']);
    }

    // PAY-AL-04: Konfirmasi ulang Cash tidak menduplikasi log.
    public function test_repeated_cash_confirmation_does_not_duplicate_log(): void
    {
        $payment = $this->createPayment();
        $staff = $this->account('petugas');

        $this->confirmCash($payment, $staff);
        $this->confirmCash($payment, $staff);

        $this->assertSame(
            1,
            $this->paymentLogs($payment)->count()
        );

        $this->assertSame(
            1,
            $payment->events()
                ->where('action', 'payment_confirmed')
                ->count()
        );
    }

    // PAY-AL-05: Cash dengan uang kurang tidak menghasilkan log.
    public function test_insufficient_cash_does_not_create_success_log(): void
    {
        $staff = $this->account('petugas');
        $payment = $this->createPayment('cash', $staff);

        try {
            $this->confirmCash($payment, $staff, [
                'cash_received_amount' => 0,
            ]);

            $this->fail('Insufficient cash should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'cash_received_amount',
                $exception->errors()
            );
        }

        $this->assertSame('pending', $payment->fresh()->status);

        $this->assertSame(
            0,
            $this->paymentLogs($payment)->count()
        );
    }

    // PAY-AL-06: QRIS settlement mencatat actor Sistem.
    public function test_qris_settlement_creates_system_audit_log(): void
    {
        $payment = $this->createPayment('qris');
        $payload = $this->qrisPayload($payment);

        app(TicketQrisWebhookService::class)->handle($payload);

        $log = $this->paymentLogs($payment)->firstOrFail();

        $this->assertNull($log->user_id);
        $this->assertSame('Sistem', $log->actor_name);
        $this->assertSame('system', $log->actor_role);
        $this->assertSame('qris_payment_confirmed', $log->action);
        $this->assertSame('paid', $payment->fresh()->status);

        $this->assertSame(
            1,
            $this->paymentLogs($payment)->count()
        );
    }

    // PAY-AL-07: QRIS Loket mencatat kanal counter.
    public function test_counter_qris_records_counter_channel(): void
    {
        $staff = $this->account('petugas');
        $payment = $this->createPayment('qris', $staff);

        app(TicketQrisWebhookService::class)->handle(
            $this->qrisPayload($payment)
        );

        $log = $this->paymentLogs($payment)->firstOrFail();

        $this->assertSame('counter', $log->new_values['sale_channel']);
        $this->assertSame('qris', $log->new_values['method']);
        $this->assertSame('system', $log->actor_role);
        $this->assertNull($log->user_id);
    }

    // PAY-AL-08: Histori dan nominal QRIS disimpan.
    public function test_qris_log_stores_status_and_amount(): void
    {
        $payment = $this->createPayment('qris');

        app(TicketQrisWebhookService::class)->handle(
            $this->qrisPayload($payment)
        );

        $log = $this->paymentLogs($payment)->firstOrFail();

        $this->assertSame('pending', $log->old_values['status']);
        $this->assertSame('paid', $log->new_values['status']);

        $this->assertSame(
            (string) $payment->amount,
            $log->new_values['amount']
        );

        $this->assertSame('qris', $log->new_values['method']);
        $this->assertNotEmpty($log->new_values['paid_at']);
    }

    // PAY-AL-09: Webhook settlement berulang tidak menduplikasi log.
    public function test_repeated_qris_notification_does_not_duplicate_log(): void
    {
        $payment = $this->createPayment('qris');
        $payload = $this->qrisPayload($payment);
        $service = app(TicketQrisWebhookService::class);

        $service->handle($payload);
        $service->handle($payload);

        $this->assertSame(
            1,
            $this->paymentLogs($payment)->count()
        );
    }

    // PAY-AL-10: QRIS pending tidak mencatat pembayaran sukses.
    public function test_pending_qris_does_not_create_success_log(): void
    {
        $payment = $this->createPayment('qris');

        app(TicketQrisWebhookService::class)->handle(
            $this->qrisPayload($payment, 'pending')
        );

        $this->assertSame('pending', $payment->fresh()->status);

        $this->assertSame(
            0,
            $this->paymentLogs($payment)->count()
        );
    }

    // PAY-AL-11: QRIS expired tidak mencatat pembayaran sukses.
    public function test_expired_qris_does_not_create_success_log(): void
    {
        $payment = $this->createPayment('qris');

        app(TicketQrisWebhookService::class)->handle(
            $this->qrisPayload($payment, 'expire')
        );

        $this->assertSame('pending', $payment->fresh()->status);

        $this->assertSame(
            'expired',
            $payment->gatewayAttempts()->firstOrFail()->status
        );

        $this->assertSame(
            0,
            $this->paymentLogs($payment)->count()
        );
    }

    // PAY-AL-12: Nominal QRIS salah tidak menghasilkan log.
    public function test_invalid_qris_amount_does_not_create_log(): void
    {
        $payment = $this->createPayment('qris');
        $payload = $this->qrisPayload($payment);

        $payload['gross_amount'] = '1.00';

        try {
            app(TicketQrisWebhookService::class)->handle($payload);

            $this->fail('Invalid QRIS amount should be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }

        $this->assertSame('pending', $payment->fresh()->status);

        $this->assertSame(
            0,
            $this->paymentLogs($payment)->count()
        );
    }

    // PAY-AL-13: Data sensitif QRIS tidak disimpan.
    public function test_qris_log_does_not_store_gateway_secrets(): void
    {
        $payment = $this->createPayment('qris');
        $payload = $this->qrisPayload($payment);

        app(TicketQrisWebhookService::class)->handle($payload);

        $log = $this->paymentLogs($payment)->firstOrFail();

        $this->assertStringNotContainsString(
            'TEST-PRIVATE-SIGNATURE',
            $log->toJson()
        );

        $this->assertStringNotContainsString(
            'TEST-PRIVATE-QR',
            $log->toJson()
        );

        $this->assertStringNotContainsString(
            $payload['transaction_id'],
            $log->toJson()
        );

        $this->assertArrayNotHasKey(
            'merchant_reference',
            $log->new_values
        );
    }

    // PAY-AL-14: Rollback Cash menghapus log dalam transaksi.
    public function test_cash_transaction_rollback_removes_audit_log(): void
    {
        $payment = $this->createPayment();
        $staff = $this->account('petugas');

        $this->testConnection->beginTransaction();

        try {
            $this->confirmCash($payment, $staff);

            $this->assertSame(
                1,
                $this->paymentLogs($payment)->count()
            );
        } finally {
            $this->testConnection->rollBack();
        }

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending_payment', $payment->order->fresh()->status);

        $this->assertSame(
            0,
            $this->paymentLogs($payment)->count()
        );
    }

    // PAY-AL-15: Rollback QRIS menghapus log dalam transaksi.
    public function test_qris_transaction_rollback_removes_audit_log(): void
    {
        $payment = $this->createPayment('qris');
        $payload = $this->qrisPayload($payment);

        $this->testConnection->beginTransaction();

        try {
            app(TicketQrisWebhookService::class)->handle($payload);

            $this->assertSame(
                1,
                $this->paymentLogs($payment)->count()
            );
        } finally {
            $this->testConnection->rollBack();
        }

        $this->assertSame('pending', $payment->fresh()->status);

        $this->assertSame(
            0,
            $this->paymentLogs($payment)->count()
        );
    }

    // PAY-AL-16: Cash dibatalkan jika audit log gagal disimpan.
    public function test_cash_confirmation_rolls_back_when_audit_logging_fails(): void
    {
        $payment = $this->createPayment();
        $staff = $this->account('petugas');

        $this->mock(
            AuditLogService::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('record')
                    ->once()
                    ->andThrow(
                        new RuntimeException('Audit storage failed.')
                    );
            }
        );

        try {
            $this->confirmCash($payment, $staff);

            $this->fail('Payment should be rolled back.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Audit storage failed.',
                $exception->getMessage()
            );
        }

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending_payment', $payment->order->fresh()->status);

        $this->assertSame(
            0,
            $payment->events()
                ->where('action', 'payment_confirmed')
                ->count()
        );

        $this->assertSame(
            0,
            $this->paymentLogs($payment)->count()
        );
    }

    // PAY-AL-17: QRIS dibatalkan jika audit log gagal disimpan.
    public function test_qris_confirmation_rolls_back_when_audit_logging_fails(): void
    {
        $payment = $this->createPayment('qris');
        $payload = $this->qrisPayload($payment);

        $this->mock(
            AuditLogService::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('record')
                    ->once()
                    ->andThrow(
                        new RuntimeException('Audit storage failed.')
                    );
            }
        );

        try {
            app(TicketQrisWebhookService::class)->handle($payload);

            $this->fail('QRIS payment should be rolled back.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Audit storage failed.',
                $exception->getMessage()
            );
        }

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending_payment', $payment->order->fresh()->status);

        $this->assertSame(
            'pending',
            $payment->gatewayAttempts()->firstOrFail()->status
        );

        $this->assertSame(
            0,
            $this->paymentLogs($payment)->count()
        );
    }
}
