<?php

namespace App\Support;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;

class TicketPurchaseDatabase
{
    public function assertMasterConfiguration(): void
    {
        $name = app()->environment('testing') ? 'simticketingkrm_purchase_test' : 'simticketingkrm';
        $config = config('database.connections.pgsql', []);
        if (! app()->environment(['local', 'testing'])
            || app()->configurationIsCached() || app()->routesAreCached()
            || config('database.default') !== 'pgsql' || ($config['driver'] ?? null) !== 'pgsql'
            || ($config['host'] ?? null) !== '127.0.0.1' || (string) ($config['port'] ?? '') !== '5433'
            || ($config['database'] ?? null) !== $name || ! empty($config['url'])
            || ! empty($config['read']) || ! empty($config['write']) || ! empty($config['prefix'])) {
            throw new RuntimeException('Pembelian master hanya menerima PostgreSQL lokal yang terverifikasi.');
        }
    }

    public function connection(): Connection
    {
        $this->assertMasterConfiguration();
        $connection = DB::connection('pgsql');
        foreach (['driver', 'host', 'database'] as $key) {
            if ($connection->getConfig($key) !== config('database.connections.pgsql.'.$key)) {
                throw new RuntimeException('Koneksi aktif tidak sesuai dengan konfigurasi PostgreSQL lokal.');
            }
        }
        if ((string) $connection->getConfig('port') !== '5433'
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
}
