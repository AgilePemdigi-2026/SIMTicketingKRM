<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfoDestinasi;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InfoDestinasiController extends Controller
{
    /**
     * Menampilkan formulir pengelolaan Info Destinasi.
     */
    public function index(): View
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'admin',
            403
        );

        // Info Destinasi dikelola sebagai satu profil utama.
        $destinasi = InfoDestinasi::query()
            ->orderBy('id_destinasi')
            ->first();

        return view(
            'admin.konten-destinasi.index',
            compact('destinasi')
        );
    }

    /**
     * Menyimpan atau memperbarui informasi destinasi.
     */
    public function update(Request $request): RedirectResponse
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'admin',
            403
        );

        // Validasi input dari Admin.
        $data = $request->validate([
            'nama_destinasi' => [
                'required',
                'string',
                'max:150',
            ],
            'deskripsi_singkat' => [
                'nullable',
                'string',
            ],
            'deskripsi_lengkap' => [
                'nullable',
                'string',
            ],
            'jam_buka' => [
                'nullable',
                'date_format:H:i',
            ],
            'jam_tutup' => [
                'nullable',
                'date_format:H:i',
            ],
            'alamat' => [
                'nullable',
                'string',
            ],
            'foto_utama' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        // Mengambil data yang sudah ada,
        // atau membuat data pertama jika masih kosong.
        $destinasi = InfoDestinasi::query()
            ->orderBy('id_destinasi')
            ->first() ?? new InfoDestinasi();

        $fotoLama = $destinasi->foto_utama;
        $fotoBaru = null;

        // File gambar ditangani terpisah.
        unset($data['foto_utama']);

        if ($request->hasFile('foto_utama')) {
            $fotoBaru = $request
                ->file('foto_utama')
                ->store('konten/destinasi', 'public');

            $data['foto_utama'] = $fotoBaru;
        }

        try {
            // Menyimpan perubahan ke PostgreSQL.
            $destinasi->fill($data);
            $destinasi->save();
        } catch (\Throwable $e) {

            // Hapus upload baru jika penyimpanan DB gagal.
            if ($fotoBaru) {
                Storage::disk('public')->delete($fotoBaru);
            }

            throw $e;
        }

        // Menghapus foto lama setelah data berhasil disimpan.
        if ($fotoBaru && $fotoLama && $fotoLama !== $fotoBaru) {
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()
            ->route('admin.konten-destinasi.index')
            ->with(
                'success',
                'Informasi destinasi berhasil disimpan.'
            );
    }
}