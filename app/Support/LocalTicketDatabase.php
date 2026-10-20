<?php

namespace App\Support;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;

/**
 * Fail closed before any connection or write; never accepts a PostgreSQL target.
 */
class LocalTicketDatabase
{
    public function assertConfiguration(bool $allowMissingFile = false): void
    {
        if (! config('ticket_purchase.local_enabled')
            || ! app()->environment(['ticket-local', 'testing'])
            || app()->configurationIsCached()
            || app()->routesAreCached()
            || is_file(app()->getCachedConfigPath())
            || is_file(app()->getCachedRoutesPath())) {
            throw new RuntimeException('Demo tiket membutuhkan environment lokal/testing tanpa cache konfigurasi atau route.');
        }

        $config = config('database.connections.sqlite', []);
        if (config('database.default') !== 'sqlite'
            || ($config['driver'] ?? null) !== 'sqlite'
            || ! empty($config['url'])
            || ! empty($config['read'])
            || ! empty($config['write'])
            || ! empty($config['prefix'])
            || ! ($config['foreign_key_constraints'] ?? false)) {
            throw new RuntimeException('Target demo wajib SQLite tanpa connection URL, read/write override, atau prefix, dengan foreign key aktif.');
        }

        $database = $config['database'] ?? null;
        if (app()->environment('testing')) {
            if ($database !== ':memory:') {
                throw new RuntimeException('Pengujian tiket hanya boleh menggunakan SQLite :memory:.');
            }

            return;
        }

        if ($database !== database_path('simticketingkrm_local.sqlite')) {
            throw new RuntimeException('Path database tidak sesuai dengan file SQLite demo.');
        }

        // Reject symlinks anywhere in the path, including parent directories.
        for ($path = $database; $path !== dirname($path); $path = dirname($path)) {
            if (is_link($path)) {
                throw new RuntimeException('Path database demo tidak boleh menggunakan symlink.');
            }
        }

        if (file_exists($database)) {
            if (! is_file($database) || realpath($database) !== $database) {
                throw new RuntimeException('Target database demo bukan file lokal yang sesuai.');
            }
        } elseif (! $allowMissingFile) {
            throw new RuntimeException('Database demo belum tersedia. Jalankan ticket:local-setup pada environment ticket-local.');
        }
    }

    public function connection(): Connection
    {
        $this->assertConfiguration();
        $connection = DB::connection('sqlite');
        if ($connection->getConfig('driver') !== 'sqlite'
            || $connection->getConfig('database') !== config('database.connections.sqlite.database')
            || ! empty($connection->getConfig('url'))
            || ! empty($connection->getConfig('read'))
            || ! empty($connection->getConfig('write'))) {
            throw new RuntimeException('Koneksi aktif berbeda dari target SQLite yang diverifikasi.');
        }

        $pdo = $connection->getPdo();
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
            throw new RuntimeException('Driver PDO aktif bukan SQLite.');
        }

        $databases = $pdo->query('PRAGMA database_list')->fetchAll(PDO::FETCH_ASSOC);
        $expected = app()->environment('testing') ? '' : database_path('simticketingkrm_local.sqlite');
        foreach ($databases as $database) {
            if ($database['name'] !== 'temp' && ($database['name'] !== 'main' || $database['file'] !== $expected)) {
                throw new RuntimeException('Database SQLite aktif atau attachment tidak sesuai target.');
            }
        }

        if ((int) $pdo->query('PRAGMA foreign_keys')->fetchColumn() !== 1) {
            throw new RuntimeException('Foreign key SQLite harus aktif.');
        }

        return $connection;
    }

    public function passwords(): array
    {
        $passwords = config('ticket_purchase.passwords', []);
        foreach (['admin', 'wisatawan', 'petugas'] as $role) {
            if (! is_string($passwords[$role] ?? null) || strlen($passwords[$role]) < 8) {
                throw new RuntimeException('Isi LOCAL_ADMIN_PASSWORD, LOCAL_WISATAWAN_PASSWORD, dan LOCAL_PETUGAS_PASSWORD (minimal 8 karakter) di environment lokal.');
            }
        }

        return $passwords;
    }
}
