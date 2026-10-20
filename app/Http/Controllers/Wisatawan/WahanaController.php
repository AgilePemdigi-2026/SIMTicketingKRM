<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WahanaController extends Controller
{
    /**
     * Data contoh sesuai Home dan mockup Detail Informasi Wahana.
     * Ini belum membaca tabel Master Data yang dikerjakan Dana.
     * Ketika tabel sudah siap, ganti array ini dengan query model yang disepakati.
     */
    public function show(string $slug): View|RedirectResponse
    {
        // Kartu pertama di Home adalah destinasi, bukan sebuah wahana.
        if ($slug === 'kebun-raya-mangrove') {
            return redirect()->route('wisatawan.destinasi');
        }

        $galeri = [
            [
                'gambar' => 'images/wahana/danau-mangrove.jpg',
                'alt' => 'Pemandangan danau di kawasan mangrove',
            ],
            [
                'gambar' => 'images/wahana/sungai-mangrove.jpg',
                'alt' => 'Aliran air dengan pepohonan mangrove',
            ],
            [
                'gambar' => 'images/wahana/gunung-mangrove.jpg',
                'alt' => 'Kawasan hijau di sekitar destinasi mangrove',
            ],
            [
                'gambar' => 'images/wahana/boardwalk-mangrove.jpg',
                'alt' => 'Jembatan kayu di tengah hutan mangrove',
            ],
        ];

        // Harga, syarat, dan jam Sepeda Air menyalin *mockup*, bukan tarif resmi.
        // Untuk wahana lain, jangan mengarang harga ataupun ketentuan yang belum diberikan Admin.
        $katalog = [
            'sepeda-air' => [
                'nama' => 'Sepeda Air',
                'deskripsi' => 'Nikmati pengalaman bersepeda air di atas danau yang dikelilingi pohon mangrove yang rindang. Cocok untuk keluarga dan wisatawan dari segala usia.',
                'gambar' => 'images/wahana/danau-mangrove.jpg',
                'harga' => 'Rp 20.000',
                'harga_satuan' => 'per orang / 30 menit',
                'harga_detail' => 'Rp 20.000 per orang / 30 menit',
                'ketentuan' => 'Minimal usia 5 tahun, wajib menggunakan jaket pelampung, dilarang berdiri di atas sepeda air, maksimal 2 orang per unit.',
                'jam_operasional' => '08.00 – 17.00 WIB (Setiap Hari)',
            ],
            'atv' => [
                'nama' => 'ATV',
                'deskripsi' => 'Petualangan singkat melintasi jalur alam dengan wahana ATV.',
                'gambar' => 'images/wahana/gunung-mangrove.jpg',
                'harga' => null,
                'harga_satuan' => null,
                'harga_detail' => 'Menunggu informasi harga dari pengelola.',
                'ketentuan' => 'Ketentuan penggunaan akan ditampilkan setelah data Admin tersedia.',
                'jam_operasional' => 'Menunggu informasi operasional wahana.',
            ],
            'mobil-golf' => [
                'nama' => 'Mobil Golf',
                'deskripsi' => 'Jelajahi kawasan dengan nyaman menggunakan mobil golf.',
                'gambar' => 'images/wahana/boardwalk-mangrove.jpg',
                'harga' => null,
                'harga_satuan' => null,
                'harga_detail' => 'Menunggu informasi harga dari pengelola.',
                'ketentuan' => 'Ketentuan penggunaan akan ditampilkan setelah data Admin tersedia.',
                'jam_operasional' => 'Menunggu informasi operasional wahana.',
            ],
            'perahu-air' => [
                'nama' => 'Perahu Air',
                'deskripsi' => 'Susuri perairan mangrove dan nikmati ekosistem pesisir lebih dekat.',
                'gambar' => 'images/wahana/sungai-mangrove.jpg',
                'harga' => null,
                'harga_satuan' => null,
                'harga_detail' => 'Menunggu informasi harga dari pengelola.',
                'ketentuan' => 'Ketentuan penggunaan akan ditampilkan setelah data Admin tersedia.',
                'jam_operasional' => 'Menunggu informasi operasional wahana.',
            ],
            'sepeda-listrik' => [
                'nama' => 'Sepeda Listrik',
                'deskripsi' => 'Berkeliling kawasan hijau dengan sepeda listrik yang ramah lingkungan.',
                'gambar' => 'images/wahana/boardwalk-mangrove.jpg',
                'harga' => null,
                'harga_satuan' => null,
                'harga_detail' => 'Menunggu informasi harga dari pengelola.',
                'ketentuan' => 'Ketentuan penggunaan akan ditampilkan setelah data Admin tersedia.',
                'jam_operasional' => 'Menunggu informasi operasional wahana.',
            ],
        ];

        abort_unless(isset($katalog[$slug]), 404);

        $wahana = $katalog[$slug];
        $wahana['galeri'] = $galeri;

        return view('wisatawan.detail-wahana', compact('wahana'));
    }
}