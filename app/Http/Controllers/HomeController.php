<?php

namespace App\Http\Controllers;

use App\Models\Wahana;
use App\Models\Fasilitas;

class HomeController extends Controller
{
    /**
     * Halaman publik: tamu, wisatawan, admin, dan petugas dapat melihat Home.
     * Data mock sementara sampai migration/model Master Data Dana siap.
     * Pertahankan nama key view-model saat mengganti sumbernya ke Eloquent.
     */
    public function index()
    {
        $wahana = Wahana::query()
            ->where('status_aktif', 1)
            ->orderBy('id_wahana')
            ->get()
            ->map(function (Wahana $item) {
                $foto = $item->foto;

                $gambar = $foto && is_file(public_path('storage/' . $foto))
                    ? 'storage/' . $foto
                    : 'images/home/boardwalk.jpg';

                return [
                    'slug' => (string) $item->id_wahana,
                    'nama' => $item->nama_wahana,
                    'deskripsi' => $item->deskripsi
                        ?: 'Deskripsi wahana belum tersedia.',
                    'gambar' => $gambar,
                ];
            })
            ->all();

        $fasilitas = Fasilitas::query()
            ->where('status_fasilitas', 1)
            ->orderBy('id_fasilitas')
            ->get()
            ->map(function (Fasilitas $item) {
                $foto = $item->foto_fasilitas;

                $gambar = $foto && is_file(public_path('storage/' . $foto))
                    ? 'storage/' . $foto
                    : 'images/home/boardwalk.jpg';

                return [
                    'nama' => $item->nama_fasilitas,
                    'kapasitas' => 'Kapasitas ' . $item->kapasitas_fasilitas . ' orang',
                    'gambar' => $gambar,
                ];
            })
            ->all();

        $galeri = [
            'images/home/boardwalk.jpg',
            'images/home/atv.jpg',
            'images/home/river.jpg',
            'images/home/water.jpg',
            'images/home/atv.jpg',
            'images/home/boardwalk.jpg',
            'images/home/water.jpg',
            'images/home/river.jpg',
        ];

        // Judul, kategori, dan tanggal di bawah ini sekadar contoh dari mockup,
        // bukan publikasi berita yang telah diverifikasi.
        $berita = [
            [
                'judul' => 'Festival Mangrove 2026 Resmi Dibuka oleh Wali Kota Surabaya',
                'kategori' => 'Event',
                'tanggal' => '28 Agu 2026',
                'ringkasan' => 'Informasi terbaru seputar Kebun Raya Mangrove Surabaya dan kegiatan konservasi.',
                'gambar' => 'images/home/boardwalk.jpg',
            ],
            [
                'judul' => 'Cara Menjaga dan Melestarikan Ekosistem Mangrove Surabaya',
                'kategori' => 'Edukasi',
                'tanggal' => '28 Agu 2026',
                'ringkasan' => 'Informasi terbaru seputar Kebun Raya Mangrove Surabaya dan kegiatan konservasi.',
                'gambar' => 'images/home/atv.jpg',
            ],
            [
                'judul' => 'Wahana Baru: Sepeda Listrik Kini Tersedia di KRM Surabaya',
                'kategori' => 'Info',
                'tanggal' => '28 Agu 2026',
                'ringkasan' => 'Informasi terbaru seputar Kebun Raya Mangrove Surabaya dan kegiatan konservasi.',
                'gambar' => 'images/home/water.jpg',
            ],
        ];

        return view('public.home', compact('wahana', 'fasilitas', 'galeri', 'berita'));
    }
}