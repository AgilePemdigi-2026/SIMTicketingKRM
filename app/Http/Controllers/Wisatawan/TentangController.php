<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use App\Models\InfoDestinasi;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TentangController extends Controller
{
    public function index(): View
    {
        // Mengambil informasi destinasi dari PostgreSQL.
        $profil = InfoDestinasi::query()
            ->orderBy('id_destinasi')
            ->first();

        // Gunakan foto dari Admin jika tersedia.
        $foto = $profil?->foto_utama;

        $gambar = $foto && Storage::disk('public')->exists($foto)
            ? asset('storage/' . $foto)
            : asset('images/destinasi/boardwalk-hero.jpg');

        // Menyiapkan informasi profil Kebun Raya Mangrove.
        $tentang = [
            'nama' => $profil?->nama_destinasi
                ?: 'Kebun Raya Mangrove Surabaya',

            'deskripsi' => $profil?->deskripsi_lengkap
                ?: ($profil?->deskripsi_singkat
                    ?: 'Informasi profil destinasi belum tersedia.'),

            'gambar' => $gambar,

            'alamat' => $profil?->alamat
                ?: 'Informasi alamat belum tersedia.',

            'jam_operasional' => $profil?->jam_buka && $profil?->jam_tutup
                ? substr((string) $profil->jam_buka, 0, 5)
                    . ' - '
                    . substr((string) $profil->jam_tutup, 0, 5)
                    . ' WIB'
                : 'Informasi jam operasional belum tersedia.',
        ];

        return view('wisatawan.tentang', compact('tentang'));
    }
}
