<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use App\Models\Konten;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Support\FigmaDemoContent;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class BeritaController extends Controller
{
    /**
     * Mengubah data Model menjadi format
     * yang digunakan oleh Blade wisatawan.
     */
    private function formatArtikel(Konten $artikel): array
    {
        return [
            'slug' => $artikel->slug,
            'judul' => $artikel->judul,
            'kategori' => $artikel->kategori,
            'tanggal' => $artikel->waktu_publikasi
                ? $artikel->waktu_publikasi
                    ->locale('id')
                    ->translatedFormat('d M Y')
                : '-',
            'ringkasan' => Str::limit(
                strip_tags($artikel->isi ?? ''),
                150
            ),
            'gambar' => $artikel->gambar && Storage::disk('public')->exists($artikel->gambar)
                ? 'storage/' . $artikel->gambar
                : 'images/figma/lanskap.jpg',
            'isi' => $artikel->isi,
            'penulis' => $artikel->penulis,
        ];
    }

    /**
     * Menampilkan daftar berita yang dipublikasikan.
     */
    public function index(Request $request): View
    {
        // Ambil kata kunci pencarian
        $inputCari = $request->query('q', '');

        $cari = is_string($inputCari)
            ? trim(substr($inputCari, 0, 100))
            : '';

        // Ambil kategori
        $inputKategori = $request->query(
            'kategori',
            'semua'
        );

        $kategori = is_string($inputKategori)
            && in_array(
                strtolower($inputKategori),
                ['semua', 'event', 'edukasi', 'info'],
                true
            )
                ? strtolower($inputKategori)
                : 'semua';

        // Query berita dari PostgreSQL
        $query = Konten::query()
            ->where('jenis_konten', 'berita')
            ->where('status_publikasi', 'published')
            ->whereNotNull('slug')
            ->where('waktu_publikasi', '<=', now());

        // Filter berdasarkan kategori
        if ($kategori !== 'semua') {
            $query->where(
                'kategori',
                'ilike',
                $kategori
            );
        }

        // Pencarian berita
        if ($cari !== '') {
            $query->where(function ($q) use ($cari) {
                $q->where('judul', 'ilike', '%' . $cari . '%')
                    ->orWhere('isi', 'ilike', '%' . $cari . '%')
                    ->orWhere('kategori', 'ilike', '%' . $cari . '%');
            });
        }

        // Urutkan berita terbaru dan batasi 6 per halaman
        $berita = $query
            ->orderByDesc('waktu_publikasi')
            ->orderByDesc('id_konten')
            ->paginate(6)
            ->withQueryString();

        // Sesuaikan format dengan Blade lama
        $berita->through(
            fn (Konten $artikel) => $this->formatArtikel($artikel)
        );

        if (FigmaDemoContent::enabled() && ! $this->publishedArticlesExist()) {
            $demo = FigmaDemoContent::article();
            $matches = in_array($kategori, ['semua', 'edukasi'], true)
                && ($cari === '' || str_contains(mb_strtolower($demo['judul'].' '.$demo['isi']), mb_strtolower($cari)));
            $page = LengthAwarePaginator::resolveCurrentPage();
            $items = $matches && $page === 1 ? [$demo] : [];
            $berita = new LengthAwarePaginator($items, $matches ? 1 : 0, 6, $page,
                ['path' => $request->url(), 'query' => $request->query()]);
        }

        return view(
            'wisatawan.berita.index',
            compact('berita', 'cari', 'kategori')
        );
    }

    /**
     * Menampilkan detail artikel berdasarkan slug.
     */
    private function publishedArticlesExist(): bool
    {
        return Konten::where('jenis_konten', 'berita')->where('status_publikasi', 'published')
            ->whereNotNull('slug')->where('waktu_publikasi', '<=', now())->exists();
    }

    public function show(string $slug): View
    {
        if ($slug === FigmaDemoContent::ARTICLE_SLUG && FigmaDemoContent::enabled() && ! $this->publishedArticlesExist()) {
            return view('wisatawan.berita.show', ['artikel' => FigmaDemoContent::article()]);
        }

        $konten = Konten::query()
            ->where('jenis_konten', 'berita')
            ->where('status_publikasi', 'published')
            ->where('waktu_publikasi', '<=', now())
            ->where('slug', $slug)
            ->firstOrFail();

        $artikel = $this->formatArtikel($konten);

        return view(
            'wisatawan.berita.show',
            compact('artikel')
        );
    }
}
