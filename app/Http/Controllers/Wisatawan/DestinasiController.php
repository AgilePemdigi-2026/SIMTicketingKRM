<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use App\Models\DaftarTiket;
use App\Models\Konten;
use Illuminate\Support\Facades\Storage;
use App\Models\InfoDestinasi;

class DestinasiController extends Controller
{
    /**
     * Halaman destinasi wisatawan (dilindungi middleware auth + role di routes/web.php).
     *
     * Data di bawah merupakan placeholder berdasarkan mockup kelompok.
     * Ganti sumber data setelah tabel master Dana siap dan struktur kolom disepakati.
     */
    public function index(): View
    {   
        // Mengambil profil destinasi dari PostgreSQL.
        $profil = InfoDestinasi::query()
            ->orderBy('id_destinasi')
            ->first();

        // Informasi tambahan berdasarkan data dari Admin.
        $informasi = [];

        if ($profil?->jam_buka && $profil?->jam_tutup) {
            $informasi[] = [
                'judul' => 'Jam Operasional',
                'isi' => substr((string) $profil->jam_buka, 0, 5)
                    . ' - '
                    . substr((string) $profil->jam_tutup, 0, 5)
                    . ' WIB',
            ];
        }

        if ($profil?->alamat) {
            $informasi[] = [
                'judul' => 'Alamat',
                'isi' => $profil->alamat,
            ];
        }

        // Rincian harga tetap diambil dari Master Data Tiket.
        $informasi[] = [
            'judul' => 'Informasi Tiket',
            'isi' => 'Lihat rincian jenis tiket dan harga pada panel Harga Tiket.',
        ];

        // Format array dipertahankan agar cocok dengan Blade.
        $destinasi = [
            'nama' => $profil?->nama_destinasi
                ?: 'Kebun Raya Mangrove Surabaya',

            'gambar' => $profil?->foto_utama
                && Storage::disk('public')->exists($profil->foto_utama)
                    ? 'storage/' . $profil->foto_utama
                    : 'images/destinasi/boardwalk-hero.jpg',

            'deskripsi' => $profil?->deskripsi_lengkap
                ?: ($profil?->deskripsi_singkat
                    ?: 'Informasi destinasi belum tersedia.'),

            'informasi' => $informasi,
        ];

        $hargaTiket = DaftarTiket::query()
            ->where('status_aktif', 1)
            ->orderBy('id_tiket')
            ->get()
            ->flatMap(function (DaftarTiket $tiket) {
                return [
                    [
                        'label' => $tiket->nama_tiket
                            . ' - Dewasa (' . $tiket->kategori_wisatawan . ')',
                        'harga' => 'Rp ' . number_format(
                            (float) $tiket->harga_dewasa, 0, ',', '.'
                        ),
                    ],
                    [
                        'label' => $tiket->nama_tiket
                            . ' - Anak (' . $tiket->kategori_wisatawan . ')',
                        'harga' => 'Rp ' . number_format(
                            (float) $tiket->harga_anak, 0, ',', '.'
                        ),
                    ],
                ];
            })
            ->all();


        // Galeri destinasi dari database.
        // Hanya foto berstatus Published yang ditampilkan.
        $galeri = Konten::query()
            ->where('jenis_konten', 'galeri')
            ->where('status_publikasi', 'published')
            ->whereNotNull('gambar')
            ->whereNotNull('waktu_publikasi')
            ->where('waktu_publikasi', '<=', now())
            ->orderByDesc('waktu_publikasi')
            ->orderByDesc('id_konten')
            ->get()
            ->filter(function (Konten $item) {
                return Storage::disk('public')
                    ->exists($item->gambar);
            })
            ->map(function (Konten $item) {
                return [
                    'gambar' => 'storage/' . $item->gambar,
                    'alt' => $item->judul
                        ?: 'Foto galeri Kebun Raya Mangrove Surabaya',
                ];
            })
            ->values()
            ->all();

        return view('wisatawan.destinasi', compact('destinasi', 'hargaTiket', 'galeri'));
    }
}