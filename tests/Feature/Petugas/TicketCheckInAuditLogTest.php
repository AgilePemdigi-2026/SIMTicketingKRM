<?php

namespace Tests\Feature\Petugas;

use App\Listeners\IssueTicketsAfterPayment;
use App\Models\AuditLog;
use App\Models\IssuedTicket;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\TicketPurchaseService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use RuntimeException;
use Tests\Support\PostgresTicketTestCase;

class TicketCheckInAuditLogTest extends PostgresTicketTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->travelTo(
            CarbonImmutable::parse(
                '2026-11-24 12:00:00',
                'Asia/Jakarta'
            )->utc()
        );
    }

    protected function tearDown(): void
    {
        $this->travelBack();

        parent::tearDown();
    }

    private function createTicket(
        bool $paid = true,
        ?string $visitDate = null
    ): IssuedTicket {
        $user = $this->account('wisatawan');

        $payload = $this->payload();

        $payload['visit_date'] = $visitDate
            ?? now('Asia/Jakarta')->toDateString();

        $order = app(TicketPurchaseService::class)
            ->create($user, $payload);

        $order->update([
            'status' => 'paid',
        ]);

        $payment = $order->payment()->create([
            'code' => (string) Str::uuid(),
            'method' => 'cash',
            'amount' => $order->total,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Issue tickets inside the isolated test transaction.
        app(IssueTicketsAfterPayment::class)
            ->issueForOrder($order->code);

        $ticket = $order->issuedTickets()->firstOrFail();

        if (! $paid) {
            $payment->update([
                'status' => 'pending',
                'paid_at' => null,
            ]);

            $order->update([
                'status' => 'pending_payment',
            ]);
        }

        return $ticket;
    }

    private function checkIn(
        IssuedTicket $ticket,
        User $staff
    ) {
        return $this->actingAs($staff)
            ->post(
                route('petugas.validasi-tiket.check-in'),
                [
                    'qr_token' => $ticket->qr_token,
                ]
            );
    }

    private function ticketLogs(IssuedTicket $ticket)
    {
        return AuditLog::query()
            ->where('module', 'Validasi Tiket')
            ->where('action', 'ticket_checked_in')
            ->where('subject_type', 'IssuedTicket')
            ->where('subject_id', (string) $ticket->id);
    }

    // CI-AL-01: Check-in berhasil membuat audit log.
    public function test_successful_checkin_creates_audit_log(): void
    {
        $ticket = $this->createTicket();
        $staff = $this->account('petugas');

        $this->checkIn($ticket, $staff)
            ->assertRedirect(
                route('petugas.validasi-tiket.index')
            )
            ->assertSessionHas('success');

        $this->assertSame(
            'used',
            $ticket->fresh()->status
        );

        $this->assertSame(
            1,
            $this->ticketLogs($ticket)->count()
        );
    }

    // CI-AL-02: Identitas petugas dicatat.
    public function test_checkin_records_staff_identity(): void
    {
        $ticket = $this->createTicket();
        $staff = $this->account('petugas');

        $this->checkIn($ticket, $staff);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $staff->id,
            'actor_name' => $staff->name,
            'actor_role' => 'petugas',
            'module' => 'Validasi Tiket',
            'action' => 'ticket_checked_in',
            'subject_type' => 'IssuedTicket',
            'subject_id' => (string) $ticket->id,
        ]);

        $this->assertSame(
            $staff->id,
            $ticket->fresh()->checked_in_by
        );
    }

    // CI-AL-03: Histori perubahan status tersimpan.
    public function test_checkin_records_status_history(): void
    {
        $ticket = $this->createTicket();
        $staff = $this->account('petugas');

        $this->checkIn($ticket, $staff);

        $log = $this->ticketLogs($ticket)->firstOrFail();

        $this->assertSame(
            'active',
            $log->old_values['status']
        );

        $this->assertSame(
            'used',
            $log->new_values['status']
        );

        $this->assertNull(
            $log->old_values['used_at']
        );

        $this->assertNull(
            $log->old_values['checked_in_by']
        );

        $this->assertSame(
            $staff->id,
            $log->new_values['checked_in_by']
        );

        $this->assertSame(
            now('Asia/Jakarta')->toDateString(),
            $log->new_values['visit_date']
        );

        $this->assertSame(
            'online',
            $log->new_values['sale_channel']
        );
    }

    // CI-AL-04: Timestamp audit log sesuai waktu check-in.
    public function test_checkin_records_correct_timestamp(): void
    {
        $ticket = $this->createTicket();
        $staff = $this->account('petugas');

        $this->checkIn($ticket, $staff);

        $ticket->refresh();

        $log = $this->ticketLogs($ticket)->firstOrFail();

        $this->assertNotNull($ticket->used_at);
        $this->assertNotNull($log->created_at);

        $this->assertSame(
            $ticket->used_at->getTimestamp(),
            CarbonImmutable::parse(
                $log->new_values['used_at']
            )->getTimestamp()
        );

        $this->assertSame(
            now()->getTimestamp(),
            $log->created_at->getTimestamp()
        );
    }

    // CI-AL-05: Verify saja tidak mencatat check-in sukses.
    public function test_verify_without_checkin_does_not_create_log(): void
    {
        $ticket = $this->createTicket();
        $staff = $this->account('petugas');

        $response = $this->actingAs($staff)
            ->post(
                route('petugas.validasi-tiket.verify'),
                [
                    'qr_token' => $ticket->qr_token,
                ]
            );

        $response->assertOk();

        $response->assertViewHas(
            'result',
            fn ($result) => $result['valid'] === true
        );

        $this->assertSame(
            'active',
            $ticket->fresh()->status
        );

        $this->assertSame(
            0,
            $this->ticketLogs($ticket)->count()
        );
    }

    // CI-AL-06: Check-in ulang tidak menduplikasi audit log.
    public function test_repeated_checkin_does_not_duplicate_log(): void
    {
        $ticket = $this->createTicket();
        $firstStaff = $this->account('petugas');
        $secondStaff = $this->account('petugas');

        $this->checkIn($ticket, $firstStaff)
            ->assertSessionHas('success');

        $this->checkIn($ticket, $secondStaff)
            ->assertSessionHasErrors('qr_token');

        $this->assertSame(
            1,
            $this->ticketLogs($ticket)->count()
        );

        $this->assertSame(
            $firstStaff->id,
            $ticket->fresh()->checked_in_by
        );
    }

    // CI-AL-07: Tiket belum lunas ditolak.
    public function test_unpaid_ticket_does_not_create_log(): void
    {
        $ticket = $this->createTicket(false);
        $staff = $this->account('petugas');

        $this->checkIn($ticket, $staff)
            ->assertSessionHasErrors('qr_token');

        $this->assertSame(
            'active',
            $ticket->fresh()->status
        );

        $this->assertSame(
            0,
            $this->ticketLogs($ticket)->count()
        );
    }

    // CI-AL-08: Tanggal kunjungan tidak sesuai ditolak.
    public function test_wrong_visit_date_does_not_create_log(): void
    {
        $ticket = $this->createTicket(
            true,
            now('Asia/Jakarta')->addDay()->toDateString()
        );

        $staff = $this->account('petugas');

        $this->checkIn($ticket, $staff)
            ->assertSessionHasErrors('qr_token');

        $this->assertSame(
            'active',
            $ticket->fresh()->status
        );

        $this->assertSame(
            0,
            $this->ticketLogs($ticket)->count()
        );
    }

    // CI-AL-09: Token QR tidak ditemukan.
    public function test_unknown_qr_token_does_not_create_log(): void
    {
        $staff = $this->account('petugas');

        $before = AuditLog::count();

        $this->actingAs($staff)
            ->post(
                route('petugas.validasi-tiket.check-in'),
                [
                    'qr_token' => Str::random(64),
                ]
            )
            ->assertSessionHasErrors('qr_token');

        $this->assertSame(
            $before,
            AuditLog::count()
        );
    }

    // CI-AL-10: Wisatawan tidak memiliki akses check-in.
    public function test_wisatawan_cannot_checkin_ticket(): void
    {
        $ticket = $this->createTicket();

        $this->checkIn(
            $ticket,
            $this->account('wisatawan')
        )->assertForbidden();

        $this->assertSame(
            'active',
            $ticket->fresh()->status
        );

        $this->assertSame(
            0,
            $this->ticketLogs($ticket)->count()
        );
    }

    // CI-AL-11: Admin tidak memiliki akses check-in petugas.
    public function test_admin_cannot_checkin_ticket(): void
    {
        $ticket = $this->createTicket();

        $this->checkIn(
            $ticket,
            $this->account('admin')
        )->assertForbidden();

        $this->assertSame(
            'active',
            $ticket->fresh()->status
        );

        $this->assertSame(
            0,
            $this->ticketLogs($ticket)->count()
        );
    }

    // CI-AL-12: Token QR dan identitas tidak tersimpan.
    public function test_checkin_log_does_not_store_private_values(): void
    {
        $ticket = $this->createTicket();
        $staff = $this->account('petugas');

        $this->checkIn($ticket, $staff);

        $log = $this->ticketLogs($ticket)->firstOrFail();

        $this->assertStringNotContainsString(
            $ticket->qr_token,
            $log->toJson()
        );

        $this->assertArrayNotHasKey(
            'qr_token',
            $log->new_values
        );

        $this->assertArrayNotHasKey(
            'identity_number',
            $log->new_values
        );

        $this->assertStringNotContainsString(
            $ticket->visitor->identity_number,
            $log->toJson()
        );
    }

    // CI-AL-13: Rollback membatalkan status tiket dan log.
    public function test_checkin_rollback_removes_audit_log(): void
    {
        $ticket = $this->createTicket();
        $staff = $this->account('petugas');

        $this->testConnection->beginTransaction();

        try {
            $this->checkIn($ticket, $staff)
                ->assertSessionHas('success');

            $this->assertSame(
                1,
                $this->ticketLogs($ticket)->count()
            );
        } finally {
            $this->testConnection->rollBack();
        }

        $ticket->refresh();

        $this->assertSame('active', $ticket->status);
        $this->assertNull($ticket->used_at);
        $this->assertNull($ticket->checked_in_by);

        $this->assertSame(
            0,
            $this->ticketLogs($ticket)->count()
        );
    }

    // CI-AL-14: Check-in dibatalkan jika audit log gagal.
    public function test_checkin_rolls_back_when_audit_logging_fails(): void
    {
        $ticket = $this->createTicket();
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

        $this->checkIn($ticket, $staff)
            ->assertStatus(500);

        $ticket->refresh();

        $this->assertSame('active', $ticket->status);
        $this->assertNull($ticket->used_at);
        $this->assertNull($ticket->checked_in_by);

        $this->assertSame(
            0,
            $this->ticketLogs($ticket)->count()
        );
    }

    // CI-AL-15: Format QR tidak valid ditolak.
    public function test_invalid_qr_format_does_not_create_log(): void
    {
        $staff = $this->account('petugas');

        $before = AuditLog::count();

        $this->actingAs($staff)
            ->post(
                route('petugas.validasi-tiket.check-in'),
                [
                    'qr_token' => 'invalid',
                ]
            )
            ->assertSessionHasErrors('qr_token');

        $this->assertSame(
            $before,
            AuditLog::count()
        );
    }

    // CI-AL-16: Guest tidak dapat melakukan check-in.
    public function test_guest_cannot_checkin_ticket(): void
    {
        $ticket = $this->createTicket();

        $this->post(
            route('petugas.validasi-tiket.check-in'),
            [
                'qr_token' => $ticket->qr_token,
            ]
        )->assertRedirect(route('login'));

        $this->assertSame(
            'active',
            $ticket->fresh()->status
        );

        $this->assertSame(
            0,
            $this->ticketLogs($ticket)->count()
        );
    }
}
