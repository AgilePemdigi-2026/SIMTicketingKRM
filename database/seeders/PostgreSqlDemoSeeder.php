<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PostgreSqlDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (
            ! app()->environment('local') ||
            DB::connection()->getDriverName() !== 'pgsql' ||
            ! in_array(
                DB::connection()->getConfig('host'),
                ['127.0.0.1', 'localhost'],
                true
            )
        ) {
            throw new RuntimeException(
                'Seeder hanya boleh dijalankan pada PostgreSQL lokal.'
            );
        }

        $tables = [
            'wahana',
            'daftar_tiket',
            'fasilitas',
            'tiket_wahana',
            'sesi_fasilitas',
        ];

        foreach ($tables as $table) {
            if (DB::table($table)->exists()) {
                $this->command?->warn(
                    "Seeder dilewati: tabel {$table} sudah berisi data."
                );
                return;
            }
        }

        DB::transaction(function () {

            // MASTER WAHANA
            DB::table('wahana')->insert([
                'id_wahana' => 2,
                'nama_wahana' => 'Roller Coster',
                'kapasitas' => 20,
                'status_aktif' => 1,
                'deskripsi' => 'Wahana Uji Coba',
                'foto' => 'wahana/YU7JmnHoMb9csXJ6fNPILSIkgwGHiYqWtVqbV25h.png',
                'durasi' => 600,
                'jam_buka' => '10:00:00',
                'jam_tutup' => '17:00:00',
            ]);

            // MASTER TIKET
            DB::table('daftar_tiket')->insert([
                [
                    'id_tiket' => 1,
                    'id_wahana' => 2,
                    'nama_tiket' => 'Tiket Reguler',
                    'kategori_tiket' => 'Reguler',
                    'kuota' => 100,
                    'kategori_wisatawan' => 'Domestik',
                    'harga_dewasa' => 50000,
                    'harga_anak' => 25000,
                    'status_aktif' => 1,
                    'ketentuan' => null,
                ],
                [
                    'id_tiket' => 5,
                    'id_wahana' => null,
                    'nama_tiket' => 'Tiket Masuk Saja',
                    'kategori_tiket' => 'Tiket Masuk',
                    'kuota' => 10,
                    'kategori_wisatawan' => 'Domestik (WNI)',
                    'harga_dewasa' => 20000,
                    'harga_anak' => 10000,
                    'status_aktif' => 1,
                    'ketentuan' => 'Testing',
                ],
                [
                    'id_tiket' => 4,
                    'id_wahana' => 2,
                    'nama_tiket' => 'Tiket Terusan 1',
                    'kategori_tiket' => 'Tiket Masuk',
                    'kuota' => 10,
                    'kategori_wisatawan' => 'Domestik (WNI)',
                    'harga_dewasa' => 15000,
                    'harga_anak' => 10000,
                    'status_aktif' => 1,
                    'ketentuan' => null,
                ],
            ]);

            // MASTER FASILITAS
            DB::table('fasilitas')->insert([
                [
                    'id_fasilitas' => 1,
                    'nama_fasilitas' => 'Gazebo Uji Coba',
                    'kapasitas_fasilitas' => 10,
                    'status_fasilitas' => 1,
                    'ketentuan_fasilitas' => null,
                    'deskripsi_fasilitas' => null,
                    'foto_fasilitas' => null,
                ],
                [
                    'id_fasilitas' => 2,
                    'nama_fasilitas' => 'Kolam Renang',
                    'kapasitas_fasilitas' => 50,
                    'status_fasilitas' => 1,
                    'ketentuan_fasilitas' => 'Apa',
                    'deskripsi_fasilitas' => 'Tes',
                    'foto_fasilitas' => 'fasilitas/lvD7n9Ms0DtCIEwjfKRj2fupDbTxuqOvYAjOJPN3.png',
                ],
            ]);

            // RELASI TIKET DAN WAHANA
            DB::table('tiket_wahana')->insert([
                'id_tiket_wahana' => 3,
                'id_tiket' => 4,
                'id_wahana' => 2,
            ]);

            // SESI FASILITAS
            DB::table('sesi_fasilitas')->insert([
                [
                    'id_sesi' => 1,
                    'id_fasilitas' => 1,
                    'nama_sesi' => 'Pagi',
                    'jam_mulai' => '08:00:00',
                    'jam_selesai' => '10:00:00',
                    'durasi_menit' => 120,
                    'harga_sesi' => 50000,
                    'status_aktif' => 1,
                ],
                [
                    'id_sesi' => 2,
                    'id_fasilitas' => 2,
                    'nama_sesi' => 'Pagi',
                    'jam_mulai' => '10:00:00',
                    'jam_selesai' => '11:00:00',
                    'durasi_menit' => 60,
                    'harga_sesi' => 10000,
                    'status_aktif' => 1,
                ],
            ]);

            // Sesuaikan sequence PostgreSQL setelah insert ID manual
            $sequences = [
                'wahana' => 'id_wahana',
                'daftar_tiket' => 'id_tiket',
                'fasilitas' => 'id_fasilitas',
                'tiket_wahana' => 'id_tiket_wahana',
                'sesi_fasilitas' => 'id_sesi',
            ];

            foreach ($sequences as $table => $id) {
                DB::select(
                    "SELECT setval(
                        pg_get_serial_sequence('{$table}', '{$id}'),
                        (SELECT MAX({$id}) FROM {$table}),
                        true
                    )"
                );
            }
        });

        $this->command?->info(
            'Seeder PostgreSQL berhasil: 9 record master data dan relasi.'
        );
    }
}
