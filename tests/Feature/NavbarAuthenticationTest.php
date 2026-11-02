<?php

namespace Tests\Feature;

use Tests\Support\PostgresTicketTestCase;

class NavbarAuthenticationTest extends PostgresTicketTestCase
{
    public function test_guest_home_offers_login_without_logout(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('aria-label="Masuk"', false)
            ->assertDontSee('action="'.route('logout').'"', false);
    }

    public function test_public_home_respects_each_authenticated_role_and_allows_logout(): void
    {
        foreach (['wisatawan' => 'wisatawan.profil', 'petugas' => 'petugas.dashboard', 'admin' => 'admin.dashboard'] as $role => $accountRoute) {
            $this->actingAs($this->account($role))->get('/')->assertOk()
                ->assertSee('aria-label="Profil / Akun"', false)
                ->assertSee('href="'.route($accountRoute).'"', false)
                ->assertDontSee('aria-label="Masuk"', false)
                ->assertSee('Keluar')
                ->assertSee('action="'.route('logout').'"', false);

            $this->post(route('logout'))->assertRedirect(route('login'));
            $this->assertGuest();
            $this->get('/')->assertOk()->assertSee('aria-label="Masuk"', false)
                ->assertDontSee('action="'.route('logout').'"', false);
        }
    }
}
