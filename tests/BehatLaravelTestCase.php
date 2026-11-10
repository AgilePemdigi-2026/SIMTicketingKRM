<?php

namespace Tests;

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;

class BehatLaravelTestCase
{
    use MakesHttpRequests;
    use InteractsWithExceptionHandling;

    protected Application $app;

    private ?TestResponse $response = null;

    public function startTestEnvironment(): void
    {
        $this->app = require Application::inferBasePath() . '/bootstrap/app.php';

        $kernel = $this->app->make(Kernel::class);
        $kernel->bootstrap();

        // Konfigurasi testing sebelum migration dijalankan
        $this->app->make('config')->set('app.env', 'testing');
        $this->app->make('config')->set('database.default', 'sqlite');
        $this->app->make('config')->set('database.connections.sqlite.database', ':memory:');
        $this->app->make('config')->set('session.driver', 'array');
        $this->app->make('config')->set('cache.default', 'array');

        // Pastikan koneksi SQLite menggunakan database in-memory
        $this->app->make('db')->purge('sqlite');
        $this->app->make('db')->connection('sqlite')->getPdo();

        // Jalankan seluruh migration pada SQLite in-memory
        $kernel->call('migrate:fresh', [
            '--database' => 'sqlite',
            '--force' => true,
        ]);

        $this->app->make(Kernel::class)->setArtisan(null);

        $this->app->instance('app', $this->app);
        $this->app->instance(
            'Illuminate\Contracts\Http\Kernel',
            $this->app->make('Illuminate\Contracts\Http\Kernel')
        );

        $this->app->make('router');
        $this->app->make('session')->driver()->start();
    }

    public function stopTestEnvironment(): void
    {
        if (isset($this->app)) {
            $this->app->make('db')->disconnect();
            $this->app->flush();
        }
    }

    public function getPage(string $uri): TestResponse
    {
        return $this->response = $this->get($uri);
    }

    public function getPageWithDebug(string $uri): TestResponse
    {
        $this->withoutExceptionHandling();

        return $this->response = $this->get($uri);
    }

    public function postJsonPage(string $uri, array $data = []): TestResponse
    {
        return $this->response = $this->postJson($uri, $data);
    }

    public function postPage(string $uri, array $data = []): TestResponse
    {
        return $this->response = $this->post($uri, $data);
    }

    public function patchPage(string $uri, array $data = []): TestResponse
    {
        return $this->response = $this->patch($uri, $data);
    }
    
    public function checkViewIs(string $view): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('Belum ada response untuk diperiksa.');
        }

        $this->response->assertViewIs($view);
    }

    public function assertPageIs(string $uri): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('Belum ada response untuk diperiksa.');
        }

        $this->response->assertRedirect($uri);
    }

    public function assertDatabaseHasRecord(string $table, array $data): void
    {
        $this->app->make('db')->table($table)->where($data)->first()
            ?? throw new \RuntimeException("Data tidak ditemukan di tabel {$table}.");
    }

    public function assertSessionHasErrorsFor(string $key): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('Belum ada response untuk diperiksa.');
        }

        $this->response->assertSessionHasErrors($key);
    }

    public function loginAsUser(User $user): void
    {
        $guard = $this->app->make('auth')->guard('web');

        $guard->setUser($user);

        $this->app->make('auth')->shouldUse('web');
    }

    public function makeUser(array $data): User
    {
        return User::create($data);
    }

    public function currentUser()
    {
        return auth()->user();
    }

    public function putPage(string $uri, array $data = []): TestResponse
    {
        return $this->response = $this->put($uri, $data);
    }

    public function assertResponseRedirectedToRoute(string $route): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('Belum ada response untuk diperiksa.');
        }

        $this->response->assertRedirect(route($route));
    }

    public function assertResponseOk(): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('Belum ada response untuk diperiksa.');
        }

        $this->response->assertOk();
    }

    public function assertPasswordHashed(string $password): void
    {
        $user = User::findOrFail($this->currentUser()->id);

        if (!Hash::check($password, $user->password)) {
            throw new \RuntimeException('Password baru tidak tersimpan dengan hash yang sesuai.');
        }
    }

    public function debugResponse(): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('Belum ada response untuk diperiksa.');
        }

        dump([
            'status' => $this->response->getStatusCode(),
            'url' => $this->response->headers->get('Location'),
            'content' => substr($this->response->getContent(), 0, 1000),
        ]);
    }

    public function assertResponseNotFound(): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('Belum ada response untuk diperiksa.');
        }

        $this->response->assertNotFound();
    }

    public function assertResponseContains(string $text): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('Belum ada response untuk diperiksa.');
        }

        $this->response->assertSee($text);
    }

    public function assertResponseRedirectedTo(string $uri): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('Belum ada response untuk diperiksa.');
        }

        $this->response->assertRedirect($uri);
    }

    public function assertResponseHasStatus(int $status): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('Belum ada response untuk diperiksa.');
        }

        $this->response->assertStatus($status);
    }

    public function deletePage(string $uri): TestResponse
    {
        return $this->response = $this->delete($uri);
    }

    public function bddLastResponse(): \Illuminate\Testing\TestResponse
    {
        if (!($this->response instanceof \Illuminate\Testing\TestResponse)) {
            throw new \RuntimeException(
                'Response Laravel belum tersedia.'
            );
        }

        return $this->response;
    }
}