<?php

namespace App\Console\Commands;

use App\Support\LocalTicketDatabase;
use Database\Seeders\LocalTicketSeeder;
use Illuminate\Console\Command;
use Throwable;

class SetupLocalTickets extends Command
{
    protected $signature = 'ticket:local-setup';

    protected $description = 'Siapkan database SQLite demo tiket yang terisolasi (tanpa menghapus data).';

    public function handle(LocalTicketDatabase $guard): int
    {
        try {
            $guard->assertConfiguration(allowMissingFile: true);
            // Missing secrets must fail before files, migrations, or rows are created.
            $guard->passwords();
            if (! app()->environment('ticket-local')) {
                $this->error('Setup demo hanya boleh dijalankan dengan --env=ticket-local.');

                return self::FAILURE;
            }

            $path = database_path('simticketingkrm_local.sqlite');
            if (! file_exists($path)) {
                $file = fopen($path, 'x');
                if ($file === false) {
                    throw new \RuntimeException('File SQLite demo tidak dapat dibuat.');
                }
                fclose($file);
                chmod($path, 0600);
            }
            $connection = $guard->connection();
            $this->info('Target terverifikasi: SQLite '.$path);

            // Never adopt existing unmanaged tables and guess their schema.
            $tables = $connection->getSchemaBuilder()->getTableListing();
            if ($tables !== [] && ! in_array('migrations', $tables, true)) {
                throw new \RuntimeException('Database berisi tabel tanpa riwayat migration. Setup dihentikan.');
            }

            foreach (['database/migrations', 'database/local/migrations'] as $migrationPath) {
                $guard->connection();
                $status = $this->call('migrate', [
                    '--database' => 'sqlite', '--path' => [$migrationPath], '--force' => true,
                ]);
                if ($status !== self::SUCCESS) {
                    return self::FAILURE;
                }
            }

            $guard->connection();
            app(LocalTicketSeeder::class)->run();
            foreach (['sessions', 'cache', 'locks'] as $directory) {
                $path = storage_path('framework/ticket-local/'.$directory);
                if (! is_dir($path)) {
                    mkdir($path, 0700, true);
                }
            }
            $this->info('Demo siap: 3 akun, 3 tiket dummy, dan 6 tarif. Data transaksi existing dipertahankan.');

            return self::SUCCESS;
        } catch (Throwable) {
            // Do not echo SQL exceptions: bindings may contain credentials or hashes.
            $this->error('Setup ditolak/gagal. Periksa environment ticket-local, password lokal, cache, target SQLite, dan schema existing. Tidak ada credential yang ditampilkan.');

            return self::FAILURE;
        }
    }
}
