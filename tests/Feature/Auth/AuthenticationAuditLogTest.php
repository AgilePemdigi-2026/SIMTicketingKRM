<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Mockery\MockInterface;
use RuntimeException;
use Tests\Support\PostgresTicketTestCase;

class AuthenticationAuditLogTest extends PostgresTicketTestCase
{
    private function loginUser(
        User $user,
        ?string $identifier = null
    ) {
        return $this->post(route('login.store'), [
            'login' => $identifier ?? $user->email,
            'password' => 'test-only-password',
        ]);
    }

    private function assertAuthenticationLog(
        User $user,
        string $action
    ): void {
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'actor_name' => $user->name,
            'actor_role' => $user->role,
            'module' => 'Autentikasi',
            'action' => $action,
            'subject_type' => 'User',
            'subject_id' => (string) $user->id,
        ]);
    }

    // AUTH-01: Login Admin dicatat.
    public function test_admin_login_is_recorded(): void
    {
        $user = $this->account('admin');

        $this->loginUser($user)
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->assertAuthenticationLog($user, 'login');
    }

    // AUTH-02: Login Petugas dicatat.
    public function test_petugas_login_is_recorded(): void
    {
        $user = $this->account('petugas');

        $this->loginUser($user)
            ->assertRedirect(route('petugas.dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->assertAuthenticationLog($user, 'login');
    }

    // AUTH-03: Login Wisatawan dicatat.
    public function test_wisatawan_login_is_recorded(): void
    {
        $user = $this->account('wisatawan');

        $this->loginUser($user)
            ->assertRedirect(route('wisatawan.beranda'));

        $this->assertAuthenticatedAs($user);

        $this->assertAuthenticationLog($user, 'login');
    }

    // AUTH-04: Login dengan nomor telepon dicatat.
    public function test_phone_login_is_recorded(): void
    {
        $user = $this->account('wisatawan');

        $user->update([
            'phone' => '089876543210',
        ]);

        $this->loginUser(
            $user,
            $user->phone
        )->assertRedirect(route('wisatawan.beranda'));

        $this->assertAuthenticatedAs($user);

        $this->assertAuthenticationLog($user, 'login');
    }

    // AUTH-05: Password salah tidak menghasilkan log sukses.
    public function test_invalid_password_does_not_record_login(): void
    {
        $user = $this->account('wisatawan');

        $this->from(route('login'))
            ->post(route('login.store'), [
                'login' => $user->email,
                'password' => 'incorrect-password',
            ])
            ->assertSessionHasErrors('login');

        $this->assertGuest();

        $this->assertDatabaseMissing('audit_logs', [
            'user_id' => $user->id,
            'action' => 'login',
        ]);
    }

    // AUTH-06: Email tidak terdaftar tidak menghasilkan log.
    public function test_unknown_email_does_not_record_login(): void
    {
        $before = AuditLog::count();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'login' => 'not-registered@example.test',
                'password' => 'test-only-password',
            ])
            ->assertSessionHasErrors('login');

        $this->assertGuest();

        $this->assertSame(
            $before,
            AuditLog::count()
        );
    }

    // AUTH-07: Form kosong tidak menghasilkan log.
    public function test_invalid_login_input_does_not_create_log(): void
    {
        $before = AuditLog::count();

        $this->from(route('login'))
            ->post(route('login.store'), [])
            ->assertSessionHasErrors([
                'login',
                'password',
            ]);

        $this->assertGuest();

        $this->assertSame(
            $before,
            AuditLog::count()
        );
    }

    // AUTH-08: Logout Admin dicatat.
    public function test_admin_logout_is_recorded(): void
    {
        $user = $this->account('admin');

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->assertAuthenticationLog($user, 'logout');
    }

    // AUTH-09: Logout Petugas dicatat.
    public function test_petugas_logout_is_recorded(): void
    {
        $user = $this->account('petugas');

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->assertAuthenticationLog($user, 'logout');
    }

    // AUTH-10: Logout Wisatawan dicatat.
    public function test_wisatawan_logout_is_recorded(): void
    {
        $user = $this->account('wisatawan');

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->assertAuthenticationLog($user, 'logout');
    }

    // AUTH-11: Guest tidak menghasilkan log logout.
    public function test_guest_logout_does_not_create_log(): void
    {
        $before = AuditLog::count();

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertSame(
            $before,
            AuditLog::count()
        );
    }

    // AUTH-12: Login dan logout masing-masing dicatat sekali.
    public function test_login_and_logout_are_recorded_once(): void
    {
        $user = $this->account('wisatawan');

        $this->loginUser($user)
            ->assertRedirect(route('wisatawan.beranda'));

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        // Percobaan logout ulang sebagai guest.
        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $logs = AuditLog::query()
            ->where('user_id', $user->id)
            ->where('module', 'Autentikasi');

        $this->assertSame(
            1,
            (clone $logs)->where('action', 'login')->count()
        );

        $this->assertSame(
            1,
            (clone $logs)->where('action', 'logout')->count()
        );
    }

    // AUTH-13: Audit log login tidak menyimpan kredensial.
    public function test_login_log_does_not_store_credentials(): void
    {
        $user = $this->account('admin');

        $this->loginUser($user)
            ->assertRedirect(route('admin.dashboard'));

        $log = AuditLog::query()
            ->where('user_id', $user->id)
            ->where('action', 'login')
            ->firstOrFail();

        $this->assertNull($log->old_values);
        $this->assertNull($log->new_values);

        $this->assertStringNotContainsString(
            'test-only-password',
            $log->toJson()
        );

        $this->assertStringNotContainsString(
            $user->email,
            $log->toJson()
        );
    }

    // AUTH-14: Login dibatalkan jika audit log gagal disimpan.
    public function test_login_is_cancelled_when_audit_logging_fails(): void
    {
        $user = $this->account('admin');

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

        $this->loginUser($user)
            ->assertStatus(500);

        $this->assertGuest();
    }

    // AUTH-15: Logout tetap berhasil jika audit log gagal.
    public function test_logout_still_succeeds_when_audit_logging_fails(): void
    {
        $user = $this->account('admin');

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

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
