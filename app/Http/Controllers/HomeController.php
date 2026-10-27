<?php

namespace App\Http\Controllers;

use App\Models\Wahana;
use App\Models\Fasilitas;
use App\Models\Konten;
use App\Models\InfoKontak;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $galeri = Konten::query()
            ->where('jenis_konten', 'galeri')
            ->where('status_publikasi', 'published')
            ->where('waktu_publikasi', '<=', now())
            ->whereNotNull('gambar')
            ->orderByDesc('waktu_publikasi')
            ->orderByDesc('id_konten')
            ->get()
            ->filter(function (Konten $item) {
                return Storage::disk('public')->exists($item->gambar);
            })
            ->map(function (Konten $item) {
                return 'storage/' . $item->gambar;
            })
            ->values()
            ->all();


        // Berita terbaru dari database.
        // Hanya artikel Published yang ditampilkan di Beranda.
        $berita = Konten::query()
            ->where('jenis_konten', 'berita')
            ->where('status_publikasi', 'published')
            ->whereNotNull('slug')
            ->where('waktu_publikasi', '<=', now())
            ->orderByDesc('waktu_publikasi')
            ->orderByDesc('id_konten')
            ->limit(3)
            ->get()
            ->map(function (Konten $item) {
                $gambar = $item->gambar
                    && Storage::disk('public')->exists($item->gambar)
                        ? 'storage/' . $item->gambar
                        : null;

                return [
                    'slug' => $item->slug,
                    'judul' => $item->judul,
                    'kategori' => $item->kategori,
                    'tanggal' => $item->waktu_publikasi
                        ->locale('id')
                        ->translatedFormat('d M Y'),
                    'ringkasan' => Str::limit(
                        strip_tags($item->isi ?? ''),
                        130
                    ),
                    'gambar' => $gambar,
                ];
            })
            ->all();

            
        // Informasi kontak utama dari database.
        // Dikelola melalui Manajemen Konten oleh Admin.
        $kontak = InfoKontak::query()
            ->orderBy('id_kontak')
            ->first();

        
        return view('public.home', compact(
            'wahana',
            'fasilitas',
            'galeri',
            'berita',
            'kontak'
        ));

    }
}