<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class PostgreSqlRoleSeeder extends Seeder
{
    public function run(): void
    {
        $connection = DB::connection();

        // Hanya untuk PostgreSQL lokal
        if (
            ! app()->environment('local') ||
            $connection->getDriverName() !== 'pgsql' ||
            ! in_array(
                $connection->getConfig('host'),
                ['127.0.0.1', 'localhost'],
                true
            )
        ) {
            throw new RuntimeException(
                'Seeder akun hanya boleh dijalankan pada PostgreSQL lokal.'
            );
        }

        // Akun testing yang sudah disepakati kelompok
        $accounts = [
            [
                'name' => 'Admin Testing',
                'email' => 'admin.testing@example.com',
                'role' => 'admin',
                'identity_number' => 'DEMO-ADMIN-001',
                'phone' => '080000000001',
                'password_env' => 'PG_DEMO_ADMIN_PASSWORD',
            ],
            [
                'name' => 'Wisatawan Testing',
                'email' => 'tester.wisatawan@example.com',
                'role' => 'wisatawan',
                'identity_number' => 'DEMO-WISATAWAN-001',
                'phone' => '080000000002',
                'password_env' => 'PG_DEMO_WISATAWAN_PASSWORD',
            ],
            [
                'name' => 'Petugas Testing',
                'email' => 'petugas.testing@example.com',
                'role' => 'petugas',
                'identity_number' => 'DEMO-PETUGAS-001',
                'phone' => '080000000003',
                'password_env' => 'PG_DEMO_PETUGAS_PASSWORD',
            ],
        ];

        // Baca password dari .env, bukan dari source code
        $passwords = [];

        foreach ($accounts as $account) {
            $password = env($account['password_env']);

            if (
                ! is_string($password) ||
                $password === ''
            ) {
                throw new RuntimeException(
                    'Variabel ' . $account['password_env'] .
                    ' wajib diisi di file .env.'
                );
            }

            $passwords[$account['email']] = $password;
        }

        $emails = array_column($accounts, 'email');

        // Periksa apakah akun demo sudah ada
        $existing = DB::table('users')
            ->whereIn('email', $emails)
            ->get(['email', 'role', 'password'])
            ->keyBy('email');

        // Jika ketiga akun sudah ada, validasi dan lewati
        if ($existing->count() === count($accounts)) {
            foreach ($accounts as $account) {
                $user = $existing->get($account['email']);

                if (
                    $user->role !== $account['role'] ||
                    ! Hash::check(
                        $passwords[$account['email']],
                        $user->password
                    )
                ) {
                    throw new RuntimeException(
                        'Akun demo sudah ada, tetapi role atau ' .
                        'password tidak sesuai. Seeder dibatalkan.'
                    );
                }
            }

            $this->command?->warn(
                'Ketiga akun demo sudah tersedia. Seeder dilewati.'
            );

            return;
        }

        // Jangan mengubah atau mencampur data pengguna yang ada
        if (
            $existing->isNotEmpty() ||
            DB::table('users')->exists()
        ) {
            throw new RuntimeException(
                'Tabel users sudah berisi data. ' .
                'Gunakan database pengujian dengan users kosong.'
            );
        }

        // Buat akun dalam satu transaksi
        DB::transaction(function () use ($accounts, $passwords) {
            foreach ($accounts as $account) {
                DB::table('users')->insert([
                    'name' => $account['name'],
                    'email' => $account['email'],
                    'role' => $account['role'],
                    'identity_number' => $account['identity_number'],
                    'phone' => $account['phone'],
                    'visitor_type' => 'domestik',
                    'password' => Hash::make(
                        $passwords[$account['email']]
                    ),
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $this->command?->info(
            'Berhasil membuat 3 akun testing: ' .
            'Admin, Wisatawan, dan Petugas.'
        );
    }
}
