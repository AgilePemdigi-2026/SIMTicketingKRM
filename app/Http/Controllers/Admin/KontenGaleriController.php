<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Konten;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KontenGaleriController extends Controller
{
    /**
     * Memastikan pengguna adalah Admin.
     */
    private function pastikanAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->role === 'admin',
            403
        );
    }

    /**
     * Menampilkan daftar foto Galeri.
     */
    public function index(Request $request): View
    {
        $this->pastikanAdmin($request);

        $galeri = Konten::query()
            ->where('jenis_konten', 'galeri')
            ->latest('created_at')
            ->paginate(10);

        return view(
            'admin.konten-galeri.index',
            compact('galeri')
        );
    }

    /**
     * Menampilkan form tambah foto Galeri.
     */
    public function create(Request $request): View
    {
        $this->pastikanAdmin($request);

        return view('admin.konten-galeri.create');
    }

    /**
     * Menyimpan foto Galeri ke PostgreSQL.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->pastikanAdmin($request);

        // Validasi input Admin.
        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'max:150',
            ],
            'isi' => [
                'nullable',
                'string',
            ],
            'gambar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'status_publikasi' => [
                'required',
                'in:draft,published',
            ],
        ]);

        // Simpan foto ke storage publik Laravel.
        $pathGambar = $request
            ->file('gambar')
            ->store('konten/galeri', 'public');

        // Simpan informasi Galeri ke PostgreSQL.
        Konten::create([
            'id_pengguna' => $request->user()->id,
            'jenis_konten' => 'galeri',
            'judul' => $validated['judul'],
            'isi' => $validated['isi'] ?? null,
            'gambar' => $pathGambar,
            'penulis' => $request->user()->name,
            'status_publikasi' => $validated['status_publikasi'],
            'waktu_publikasi' => $validated['status_publikasi'] === 'published'
                ? now()
                : null,
        ]);

        return redirect()
            ->route('admin.konten-galeri.index')
            ->with('success', 'Foto galeri berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit foto Galeri.
     */
    public function edit(
        Request $request,
        Konten $konten
    ): View {
        $this->pastikanAdmin($request);

        // Pastikan yang diedit adalah Galeri,
        // bukan Berita atau jenis konten lainnya.
        abort_unless(
            $konten->jenis_konten === 'galeri',
            404
        );

        return view('admin.konten-galeri.edit', [
            'foto' => $konten,
        ]);
    }

    
    /**
     * Memperbarui foto Galeri di PostgreSQL.
     */
    public function update(
        Request $request,
        Konten $konten
    ): RedirectResponse {
        $this->pastikanAdmin($request);

        // Pastikan data yang diedit adalah Galeri,
        // bukan Berita atau jenis konten lainnya.
        abort_unless(
            $konten->jenis_konten === 'galeri',
            404
        );

        // Validasi input Admin.
        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'max:150',
            ],
            'isi' => [
                'nullable',
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

        // Siapkan data yang akan diperbarui.
        $data = [
            'judul' => $validated['judul'],
            'isi' => $validated['isi'] ?? null,
            'status_publikasi' => $validated['status_publikasi'],
        ];

        // Atur tanggal publikasi.
        if ($validated['status_publikasi'] === 'published') {
            $data['waktu_publikasi'] =
                $konten->status_publikasi === 'published'
                && $konten->waktu_publikasi
                    ? $konten->waktu_publikasi
                    : now();
        } else {
            $data['waktu_publikasi'] = null;
        }

        // Simpan path gambar lama.
        $gambarLama = $konten->gambar;
        $gambarBaru = null;

        // Upload gambar baru jika Admin mengganti foto.
        if ($request->hasFile('gambar')) {
            $gambarBaru = $request
                ->file('gambar')
                ->store('konten/galeri', 'public');

            $data['gambar'] = $gambarBaru;
        }

        // Perbarui data PostgreSQL.
        try {
            $konten->update($data);
        } catch (\Throwable $e) {
            // Bersihkan gambar baru jika update database gagal.
            if ($gambarBaru) {
                Storage::disk('public')->delete($gambarBaru);
            }

            throw $e;
        }

        // Hapus gambar lama setelah update berhasil.
        if ($gambarBaru && $gambarLama) {
            Storage::disk('public')->delete($gambarLama);
        }

        return redirect()
            ->route('admin.konten-galeri.index')
            ->with('success', 'Foto galeri berhasil diperbarui.');
    }

    
    /**
     * Menghapus foto Galeri dari PostgreSQL.
     */
    public function destroy(
        Request $request,
        Konten $konten
    ): RedirectResponse {
        $this->pastikanAdmin($request);

        // Pastikan hanya konten berjenis Galeri
        // yang boleh dihapus dari modul ini.
        abort_unless(
            $konten->jenis_konten === 'galeri',
            404
        );

        // Simpan path foto sebelum data dihapus.
        $gambar = $konten->gambar;

        // Hapus data Galeri dari PostgreSQL.
        $konten->delete();

        // Hapus file foto setelah data berhasil dihapus.
        if ($gambar) {
            Storage::disk('public')->delete($gambar);
        }

        return redirect()
            ->route('admin.konten-galeri.index')
            ->with('success', 'Foto galeri berhasil dihapus.');
    }

}