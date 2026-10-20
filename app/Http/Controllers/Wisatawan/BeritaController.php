<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class BeritaController extends Controller
{
    /**
     * DAFTAR BERITA DUMMY: judul/tanggal mengikuti mockup dan BUKAN berita resmi.
     * Ganti sumber data dengan model konten setelah skema database disepakati.
     */
    private function artikelContoh(): array
    {
        $ringkasan = 'Informasi terbaru seputar Kebun Raya Mangrove Surabaya dan kegiatan konservasi.';

        return [
            [
                'slug' => 'festival-mangrove-2026',
                'judul' => 'Festival Mangrove 2026 Resmi Dibuka oleh Wali Kota Surabaya',
                'kategori' => 'Event',
                'tanggal' => '28 Agu 2026',
                'ringkasan' => $ringkasan,
                'gambar' => 'images/berita/jembatan-mangrove.jpg',
            ],
            [
                'slug' => 'melestarikan-ekosistem-mangrove',
                'judul' => 'Cara Menjaga dan Melestarikan Ekosistem Mangrove Surabaya',
                'kategori' => 'Edukasi',
                'tanggal' => '28 Agu 2026',
                'ringkasan' => $ringkasan,
                'gambar' => 'images/berita/lanskap-mangrove.jpg',
            ],
            [
                'slug' => 'wahana-sepeda-listrik',
                'judul' => 'Wahana Baru: Sepeda Listrik Kini Tersedia di KRM Surabaya',
                'kategori' => 'Info',
                'tanggal' => '28 Agu 2026',
                'ringkasan' => $ringkasan,
                'gambar' => 'images/berita/danau-mangrove.jpg',
            ],
            [
                'slug' => 'tiket-gratis-anak-di-bawah-3-tahun',
                'judul' => 'Tiket Masuk Gratis untuk Anak di Bawah 3 Tahun',
                'kategori' => 'Info',
                'tanggal' => '01 Agu 2026',
                'ringkasan' => $ringkasan,
                'gambar' => 'images/berita/sungai-mangrove.jpg',
            ],
            [
                'slug' => 'program-edukasi-mangrove-pelajar',
                'judul' => 'Program Edukasi Mangrove untuk Pelajar SD–SMA',
                'kategori' => 'Edukasi',
                'tanggal' => '01 Agu 2026',
                'ringkasan' => $ringkasan,
                'gambar' => 'images/berita/lanskap-mangrove.jpg',
            ],
            [
                'slug' => 'jadwal-operasional-september-2026',
                'judul' => 'Jadwal Operasional Libur Panjang September 2026',
                'kategori' => 'Info',
                'tanggal' => '01 Agu 2026',
                'ringkasan' => $ringkasan,
                'gambar' => 'images/berita/jembatan-mangrove.jpg',
            ],
        ];
    }

    public function index(Request $request): View
    {
        $inputCari = $request->query('q', '');
        $cari = is_string($inputCari) ? trim(substr($inputCari, 0, 100)) : '';

        $inputKategori = $request->query('kategori', 'semua');
        $kategori = is_string($inputKategori)
            && in_array(strtolower($inputKategori), ['semua', 'event', 'edukasi', 'info'], true)
            ? strtolower($inputKategori)
            : 'semua';

        $hasil = collect($this->artikelContoh())
            ->filter(function (array $artikel) use ($kategori, $cari): bool {
                $sesuaiKategori = $kategori === 'semua'
                    || strtolower($artikel['kategori']) === $kategori;

                $teks = $artikel['judul'].' '.$artikel['ringkasan'].' '.$artikel['kategori'];
                $sesuaiCari = $cari === '' || stripos($teks, $cari) !== false;

                return $sesuaiKategori && $sesuaiCari;
            })
            ->values();

        // Pagination sudah siap digunakan saat artikel asli bertambah dari database.
        $perHalaman = 6;
        $halaman = LengthAwarePaginator::resolveCurrentPage();
        $berita = new LengthAwarePaginator(
            $hasil->forPage($halaman, $perHalaman)->values(),
            $hasil->count(),
            $perHalaman,
            $halaman,
            [
                'path' => route('wisatawan.berita.index'),
                'query' => array_filter([
                    'q' => $cari,
                    'kategori' => $kategori === 'semua' ? null : $kategori,
                ], fn ($value) => $value !== null && $value !== ''),
            ],
        );

        return view('wisatawan.berita.index', compact('berita', 'cari', 'kategori'));
    }

    public function show(string $slug): View
    {
        $artikel = collect($this->artikelContoh())->firstWhere('slug', $slug);

        abort_if($artikel === null, 404);

        return view('wisatawan.berita.show', compact('artikel'));
    }
}