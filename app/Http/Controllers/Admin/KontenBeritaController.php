<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Konten;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KontenBeritaController extends Controller
{
    /**
     * Pastikan hanya Admin yang dapat mengelola berita.
     */
    private function pastikanAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->role === 'admin',
            403
        );
    }

    /**
     * Menampilkan daftar Berita & Artikel.
     */
    public function index(Request $request): View
    {
        $this->pastikanAdmin($request);

        $berita = Konten::query()
            ->where('jenis_konten', 'berita')
            ->latest('created_at')
            ->paginate(10);

        return view('admin.konten-berita.index', compact('berita'));
    }

    /**
     * Menampilkan form tambah berita.
     */
    public function create(Request $request): View
    {
        $this->pastikanAdmin($request);

        return view('admin.konten-berita.create');
    }

    /**
     * Menyimpan berita baru ke PostgreSQL.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->pastikanAdmin($request);

        // Validasi data dari form Admin
        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'max:150',
            ],
            'kategori' => [
                'required',
                'in:Event,Edukasi,Info',
            ],
            'isi' => [
                'required',
                'string',
            ],
            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'status_publikasi' => [
                'required',
                'in:draft,published',
            ],
        ]);

        // Membuat slug dari judul artikel
        $slugDasar = Str::slug($validated['judul']);

        if ($slugDasar === '') {
            $slugDasar = 'artikel';
        }

        $slugDasar = substr($slugDasar, 0, 150);

        $slug = $slugDasar;
        $nomor = 2;

        // Hindari slug duplikat
        while (Konten::where('slug', $slug)->exists()) {
            $slug = $slugDasar . '-' . $nomor;
            $nomor++;
        }

        // Data yang akan disimpan
        $data = [
            'id_pengguna' => $request->user()->id,
            'jenis_konten' => 'berita',
            'judul' => $validated['judul'],
            'slug' => $slug,
            'kategori' => $validated['kategori'],
            'isi' => $validated['isi'],
            'penulis' => $request->user()->name,
            'status_publikasi' => $validated['status_publikasi'],
            'waktu_publikasi' => $validated['status_publikasi'] === 'published'
                ? now()
                : null,
        ];

        // Upload thumbnail jika Admin memilih gambar
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request
                ->file('gambar')
                ->store('konten/berita', 'public');
        }

        // Simpan menggunakan Model Konten
        Konten::create($data);

        return redirect()
            ->route('admin.konten-berita.index')
            ->with('success', 'Berita berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit berita.
     */
    public function edit(Request $request, Konten $konten): View
    {
        $this->pastikanAdmin($request);

        // Pastikan data yang diedit adalah berita,
        // bukan galeri atau jenis konten lainnya.
        abort_unless(
            $konten->jenis_konten === 'berita',
            404
        );

        return view('admin.konten-berita.edit', [
            'artikel' => $konten,
        ]);
    }

    
    /**
     * Memperbarui berita yang sudah tersimpan.
     */
    public function update(
        Request $request,
        Konten $konten
    ): RedirectResponse {
        $this->pastikanAdmin($request);

        // Pastikan konten yang diubah adalah berita.
        abort_unless(
            $konten->jenis_konten === 'berita',
            404
        );

        // Validasi input Admin.
        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'max:150',
            ],
            'kategori' => [
                'required',
                'in:Event,Edukasi,Info',
            ],
            'isi' => [
                'required',
                'string',
            ],
            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'status_publikasi' => [
                'required',
                'in:draft,published',
            ],
        ]);

        // Siapkan data artikel yang diperbarui.
        $data = [
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'isi' => $validated['isi'],
            'status_publikasi' => $validated['status_publikasi'],
        ];

        // Atur tanggal publikasi berdasarkan status.
        if ($validated['status_publikasi'] === 'published') {
            $data['waktu_publikasi'] =
                $konten->status_publikasi === 'published'
                && $konten->waktu_publikasi
                    ? $konten->waktu_publikasi
                    : now();
        } else {
            $data['waktu_publikasi'] = null;
        }

        // Simpan gambar lama untuk dibersihkan
        // jika Admin mengganti thumbnail.
        $gambarLama = null;

        if ($request->hasFile('gambar')) {
            $gambarLama = $konten->gambar;

            $data['gambar'] = $request
                ->file('gambar')
                ->store('konten/berita', 'public');
        }

        // Update artikel melalui Eloquent.
        $konten->update($data);

        // Hapus thumbnail lama jika diganti.
        if ($gambarLama) {
            Storage::disk('public')->delete($gambarLama);
        }

        return redirect()
            ->route('admin.konten-berita.index')
            ->with('success', 'Berita berhasil diperbarui.');
    }

    
    /**
     * Menghapus berita dari PostgreSQL.
     */
    public function destroy(
        Request $request,
        Konten $konten
    ): RedirectResponse {
        $this->pastikanAdmin($request);

        // Pastikan hanya konten berjenis berita
        // yang boleh dihapus dari modul ini.
        abort_unless(
            $konten->jenis_konten === 'berita',
            404
        );

        // Simpan path gambar sebelum artikel dihapus.
        $gambar = $konten->gambar;

        // Hapus data berita dari PostgreSQL.
        $konten->delete();

        // Hapus thumbnail dari storage jika tersedia.
        if ($gambar) {
            Storage::disk('public')->delete($gambar);
        }

        return redirect()
            ->route('admin.konten-berita.index')
            ->with('success', 'Berita berhasil dihapus.');
    }

}