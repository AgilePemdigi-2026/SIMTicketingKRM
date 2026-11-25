<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\Support\PostgresTicketTestCase;

class AdminProfileTest extends PostgresTicketTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(
            'simticketingkrm_purchase_test',
            DB::connection()->getDatabaseName()
        );

        Http::preventStrayRequests();
    }

    private function profilePayload(
        User $admin,
        array $overrides = []
    ): array {
        return array_replace([
            'name' => 'Admin Profil Tes',
            'email' => $admin->email,
            'phone' => $admin->phone,
        ], $overrides);
    }

    private function passwordPayload(
        array $overrides = []
    ): array {
        return array_replace([
            'current_password' => 'test-only-password',
            'password' => 'New-Test-Password-2026!',
            'password_confirmation' => 'New-Test-Password-2026!',
        ], $overrides);
    }

    // PRF-01
    public function test_admin_can_open_all_profile_pages(): void
    {
        $admin = $this->account('admin');

        foreach ([
            'admin.profile.show',
            'admin.profile.edit',
            'admin.profile.password.edit',
        ] as $name) {
            $this->actingAs($admin)
                ->get(route($name))
                ->assertOk()
                ->assertSee($admin->name)
                ->assertSee($admin->email)
                ->assertDontSee($admin->password);
        }
    }

    // PRF-02
    public function test_other_roles_cannot_access_admin_profile(): void
    {
        foreach (['petugas', 'wisatawan'] as $role) {
            $user = $this->account($role);

            foreach ([
                'admin.profile.show',
                'admin.profile.edit',
                'admin.profile.password.edit',
            ] as $name) {
                $this->actingAs($user)
                    ->get(route($name))
                    ->assertForbidden();
            }

            $this->actingAs($user)
                ->put(
                    route('admin.profile.update'),
                    $this->profilePayload($user)
                )
                ->assertForbidden();

            $this->actingAs($user)
                ->put(
                    route('admin.profile.password.update'),
                    $this->passwordPayload()
                )
                ->assertForbidden();
        }
    }

    // PRF-03
    public function test_guest_cannot_access_admin_profile(): void
    {
        $this->get(route('admin.profile.show'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.profile.edit'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.profile.password.edit'))
            ->assertRedirect(route('login'));
    }

    // PRF-04
    public function test_admin_can_update_own_profile(): void
    {
        $admin = $this->account('admin');

        $admin->email_verified_at = now();
        $admin->save();

        $email = 'updated-'.bin2hex(random_bytes(6))
            .'@example.com';

        $phone = '08'.random_int(1000000000, 9999999999);

        $this->actingAs($admin)
            ->put(
                route('admin.profile.update'),
                $this->profilePayload($admin, [
                    'name' => 'Admin Updated',
                    'email' => $email,
                    'phone' => $phone,
                ])
            )
            ->assertRedirect(route('admin.profile.show'))
            ->assertSessionHas('success');

        $admin->refresh();

        $this->assertSame('Admin Updated', $admin->name);
        $this->assertSame($email, $admin->email);
        $this->assertSame($phone, $admin->phone);
        $this->assertSame('admin', $admin->role);
        $this->assertNull($admin->email_verified_at);
    }

    // PRF-05
    public function test_duplicate_email_is_rejected(): void
    {
        $admin = $this->account('admin');
        $other = $this->account('admin');

        $originalEmail = $admin->email;

        $this->actingAs($admin)
            ->from(route('admin.profile.edit'))
            ->put(
                route('admin.profile.update'),
                $this->profilePayload($admin, [
                    'email' => $other->email,
                ])
            )
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHasErrors('email');

        $this->assertSame(
            $originalEmail,
            $admin->fresh()->email
        );
    }

    // PRF-06
    public function test_duplicate_phone_is_rejected(): void
    {
        $admin = $this->account('admin');
        $other = $this->account('admin');

        $other->phone = '08'.random_int(
            1000000000,
            9999999999
        );

        $other->save();

        $this->actingAs($admin)
            ->from(route('admin.profile.edit'))
            ->put(
                route('admin.profile.update'),
                $this->profilePayload($admin, [
                    'phone' => $other->phone,
                ])
            )
            ->assertSessionHasErrors('phone');
    }

    // PRF-07
    public function test_profile_request_cannot_change_user_role(): void
    {
        $admin = $this->account('admin');

        $this->actingAs($admin)
            ->put(
                route('admin.profile.update'),
                $this->profilePayload($admin, [
                    'role' => 'wisatawan',
                    'identity_number' => 'UNAUTHORIZED-ID',
                    'visitor_type' => 'mancanegara',
                ])
            )
            ->assertRedirect(route('admin.profile.show'));

        $admin->refresh();

        $this->assertSame('admin', $admin->role);

        $this->assertNotSame(
            'UNAUTHORIZED-ID',
            $admin->identity_number
        );

        $this->assertSame(
            'domestik',
            $admin->visitor_type
        );
    }

    // PRF-08
    public function test_admin_cannot_update_another_account(): void
    {
        $admin = $this->account('admin');
        $other = $this->account('admin');

        $originalName = $other->name;

        $this->actingAs($admin)
            ->put(
                route('admin.profile.update'),
                $this->profilePayload($admin, [
                    'id' => $other->id,
                    'user_id' => $other->id,
                    'name' => 'Own Account Updated',
                ])
            )
            ->assertRedirect(route('admin.profile.show'));

        $this->assertSame(
            $originalName,
            $other->fresh()->name
        );

        $this->assertSame(
            'Own Account Updated',
            $admin->fresh()->name
        );
    }

    // PRF-09
    public function test_wrong_current_password_is_rejected(): void
    {
        $admin = $this->account('admin');

        $originalHash = $admin->password;

        $this->actingAs($admin)
            ->from(route('admin.profile.password.edit'))
            ->put(
                route('admin.profile.password.update'),
                $this->passwordPayload([
                    'current_password' => 'wrong-password',
                ])
            )
            ->assertSessionHasErrors('current_password');

        $this->assertSame(
            $originalHash,
            $admin->fresh()->password
        );
    }

    // PRF-10
    public function test_password_confirmation_must_match(): void
    {
        $admin = $this->account('admin');

        $this->actingAs($admin)
            ->put(
                route('admin.profile.password.update'),
                $this->passwordPayload([
                    'password_confirmation' => 'different-password',
                ])
            )
            ->assertSessionHasErrors('password');
    }

    // PRF-11
    public function test_short_password_is_rejected(): void
    {
        $admin = $this->account('admin');

        $this->actingAs($admin)
            ->put(
                route('admin.profile.password.update'),
                $this->passwordPayload([
                    'password' => '123',
                    'password_confirmation' => '123',
                ])
            )
            ->assertSessionHasErrors('password');
    }

    // PRF-12
    public function test_valid_password_change_is_hashed(): void
    {
        $admin = $this->account('admin');

        $originalHash = $admin->password;
        $originalRememberToken = $admin->getRememberToken();

        $this->actingAs($admin)
            ->put(
                route('admin.profile.password.update'),
                $this->passwordPayload()
            )
            ->assertRedirect(route('admin.profile.show'))
            ->assertSessionHas('success');

        $admin->refresh();

        $this->assertTrue(
            Hash::check(
                'New-Test-Password-2026!',
                $admin->password
            )
        );

        $this->assertFalse(
            Hash::check(
                'test-only-password',
                $admin->password
            )
        );

        $this->assertNotSame(
            $originalHash,
            $admin->password
        );

        $this->assertNotSame(
            'New-Test-Password-2026!',
            $admin->password
        );

        $this->assertNotSame(
            $originalRememberToken,
            $admin->getRememberToken()
        );
    }

    // PRF-13
    public function test_profile_changes_are_audited_without_secrets(): void
    {
        $admin = $this->account('admin');

        $this->actingAs($admin)
            ->put(
                route('admin.profile.update'),
                $this->profilePayload($admin)
            )
            ->assertRedirect();

        $this->actingAs($admin)
            ->put(
                route('admin.profile.password.update'),
                $this->passwordPayload()
            )
            ->assertRedirect();

        foreach ([
            'profile_updated',
            'password_updated',
        ] as $action) {
            $log = AuditLog::query()
                ->where('user_id', $admin->id)
                ->where('module', 'Profil')
                ->where('action', $action)
                ->firstOrFail();

            $this->assertSame('User', $log->subject_type);

            $this->assertSame(
                (string) $admin->id,
                $log->subject_id
            );

            $this->assertNull($log->old_values);
            $this->assertNull($log->new_values);

            $serialized = $log->toJson();

            $this->assertStringNotContainsString(
                'test-only-password',
                $serialized
            );

            $this->assertStringNotContainsString(
                'New-Test-Password-2026!',
                $serialized
            );

            $this->assertStringNotContainsString(
                $admin->fresh()->password,
                $serialized
            );
        }
    }

    // PRF-14
    public function test_profile_dropdown_has_no_duplicate_logout(): void
    {
        $admin = $this->account('admin');

        $response = $this->actingAs($admin)
            ->get(route('admin.profile.show'))
            ->assertOk();

        $document = new DOMDocument();

        $previous = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="UTF-8">'
                .$response->getContent()
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $xpath = new DOMXPath($document);

        $menu = $xpath->query(
            '//details[@id="admin-profile-menu"]'
        );

        $this->assertSame(1, $menu->length);

        $this->assertSame(
            0,
            $xpath->query(
                './/form[@action="'.route('logout').'"]',
                $menu->item(0)
            )->length
        );

        foreach ([
            'admin.profile.show',
            'admin.profile.edit',
            'admin.profile.password.edit',
        ] as $name) {
            $this->assertSame(
                1,
                $xpath->query(
                    './/a[@href="'.route($name).'"]',
                    $menu->item(0)
                )->length
            );
        }

        $this->assertSame(
            2,
            $xpath->query(
                '//form[@action="'.route('logout').'"]'
            )->length
        );
    }

    public function test_new_password_must_differ_from_current_password(): void
    {
        $admin = $this->account('admin');

        $this->actingAs($admin)
            ->put(
                route('admin.profile.password.update'),
                $this->passwordPayload([
                    'password' => 'test-only-password',
                    'password_confirmation' => 'test-only-password',
                ])
            )
            ->assertSessionHasErrors('password');
    }
}
