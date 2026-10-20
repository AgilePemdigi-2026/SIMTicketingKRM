<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Halaman publik: tamu, wisatawan, admin, dan petugas dapat melihat Home.
     * Data mock sementara sampai migration/model Master Data Dana siap.
     * Pertahankan nama key view-model saat mengganti sumbernya ke Eloquent.
     */
    public function index()
    {
        $wahana = [
            [
                'slug' => 'kebun-raya-mangrove',
                'nama' => 'Kebun Raya Mangrove',
                'deskripsi' => 'Destinasi singkat mengenal keindahan mangrove dan pengalaman yang ditawarkan.',
                'gambar' => 'images/home/boardwalk.jpg',
            ],
            [
                'slug' => 'sepeda-air',
                'nama' => 'Sepeda Air',
                'deskripsi' => 'Nikmati suasana danau dan hijaunya kawasan mangrove dari sepeda air.',
                'gambar' => 'images/home/water.jpg',
            ],
            [
                'slug' => 'atv',
                'nama' => 'ATV',
                'deskripsi' => 'Petualangan singkat melintasi jalur alam dengan wahana ATV.',
                'gambar' => 'images/home/atv.jpg',
            ],
            [
                'slug' => 'mobil-golf',
                'nama' => 'Mobil Golf',
                'deskripsi' => 'Jelajahi kawasan dengan nyaman menggunakan mobil golf.',
                'gambar' => 'images/home/boardwalk.jpg',
            ],
            [
                'slug' => 'perahu-air',
                'nama' => 'Perahu Air',
                'deskripsi' => 'Susuri perairan mangrove dan nikmati ekosistem pesisir lebih dekat.',
                'gambar' => 'images/home/river.jpg',
            ],
            [
                'slug' => 'sepeda-listrik',
                'nama' => 'Sepeda Listrik',
                'deskripsi' => 'Berkeliling kawasan hijau dengan sepeda listrik yang ramah lingkungan.',
                'gambar' => 'images/home/atv.jpg',
            ],
        ];

        $fasilitas = [
            ['nama' => 'Indoor / Auditorium', 'kapasitas' => 'Kapasitas 200 orang', 'gambar' => 'images/home/boardwalk.jpg'],
            ['nama' => 'Area Outdoor', 'kapasitas' => 'Kapasitas 500 orang', 'gambar' => 'images/home/atv.jpg'],
            ['nama' => 'Pujasera / Food Court', 'kapasitas' => '30 kios', 'gambar' => 'images/home/water.jpg'],
            ['nama' => 'Area Reklame', 'kapasitas' => 'Media promosi di area publik', 'gambar' => 'images/home/river.jpg'],
            ['nama' => 'Area Pre-wedding', 'kapasitas' => 'Sesuai kebutuhan', 'gambar' => 'images/home/atv.jpg'],
        ];

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
