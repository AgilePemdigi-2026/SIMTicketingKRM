<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfoKontak;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InfoKontakController extends Controller
{
    /**
     * Menampilkan halaman pengelolaan Info Kontak.
     */
    public function index(): View
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'admin',
            403
        );

        // Mengambil satu data kontak utama dari PostgreSQL.
        $kontak = InfoKontak::query()
            ->orderBy('id_kontak')
            ->first();

        return view(
            'admin.konten-kontak.index',
            compact('kontak')
        );
    }

    /**
     * Menyimpan atau memperbarui Info Kontak.
     */
    public function update(Request $request): RedirectResponse
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'admin',
            403
        );

        // Validasi data yang dikirim Admin.
        $data = $request->validate([
            'alamat' => [
                'nullable',
                'string',
            ],
            'nomor_telepon' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'jam_operasional' => [
                'nullable',
                'string',
                'max:255',
            ],
            'google_maps_url' => [
                'nullable',
                'url',
                'max:255',
            ],
            'instagram' => [
                'nullable',
                'string',
                'max:255',
            ],
            'facebook' => [
                'nullable',
                'string',
                'max:255',
            ],
            'youtube' => [
                'nullable',
                'string',
                'max:255',
            ],
            'twitter' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        // Mengambil data kontak yang sudah ada,
        // atau membuat data pertama jika masih kosong.
        $kontak = InfoKontak::query()
            ->orderBy('id_kontak')
            ->first() ?? new InfoKontak();

        // Memperbarui seluruh field berdasarkan input Admin.
        $kontak->fill($data);

        // Menyimpan ke tabel info_kontak PostgreSQL.
        $kontak->save();

        return redirect()
            ->route('admin.konten-kontak.index')
            ->with(
                'success',
                'Informasi kontak berhasil disimpan.'
            );
    }
}