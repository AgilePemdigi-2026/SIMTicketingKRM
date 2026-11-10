<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

class HttpsUrlGenerationTest extends TestCase
{
    public function test_https_app_url_keeps_login_redirect_secure_behind_an_http_proxy(): void
    {
        config(['app.url' => 'https://mangrotix.example']);
        $this->app->register(AppServiceProvider::class, force: true);

        $this->get('http://mangrotix.example/beranda')
            ->assertRedirect('https://mangrotix.example/login');
    }

    public function test_local_http_app_url_keeps_login_redirect_on_http(): void
    {
        config(['app.url' => 'http://localhost']);
        $this->app->register(AppServiceProvider::class, force: true);

        $this->get('http://localhost/beranda')
            ->assertRedirect('http://localhost/login');
    }
}
