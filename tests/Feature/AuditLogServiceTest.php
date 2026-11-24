<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use Tests\Support\PostgresTicketTestCase;

class AuditLogServiceTest extends PostgresTicketTestCase
{
    // AL-01: Mencatat aktivitas pengguna.
    public function test_service_records_user_activity(): void
    {
        $actor = $this->account('petugas');

        $log = app(AuditLogService::class)->record(
            actor: $actor,
            module: 'Pembayaran',
            action: 'payment_confirmed',
            description: 'Pembayaran Cash dikonfirmasi.',
            subjectType: 'TicketPayment',
            subjectId: 10
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'user_id' => $actor->id,
            'actor_name' => $actor->name,
            'actor_role' => 'petugas',
            'module' => 'Pembayaran',
            'action' => 'payment_confirmed',
            'subject_type' => 'TicketPayment',
            'subject_id' => '10',
        ]);

        $this->assertNotNull($log->created_at);

        $this->assertSame(
            $actor->id,
            $log->user->id
        );
    }

    // AL-02: Mencatat aktivitas otomatis oleh sistem.
    public function test_service_records_system_activity(): void
    {
        $log = app(AuditLogService::class)->record(
            actor: null,
            module: 'Pembayaran',
            action: 'qris_payment_confirmed',
            description: 'Pembayaran QRIS dikonfirmasi oleh sistem.'
        );

        $log->refresh();

        $this->assertNull($log->user_id);
        $this->assertSame('Sistem', $log->actor_name);
        $this->assertSame('system', $log->actor_role);
        $this->assertNull($log->user);
    }

    // AL-03: Menyimpan histori sebelum dan sesudah perubahan.
    public function test_service_stores_change_history(): void
    {
        $actor = $this->account('petugas');

        $log = app(AuditLogService::class)->record(
            actor: $actor,
            module: 'Pembayaran',
            action: 'payment_confirmed',
            description: 'Status pembayaran diperbarui.',
            subjectType: 'TicketPayment',
            subjectId: 11,
            oldValues: [
                'status' => 'pending',
            ],
            newValues: [
                'status' => 'paid',
                'amount' => '50000.00',
            ]
        );

        $log->refresh();

        $this->assertSame([
            'status' => 'pending',
        ], $log->old_values);

        $this->assertSame([
            'status' => 'paid',
            'amount' => '50000.00',
        ], $log->new_values);
    }

    // AL-04: Data sensitif tidak disimpan dalam histori.
    public function test_service_excludes_sensitive_values(): void
    {
        $actor = $this->account('admin');

        $log = app(AuditLogService::class)->record(
            actor: $actor,
            module: 'Autentikasi',
            action: 'profile_updated',
            description: 'Data pengguna diperbarui.',
            oldValues: [
                'status' => 'active',
                'password' => 'secret-old-password',
                'remember_token' => 'secret-token',
                'identity_number' => 'TEST-IDENTITY',
            ],
            newValues: [
                'status' => 'active',
                'password' => 'secret-new-password',
                'qr_token' => 'secret-qr',
                'server_key' => 'secret-midtrans',
                'email' => 'private@example.test',
                'role' => [
                    'password' => 'nested-secret',
                ],
            ]
        );

        $log->refresh();

        $this->assertSame([
            'status' => 'active',
        ], $log->old_values);

        $this->assertSame([
            'status' => 'active',
        ], $log->new_values);
    }

    // AL-05: Snapshot pengguna tidak berubah.
    public function test_actor_snapshot_is_preserved(): void
    {
        $actor = $this->account('petugas');

        $originalName = $actor->name;

        $log = app(AuditLogService::class)->record(
            actor: $actor,
            module: 'Autentikasi',
            action: 'login',
            description: 'Pengguna berhasil login.'
        );

        $actor->update([
            'name' => 'Nama Pengguna Baru',
            'role' => 'admin',
        ]);

        $log->refresh();

        $this->assertSame(
            $originalName,
            $log->actor_name
        );

        $this->assertSame(
            'petugas',
            $log->actor_role
        );

        $this->assertSame(
            'Nama Pengguna Baru',
            $log->user->name
        );
    }

    // AL-06: Histori tetap tersedia ketika akun dihapus.
    public function test_audit_log_survives_user_deletion(): void
    {
        $actor = $this->account('wisatawan');

        $originalName = $actor->name;

        $log = app(AuditLogService::class)->record(
            actor: $actor,
            module: 'Autentikasi',
            action: 'login',
            description: 'Pengguna berhasil login.'
        );

        $actor->delete();

        $log = AuditLog::findOrFail($log->id);

        $this->assertNull($log->user_id);

        $this->assertSame(
            $originalName,
            $log->actor_name
        );

        $this->assertSame(
            'wisatawan',
            $log->actor_role
        );
    }

    // AL-07: Histori kosong disimpan sebagai null.
    public function test_empty_change_history_is_stored_as_null(): void
    {
        $log = app(AuditLogService::class)->record(
            actor: null,
            module: 'Sistem',
            action: 'test_activity',
            description: 'Pengujian histori kosong.',
            oldValues: [],
            newValues: [
                'password' => 'secret-password',
            ]
        );

        $log->refresh();

        $this->assertNull($log->old_values);
        $this->assertNull($log->new_values);
    }
}
