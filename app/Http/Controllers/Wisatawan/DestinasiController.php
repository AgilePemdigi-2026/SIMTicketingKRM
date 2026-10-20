<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

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
        $destinasi = [
            'nama' => 'Kebun Raya Mangrove Surabaya',
            'gambar' => 'images/destinasi/boardwalk-hero.jpg',
            'deskripsi' => 'Kebun Raya Mangrove Surabaya adalah kawasan konservasi mangrove yang menjadi salah satu destinasi wisata alam unggulan di Kota Surabaya. Terletak di tepi pantai timur Surabaya, kawasan ini menawarkan pengalaman wisata alam yang unik sekaligus edukasi mengenai ekosistem mangrove.',
            'informasi' => [
                [
                    'judul' => 'Sejarah',
                    'isi' => 'Kebun Raya Mangrove Surabaya didirikan pada tahun 1998 sebagai upaya pelestarian hutan mangrove di kawasan pesisir Surabaya.',
                ],
                [
                    'judul' => 'Fasilitas',
                    'isi' => 'Area parkir, toilet umum, musala, warung makan, pusat informasi, jalur boardwalk, menara pandang, dan area bermain anak.',
                ],
                [
                    'judul' => 'Jam Operasional',
                    'isi' => 'Setiap hari: 07.00 – 17.00 WIB',
                ],
                [
                    'judul' => 'Informasi Tiket',
                    'isi' => 'Tiket masuk: Rp 25.000 (Dewasa) / Rp 15.000 (Anak). Tersedia berbagai paket tiket terusan dengan wahana pilihan.',
                ],
            ],
        ];

        // Nilai harga mengikuti *mockup*, bukan pembacaan database ataupun tarif resmi.
        $hargaTiket = [
            ['label' => 'Dewasa Domestik', 'harga' => 'Rp 25.000'],
            ['label' => 'Anak Domestik', 'harga' => 'Rp 15.000'],
            ['label' => 'Dewasa Asing', 'harga' => 'Rp 50.000'],
        ];

        // Foto hasil cuplikan mockup. Ganti foto asli saat aset disediakan tim.
        $galeri = [
            ['gambar' => 'images/destinasi/boardwalk-hero.jpg', 'alt' => 'Jalur kayu di tengah hutan mangrove'],
            ['gambar' => 'images/destinasi/gunung-mangrove.jpg', 'alt' => 'Lanskap pepohonan dan kawasan hijau'],
            ['gambar' => 'images/destinasi/sungai-mangrove.jpg', 'alt' => 'Pepohonan mangrove di tepi perairan'],
            ['gambar' => 'images/destinasi/danau-mangrove.jpg', 'alt' => 'Danau dan pohon mangrove'],
            ['gambar' => 'images/destinasi/gunung-mangrove.jpg', 'alt' => 'Pemandangan hijau di kawasan wisata'],
            ['gambar' => 'images/destinasi/boardwalk-hero.jpg', 'alt' => 'Jembatan kayu di dalam kawasan mangrove'],
        ];

        return view('wisatawan.destinasi', compact('destinasi', 'hargaTiket', 'galeri'));
    }
}