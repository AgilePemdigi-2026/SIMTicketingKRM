<?php

namespace Tests;

use App\Support\TicketPurchaseDatabase;
use Database\Seeders\PostgreSqlDemoSeeder;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

class BehatPostgresLaravelTestCase extends BehatLaravelTestCase
{
    private bool $transactionStarted = false;

    public function startTestEnvironment(): void
    {
        try {
        $this->app = require Application::inferBasePath() . '/bootstrap/app.php';

        if ($this->app->configurationIsCached()) {
            throw new \RuntimeException(
                'BDD PostgreSQL menolak cache konfigurasi.'
            );
        }

        $this->app->afterBootstrapping(
            \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
            function ($app): void {
                $file = $app->basePath('.env.postgres.local');

                if (! is_file($file)) {
                    throw new \RuntimeException(
                        'File .env.postgres.local tidak ditemukan.'
                    );
                }

                $env = Dotenv::parse(file_get_contents($file));

                if (
                    ($env['DB_CONNECTION'] ?? '') !== 'pgsql'
                    || ($env['DB_HOST'] ?? '') !== '127.0.0.1'
                    || (string) ($env['DB_PORT'] ?? '') !== '5433'
                    || ($env['DB_DATABASE'] ?? '') !== 'simticketingkrm_purchase_test'
                    || ! empty($env['DB_URL'])
                ) {
                    throw new \RuntimeException(
                        'Environment BDD harus mengarah ke PostgreSQL test lokal.'
                    );
                }

                $app->instance('env', 'testing');

                $app['config']->set([
                    'app.env' => 'testing',
                    'app.key' => 'base64:' . base64_encode(random_bytes(32)),
                    'database.default' => 'pgsql',
                    'database.connections' => [
                        'pgsql' => [
                            'driver' => 'pgsql',
                            'host' => '127.0.0.1',
                            'port' => '5433',
                            'database' => 'simticketingkrm_purchase_test',
                            'username' => $env['DB_USERNAME'],
                            'password' => $env['DB_PASSWORD'],
                            'url' => null,
                            'prefix' => '',
                            'charset' => 'utf8',
                            'search_path' => 'public',
                            'sslmode' => 'disable',
                        ],
                    ],
                    'ticket_purchase.master_enabled' => true,
                    'session.driver' => 'array',
                    'session.connection' => null,
                    'cache.default' => 'array',
                    'queue.default' => 'sync',
                    'logging.default' => 'null',
                    'hashing.bcrypt.rounds' => 4,
                ]);
            }
        );

        $kernel = $this->app->make(Kernel::class);
        $kernel->bootstrap();

        $this->app->boot();

        // Verifikasi koneksi sebelum migration atau penulisan data.
        $connection = $this->app->make(TicketPurchaseDatabase::class)->connection();

        if ($connection->getDatabaseName() !== 'simticketingkrm_purchase_test') {
            throw new \RuntimeException(
                'BDD PostgreSQL bukan simticketingkrm_purchase_test.'
            );
        }

        // Migration incremental saja, tidak menggunakan migrate:fresh.
        $exitCode = $kernel->call('migrate', [
            '--database' => 'pgsql',
            '--no-interaction' => true,
        ]);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Migration PostgreSQL test gagal.');
        }

        $connection->beginTransaction();
        $this->transactionStarted = true;

        // Seeder yang sama dengan integration test.
        $environment = $this->app->environment();

        try {
            $this->app->instance('env', 'local');
            $this->app->make(PostgreSqlDemoSeeder::class)->run();
        } finally {
            $this->app->instance('env', $environment);
        }

        $this->app->instance('app', $this->app);
        $this->app->instance(
            'Illuminate\Contracts\Http\Kernel',
            $this->app->make('Illuminate\Contracts\Http\Kernel')
        );

        $this->app->make('router');
        $this->app->make('session')->driver()->start();
        } catch (\Throwable $e) {
        fwrite(STDERR, "\nPOSTGRES BEHAT SETUP ERROR:\n");
        fwrite(STDERR, get_class($e) . "\n");
        fwrite(STDERR, $e->getMessage() . "\n");
        fwrite(STDERR, $e->getTraceAsString() . "\n");

        throw $e;
    }
    }

    public function stopTestEnvironment(): void
    {
        if (isset($this->app)) {
            if ($this->transactionStarted) {
                $connection = $this->app->make('db')->connection('pgsql');

                while ($connection->transactionLevel() > 0) {
                    $connection->rollBack();
                }

                $this->transactionStarted = false;
            }

            $this->app->make('db')->disconnect();
            $this->app->flush();
        }
    }
}