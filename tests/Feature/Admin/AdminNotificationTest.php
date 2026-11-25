<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Services\AdminNotificationService;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\PostgresTicketTestCase;

class AdminNotificationTest extends PostgresTicketTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(
            'simticketingkrm_purchase_test',
            DB::connection()->getDatabaseName()
        );

        Http::preventStrayRequests();

        // This cleanup is limited to the isolated test transaction.
        DB::table('admin_notification_reads')->delete();
        DB::table('audit_logs')->delete();
    }

    private function notification(
        string $action = 'payment_confirmed'
    ): AuditLog {
        return app(AuditLogService::class)->record(
            actor: null,
            module: 'Operasional',
            action: $action,
            description: 'Aktivitas operasional pengujian.',
            subjectType: 'Test',
            subjectId: 'TEST-001'
        );
    }

    private function service(): AdminNotificationService
    {
        return app(AdminNotificationService::class);
    }

    // NOT-01
    public function test_admin_can_open_notification_history(): void
    {
        $admin = $this->account('admin');

        $this->notification();

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Semua Notifikasi')
            ->assertSee('Pembayaran Cash berhasil')
            ->assertViewHas('unreadCount', 1);
    }

    // NOT-02
    public function test_other_roles_cannot_access_notifications(): void
    {
        $log = $this->notification();

        foreach (['petugas', 'wisatawan'] as $role) {
            $user = $this->account($role);

            $this->actingAs($user)
                ->get(route('admin.notifications.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->post(route('admin.notifications.read', $log->id))
                ->assertForbidden();

            $this->actingAs($user)
                ->post(route('admin.notifications.read-all'))
                ->assertForbidden();
        }

        $this->assertDatabaseCount(
            'admin_notification_reads',
            0
        );
    }

    // NOT-03
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.notifications.index'))
            ->assertRedirect(route('login'));
    }

    // NOT-04
    public function test_authentication_events_are_excluded(): void
    {
        $admin = $this->account('admin');

        $this->notification('login');
        $this->notification('logout');

        $this->assertSame(
            0,
            $this->service()->unreadCount($admin)
        );

        $this->assertCount(
            0,
            $this->service()->latest($admin)
        );
    }

    // NOT-05
    public function test_all_operational_actions_are_supported(): void
    {
        $admin = $this->account('admin');

        $actions = [
            'payment_confirmed',
            'qris_payment_confirmed',
            'ticket_checked_in',
            'reservation_created',
            'reservation_payment_confirmed',
            'reschedule_requested',
        ];

        foreach ($actions as $action) {
            $this->notification($action);
        }

        $this->assertSame(
            6,
            $this->service()->unreadCount($admin)
        );

        $this->assertEqualsCanonicalizing(
            $actions,
            $this->service()->latest($admin, 20)
                ->pluck('action')->all()
        );

        $this->assertCount(
            5,
            $this->service()->latest($admin)
        );
    }

    // NOT-06
    public function test_reading_notification_updates_read_status(): void
    {
        $admin = $this->account('admin');
        $log = $this->notification();

        $this->actingAs($admin)
            ->post(route('admin.notifications.read', $log->id))
            ->assertRedirect(
                route('admin.audit-log.show', $log->id)
            );

        $this->assertDatabaseHas(
            'admin_notification_reads',
            [
                'user_id' => $admin->id,
                'audit_log_id' => $log->id,
            ]
        );

        $this->assertSame(
            0,
            $this->service()->unreadCount($admin)
        );

        $this->assertNotNull(
            $this->service()->latest($admin)
                ->first()->notification_read_at
        );
    }

    // NOT-07
    public function test_repeated_read_does_not_duplicate_status(): void
    {
        $admin = $this->account('admin');
        $log = $this->notification();

        $this->actingAs($admin)
            ->post(route('admin.notifications.read', $log->id))
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.notifications.read', $log->id))
            ->assertRedirect();

        $this->assertDatabaseCount(
            'admin_notification_reads',
            1
        );
    }

    // NOT-08
    public function test_admin_can_mark_all_notifications_as_read(): void
    {
        $admin = $this->account('admin');

        $this->notification();
        $this->notification('reservation_created');
        $this->notification('ticket_checked_in');
        $this->notification('login');

        $this->actingAs($admin)
            ->post(route('admin.notifications.read-all'))
            ->assertRedirect(route('admin.notifications.index'));

        $this->assertSame(
            0,
            $this->service()->unreadCount($admin)
        );

        $this->assertDatabaseCount(
            'admin_notification_reads',
            3
        );

        $this->assertCount(
            3,
            $this->service()->latest($admin)
        );
    }

    // NOT-09
    public function test_read_status_is_independent_for_each_admin(): void
    {
        $firstAdmin = $this->account('admin');
        $secondAdmin = $this->account('admin');

        $log = $this->notification();

        $this->service()->markRead(
            $firstAdmin,
            $log->id
        );

        $this->assertSame(
            0,
            $this->service()->unreadCount($firstAdmin)
        );

        $this->assertSame(
            1,
            $this->service()->unreadCount($secondAdmin)
        );

        $this->assertDatabaseMissing(
            'admin_notification_reads',
            [
                'user_id' => $secondAdmin->id,
                'audit_log_id' => $log->id,
            ]
        );
    }

    // NOT-10
    public function test_non_notification_activity_cannot_be_marked_read(): void
    {
        $admin = $this->account('admin');

        $log = $this->notification('login');

        $this->actingAs($admin)
            ->post(route('admin.notifications.read', $log->id))
            ->assertNotFound();

        $this->assertDatabaseCount(
            'admin_notification_reads',
            0
        );
    }

    // NOT-11
    public function test_read_status_does_not_modify_audit_log(): void
    {
        $admin = $this->account('admin');
        $log = $this->notification();

        $original = $log->fresh()->getRawOriginal();

        $this->service()->markRead($admin, $log->id);

        $this->assertSame(
            $original,
            $log->fresh()->getRawOriginal()
        );

        $this->assertDatabaseCount('audit_logs', 1);
    }

    // NOT-12
    public function test_empty_notification_state_is_displayed(): void
    {
        $admin = $this->account('admin');

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Belum ada notifikasi')
            ->assertViewHas('unreadCount', 0);
    }

    public function test_new_notification_is_unread_after_read_all(): void
    {
        $admin = $this->account('admin');

        $this->notification();

        $this->service()->markAllRead($admin);

        $newLog = $this->notification('reservation_created');

        $this->assertSame(
            1,
            $this->service()->unreadCount($admin)
        );

        $this->assertDatabaseMissing(
            'admin_notification_reads',
            [
                'user_id' => $admin->id,
                'audit_log_id' => $newLog->id,
            ]
        );
    }
}
