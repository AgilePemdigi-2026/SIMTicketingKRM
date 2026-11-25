<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\FacilityReservation;
use App\Models\FacilityReservationRescheduleRequest;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\FacilityReservationService;
use App\Services\FacilityReservationPaymentService;
use App\Services\FacilityReservationQrisWebhookService;
use App\Services\FacilityReservationQrisStatusSyncService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use RuntimeException;
use Tests\Support\PostgresTicketTestCase;

class FacilityReservationAuditLogTest extends PostgresTicketTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        config()->set('midtrans.is_production', false);
        config()->set('midtrans.server_key', 'test-only-key');
        config()->set('midtrans.signature_key', null);
    }

    private function reservationPayload(array $overrides = []): array
    {
        return array_replace([
            'id_fasilitas' => 1,
            'id_sesi' => 1,
            'tanggal' => now('Asia/Jakarta')->addDay()->toDateString(),
            'jumlah_peserta' => 2,
            'nama_pemesan' => 'Pemesan Tes',
            'nomor_identitas' => 'PRIVATE-IDENTITY-TEST',
            'nomor_telepon' => '081234567890',
            'email' => 'private-reservation@example.test',
            'keperluan' => 'Pengujian Audit Log',
            'informasi_tambahan' => 'PRIVATE-NOTE-TEST',
        ], $overrides);
    }

    private function createReservation(
        ?User $user = null,
        array $overrides = []
    ): FacilityReservation {
        $user ??= $this->account('wisatawan');

        return app(FacilityReservationService::class)->create(
            $user,
            $this->reservationPayload($overrides)
        );
    }

    private function logs(
        string $action,
        string $subjectType,
        int|string $subjectId
    ) {
        return AuditLog::query()
            ->where('module', 'Reservasi')
            ->where('action', $action)
            ->where('subject_type', $subjectType)
            ->where('subject_id', (string) $subjectId);
    }

    private function reservationLogs(FacilityReservation $reservation)
    {
        return $this->logs(
            'reservation_created',
            'FacilityReservation',
            $reservation->id
        );
    }

    private function paymentLogs($payment)
    {
        return $this->logs(
            'reservation_payment_confirmed',
            'FacilityReservationPayment',
            $payment->id
        );
    }

    private function createPendingPayment(): array
    {
        $user = $this->account('wisatawan');
        $reservation = $this->createReservation($user);

        $payment = app(FacilityReservationPaymentService::class)
            ->prepare($user, $reservation->code);

        $attempt = $payment->attempts()->create([
            'reference_id' => (string) Str::uuid(),
            'provider_payment_id' => (string) Str::uuid(),
            'currency' => 'IDR',
            'amount' => $payment->amount,
            'status' => 'pending',
            'qr_image_url' => 'https://example.test/qr.png',
            'qr_string' => 'PRIVATE-QR-TEST',
            'expires_at' => now()->addMinutes(15),
        ]);

        return [$user, $reservation, $payment, $attempt];
    }

    private function notification(
        $payment,
        $attempt,
        string $status = 'settlement',
        array $overrides = []
    ): array {
        return array_replace([
            'order_id' => $attempt->reference_id,
            'transaction_id' => $attempt->provider_payment_id,
            'transaction_status' => $status,
            'status_code' => $status === 'settlement' ? '200' : '201',
            'gross_amount' => (string) $payment->amount,
            'payment_type' => 'qris',
            'currency' => 'IDR',
            'fraud_status' => 'accept',
            'signature_key' => 'PRIVATE-SIGNATURE-TEST',
        ], $overrides);
    }

    private function settle($payment, $attempt): void
    {
        app(FacilityReservationQrisWebhookService::class)
            ->handle($this->notification($payment, $attempt));
    }

    private function reschedulePayload(): array
    {
        return [
            'requested_visit_date' => now('Asia/Jakarta')
                ->addDays(3)
                ->toDateString(),
            'requested_id_sesi' => 1,
            'reason' => 'PRIVATE-RESCHEDULE-REASON-TEST',
        ];
    }

    private function submitReschedule(
        User $user,
        FacilityReservation $reservation
    ) {
        return $this->actingAs($user)->post(
            route('wisatawan.reservasi.reschedule.store', [
                'code' => $reservation->code,
            ]),
            $this->reschedulePayload()
        );
    }

    private function rescheduleCount(): int
    {
        return AuditLog::query()
            ->where('module', 'Reservasi')
            ->where('action', 'reschedule_requested')
            ->count();
    }

    // RSV-AL-01: Reservation creation creates one audit log.
    public function test_reservation_creation_records_actor(): void
    {
        $user = $this->account('wisatawan');

        $reservation = $this->createReservation($user);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'actor_name' => $user->name,
            'actor_role' => 'wisatawan',
            'module' => 'Reservasi',
            'action' => 'reservation_created',
            'subject_type' => 'FacilityReservation',
            'subject_id' => (string) $reservation->id,
        ]);

        $this->assertSame(
            1,
            $this->reservationLogs($reservation)->count()
        );
    }

    // RSV-AL-02: Reservation data uses backend values.
    public function test_reservation_log_records_operational_values(): void
    {
        $reservation = $this->createReservation(null, [
            'total_amount' => '1.00',
            'administrative_status' => 'approved',
        ]);

        $log = $this->reservationLogs($reservation)->firstOrFail();

        $this->assertNull($log->old_values);
        $this->assertSame('pending', $log->new_values['administrative_status']);
        $this->assertSame('100000.00', $log->new_values['total_amount']);
        $this->assertSame(2, (int) $log->new_values['participant_count']);
        $this->assertSame(1, (int) $log->new_values['id_fasilitas']);
        $this->assertSame(1, (int) $log->new_values['id_sesi']);

        $this->assertSame(
            $reservation->visit_date->toDateString(),
            $log->new_values['visit_date']
        );
    }

    // RSV-AL-03: Customer private fields are excluded.
    public function test_reservation_log_excludes_private_customer_data(): void
    {
        $reservation = $this->createReservation();

        $log = $this->reservationLogs($reservation)->firstOrFail();

        foreach ([
            'PRIVATE-IDENTITY-TEST',
            '081234567890',
            'private-reservation@example.test',
            'PRIVATE-NOTE-TEST',
        ] as $value) {
            $this->assertStringNotContainsString($value, $log->toJson());
        }
    }

    // RSV-AL-04: Failed reservation does not create audit log.
    public function test_capacity_failure_does_not_create_log(): void
    {
        $before = AuditLog::count();

        $this->actingAs($this->account('wisatawan'))
            ->postJson(
                route('wisatawan.reservasi.checkout'),
                $this->reservationPayload(['jumlah_peserta' => 11])
            )
            ->assertStatus(422);

        $this->assertSame($before, AuditLog::count());
    }

    // RSV-AL-05: Failed audit storage rolls back reservation.
    public function test_reservation_rolls_back_when_logging_fails(): void
    {
        $user = $this->account('wisatawan');

        $before = FacilityReservation::count();

        $this->mock(AuditLogService::class, function (MockInterface $mock) {
            $mock->shouldReceive('record')
                ->once()
                ->andThrow(new RuntimeException('Audit storage failed.'));
        });

        try {
            $this->createReservation($user);

            $this->fail('Reservation should be rolled back.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Audit storage failed.',
                $exception->getMessage()
            );
        }

        $this->assertSame($before, FacilityReservation::count());
    }

    // RSV-AL-06: QRIS settlement records system actor.
    public function test_reservation_settlement_records_system_actor(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->settle($payment, $attempt);

        $log = $this->paymentLogs($payment)->firstOrFail();

        $this->assertNull($log->user_id);
        $this->assertSame('Sistem', $log->actor_name);
        $this->assertSame('system', $log->actor_role);
        $this->assertSame('pending', $log->old_values['status']);
        $this->assertSame('paid', $log->new_values['status']);
        $this->assertSame('qris', $log->new_values['method']);
        $this->assertSame('100000.00', $log->new_values['amount']);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $attempt->fresh()->status);
        $this->assertNotEmpty($log->new_values['paid_at']);
    }

    // RSV-AL-07: Settlement does not approve reservation.
    public function test_payment_does_not_change_administrative_status(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->settle($payment, $attempt);

        $log = $this->paymentLogs($payment)->firstOrFail();

        $this->assertSame(
            'pending',
            $reservation->fresh()->administrative_status
        );

        $this->assertSame(
            'pending',
            $log->old_values['administrative_status']
        );

        $this->assertSame(
            'pending',
            $log->new_values['administrative_status']
        );
    }

    // RSV-AL-08: Repeated settlement does not duplicate log.
    public function test_repeated_settlement_does_not_duplicate_log(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->settle($payment, $attempt);
        $this->settle($payment, $attempt);

        $this->assertSame(
            1,
            $this->paymentLogs($payment)->count()
        );
    }

    // RSV-AL-09: Pending and expired payments are not success logs.
    public function test_pending_and_expired_payment_do_not_create_log(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $service = app(FacilityReservationQrisWebhookService::class);

        $service->handle(
            $this->notification($payment, $attempt, 'pending')
        );

        $this->assertSame(0, $this->paymentLogs($payment)->count());

        $service->handle(
            $this->notification($payment, $attempt, 'expire')
        );

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('expired', $attempt->fresh()->status);
        $this->assertSame(0, $this->paymentLogs($payment)->count());
    }

    // RSV-AL-10: Invalid payment amount is rejected.
    public function test_invalid_payment_amount_does_not_create_log(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        try {
            app(FacilityReservationQrisWebhookService::class)->handle(
                $this->notification($payment, $attempt, 'settlement', [
                    'gross_amount' => '1.00',
                ])
            );

            $this->fail('Incorrect amount should be rejected.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame(0, $this->paymentLogs($payment)->count());
    }

    // RSV-AL-11: Payment log excludes gateway secrets.
    public function test_payment_log_excludes_gateway_secrets(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->settle($payment, $attempt);

        $log = $this->paymentLogs($payment)->firstOrFail();

        foreach ([
            'PRIVATE-SIGNATURE-TEST',
            'PRIVATE-QR-TEST',
            $attempt->provider_payment_id,
            $attempt->reference_id,
        ] as $value) {
            $this->assertStringNotContainsString($value, $log->toJson());
        }
    }

    // RSV-AL-12: Failed audit storage rolls back payment.
    public function test_payment_rolls_back_when_logging_fails(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->mock(AuditLogService::class, function (MockInterface $mock) {
            $mock->shouldReceive('record')
                ->once()
                ->andThrow(new RuntimeException('Audit storage failed.'));
        });

        try {
            $this->settle($payment, $attempt);

            $this->fail('Payment should be rolled back.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Audit storage failed.',
                $exception->getMessage()
            );
        }

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending', $attempt->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
        $this->assertSame(0, $this->paymentLogs($payment)->count());
    }

    // RSV-AL-13: Status sync creates only one success log.
    public function test_status_sync_records_payment_once(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $url = 'https://api.sandbox.midtrans.com/v2/'
            . rawurlencode($attempt->reference_id)
            . '/status';

        Http::fake([
            $url => Http::response(
                $this->notification($payment, $attempt),
                200
            ),
        ]);

        $service = app(FacilityReservationQrisStatusSyncService::class);

        $service->syncFor($user, $reservation->code);
        $service->syncFor($user, $reservation->code);

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame(1, $this->paymentLogs($payment)->count());

        Http::assertSentCount(1);
    }

    // RSV-AL-14: Reschedule submission records owner.
    public function test_reschedule_submission_creates_audit_log(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->settle($payment, $attempt);

        $this->submitReschedule($user, $reservation)
            ->assertRedirect(route('wisatawan.reservasi.reschedule.create', [
                'code' => $reservation->code,
            ]))
            ->assertSessionHas('success');

        $reschedule = FacilityReservationRescheduleRequest::query()
            ->where('facility_reservation_id', $reservation->id)
            ->firstOrFail();

        $log = $this->logs(
            'reschedule_requested',
            'FacilityReservationRescheduleRequest',
            $reschedule->id
        )->firstOrFail();

        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('wisatawan', $log->actor_role);
        $this->assertSame('pending', $log->new_values['status']);
        $this->assertSame('pending', $reschedule->status);
    }

    // RSV-AL-15: Reschedule preserves original and requested dates.
    public function test_reschedule_log_records_schedule_history(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->settle($payment, $attempt);

        $originalDate = $reservation->visit_date->toDateString();

        $this->submitReschedule($user, $reservation)
            ->assertSessionHas('success');

        $reschedule = FacilityReservationRescheduleRequest::query()
            ->where('facility_reservation_id', $reservation->id)
            ->firstOrFail();

        $log = $this->logs(
            'reschedule_requested',
            'FacilityReservationRescheduleRequest',
            $reschedule->id
        )->firstOrFail();

        $this->assertSame($originalDate, $log->old_values['visit_date']);

        $this->assertSame(
            $this->reschedulePayload()['requested_visit_date'],
            $log->new_values['requested_visit_date']
        );

        $this->assertSame(
            $originalDate,
            $reservation->fresh()->visit_date->toDateString()
        );

        $this->assertStringNotContainsString(
            'PRIVATE-RESCHEDULE-REASON-TEST',
            $log->toJson()
        );
    }

    // RSV-AL-16: Duplicate pending reschedule is rejected.
    public function test_duplicate_reschedule_does_not_duplicate_log(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->settle($payment, $attempt);

        $before = $this->rescheduleCount();

        $this->submitReschedule($user, $reservation)
            ->assertSessionHas('success');

        $this->submitReschedule($user, $reservation)
            ->assertSessionHasErrors('requested_visit_date');

        $this->assertSame(
            $before + 1,
            $this->rescheduleCount()
        );
    }

    // RSV-AL-17: Unpaid reservation cannot be rescheduled.
    public function test_unpaid_reservation_cannot_create_reschedule_log(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $before = $this->rescheduleCount();

        $this->submitReschedule($user, $reservation)
            ->assertForbidden();

        $this->assertSame($before, $this->rescheduleCount());
    }

    // RSV-AL-18: Other users cannot submit reschedule.
    public function test_other_user_cannot_create_reschedule_log(): void
    {
        [$owner, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->settle($payment, $attempt);

        $before = $this->rescheduleCount();

        $this->submitReschedule(
            $this->account('wisatawan'),
            $reservation
        )->assertNotFound();

        $this->assertSame($before, $this->rescheduleCount());
    }

    // RSV-AL-19: Failed audit storage rolls back reschedule.
    public function test_reschedule_rolls_back_when_logging_fails(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->settle($payment, $attempt);

        $before = $this->rescheduleCount();

        $this->mock(AuditLogService::class, function (MockInterface $mock) {
            $mock->shouldReceive('record')
                ->once()
                ->andThrow(new RuntimeException('Audit storage failed.'));
        });

        $this->submitReschedule($user, $reservation)
            ->assertStatus(500);

        $this->assertSame(
            0,
            FacilityReservationRescheduleRequest::query()
                ->where('facility_reservation_id', $reservation->id)
                ->count()
        );

        $this->assertSame($before, $this->rescheduleCount());
    }

    // RSV-AL-20: Payment preparation is not successful payment.
    public function test_preparing_payment_does_not_create_success_log(): void
    {
        [$user, $reservation, $payment, $attempt] =
            $this->createPendingPayment();

        $this->assertSame('pending', $payment->fresh()->status);

        $this->assertSame(
            0,
            $this->paymentLogs($payment)->count()
        );

        $this->assertSame(
            1,
            $this->reservationLogs($reservation)->count()
        );
    }
}
