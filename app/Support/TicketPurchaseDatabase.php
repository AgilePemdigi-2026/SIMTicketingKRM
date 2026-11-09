<?php

namespace App\Support;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;

class TicketPurchaseDatabase
{
    public function supportsEnvironment(): bool
    {
        return app()->environment(['local', 'testing']) || $this->usesComposeDatabase();
    }

    public function assertMasterConfiguration(): void
    {
        [$host, $port, $name] = match (true) {
            $this->usesComposeDatabase() => ['postgres', '5432', 'mangrotix'],
            app()->environment('testing') => ['127.0.0.1', '5433', 'simticketingkrm_purchase_test'],
            default => ['127.0.0.1', '5433', 'simticketingkrm'],
        };
        $config = config('database.connections.pgsql', []);
        if (! $this->supportsEnvironment()
            || app()->configurationIsCached() || app()->routesAreCached()
            || config('database.default') !== 'pgsql' || ($config['driver'] ?? null) !== 'pgsql'
            || ($config['host'] ?? null) !== $host || (string) ($config['port'] ?? '') !== $port
            || ($config['database'] ?? null) !== $name || ! empty($config['url'])
            || ! empty($config['read']) || ! empty($config['write']) || ! empty($config['prefix'])) {
            throw new RuntimeException('Pembelian master hanya menerima konfigurasi PostgreSQL yang terverifikasi.');
        }
    }

    public function connection(): Connection
    {
        $this->assertMasterConfiguration();
        $connection = DB::connection('pgsql');
        foreach (['driver', 'host', 'database'] as $key) {
            if ($connection->getConfig($key) !== config('database.connections.pgsql.'.$key)) {
                throw new RuntimeException('Koneksi aktif tidak sesuai dengan konfigurasi PostgreSQL.');
            }
        }
        if ((string) $connection->getConfig('port') !== (string) config('database.connections.pgsql.port')
            || ! empty($connection->getConfig('url')) || ! empty($connection->getConfig('read'))
            || ! empty($connection->getConfig('write')) || ! empty($connection->getConfig('prefix'))) {
            throw new RuntimeException('Koneksi aktif memiliki pengalihan atau port yang tidak sesuai.');
        }
        if ($connection->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql') {
            throw new RuntimeException('Driver PDO pembelian tidak sesuai.');
        }
        $identity = $connection->selectOne('SELECT current_database() AS name, current_setting(\'server_version_num\') AS version');
        if ($identity->name !== config('database.connections.pgsql.database') || (int) $identity->version < 170000) {
            throw new RuntimeException('Identitas database PostgreSQL pembelian tidak sesuai.');
        }

        return $connection;
    }

    private function usesComposeDatabase(): bool
    {
        return app()->environment('production') && config('ticket_purchase.compose_database_enabled') === true;
    }
}
