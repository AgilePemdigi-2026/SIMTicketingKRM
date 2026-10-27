<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use App\Models\Wahana;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WahanaController extends Controller
{
    public function show(string $slug): View|RedirectResponse
    {
        // Kompatibilitas link destinasi dari mockup lama.
        if ($slug === 'kebun-raya-mangrove') {
            return redirect()->route('wisatawan.destinasi');
        }

        // Link wahana baru menggunakan ID dari PostgreSQL.
        abort_unless(ctype_digit($slug), 404);

        $item = Wahana::query()
            ->where('status_aktif', 1)
            ->findOrFail((int) $slug);

        // Cari tiket aktif yang terkait langsung atau
        // melalui tabel relasi tiket_wahana.
        $tiket = DB::table('daftar_tiket')
            ->where('status_aktif', 1)
            ->where(function ($query) use ($item) {
                $query
                    ->where('id_wahana', $item->id_wahana)
                    ->orWhereIn('id_tiket', function ($subquery) use ($item) {
                        $subquery
                            ->select('id_tiket')
                            ->from('tiket_wahana')
                            ->where('id_wahana', $item->id_wahana);
                    });
            })
            ->orderBy('harga_dewasa')
            ->first();

        $foto = $item->foto;

        $gambar = $foto && is_file(public_path('storage/' . $foto))
            ? 'storage/' . $foto
            : 'images/wahana/boardwalk-mangrove.jpg';

        $hargaDewasa = $tiket
            ? 'Rp ' . number_format((float) $tiket->harga_dewasa, 0, ',', '.')
            : null;

        $hargaAnak = $tiket
            ? 'Rp ' . number_format((float) $tiket->harga_anak, 0, ',', '.')
            : null;

        $jamOperasional = $item->jam_buka && $item->jam_tutup
            ? substr((string) $item->jam_buka, 0, 5)
                . ' - '
                . substr((string) $item->jam_tutup, 0, 5)
                . ' WIB'
            : 'Jam operasional belum tersedia.';

        $wahana = [
            'nama' => $item->nama_wahana,
            'deskripsi' => $item->deskripsi
                ?: 'Deskripsi wahana belum tersedia.',
            'gambar' => $gambar,

            // Harga berasal dari tiket terkait,
            // bukan harga sewa wahana tersendiri.
            'harga' => $hargaDewasa,
            'harga_satuan' => $tiket
                ? 'Harga dewasa tiket terkait: ' . $tiket->nama_tiket
                : null,
            'harga_detail' => $tiket
                ? 'Tiket ' . $tiket->nama_tiket
                    . ': Dewasa ' . $hargaDewasa
                    . ', Anak ' . $hargaAnak . '.'
                : 'Belum ada harga tiket terkait yang tersedia.',

            'ketentuan' => $tiket?->ketentuan
                ?: 'Ketentuan penggunaan belum tersedia.',

            'jam_operasional' => $jamOperasional,

            // Galeri sementara menggunakan ilustrasi mockup
            // sampai foto-foto wahana tersedia.
            'galeri' => [
                [
                    'gambar' => 'images/wahana/danau-mangrove.jpg',
                    'alt' => 'Ilustrasi pemandangan danau mangrove',
                ],
                [
                    'gambar' => 'images/wahana/sungai-mangrove.jpg',
                    'alt' => 'Ilustrasi perairan mangrove',
                ],
                [
                    'gambar' => 'images/wahana/gunung-mangrove.jpg',
                    'alt' => 'Ilustrasi kawasan hijau mangrove',
                ],
                [
                    'gambar' => 'images/wahana/boardwalk-mangrove.jpg',
                    'alt' => 'Ilustrasi jalur mangrove',
                ],
            ],
        ];

        return view('wisatawan.detail-wahana', compact('wahana'));
    }
}
