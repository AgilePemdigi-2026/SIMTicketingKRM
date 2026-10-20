<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use App\Models\SesiFasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FasilitasController extends Controller
{
    public function index()
    {
        $fasilitas = Fasilitas::with('sesiFasilitas')
            ->orderBy('id_fasilitas', 'desc')
            ->get();

        return view('admin.fasilitas.index', compact('fasilitas'));
    }

    public function create()
    {
        return view('admin.fasilitas.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateFasilitas($request);

        $fotoBaru = null;

        if ($request->hasFile('foto_fasilitas')) {
            $fotoBaru = $request->file('foto_fasilitas')
                ->store('fasilitas', 'public');
        }

        try {
            DB::transaction(function () use ($validated, $fotoBaru) {
                $fasilitas = Fasilitas::create([
                    'nama_fasilitas' => $validated['nama_fasilitas'],
                    'kapasitas_fasilitas' => $validated['kapasitas_fasilitas'],
                    'status_fasilitas' => $validated['status_fasilitas'],
                    'ketentuan_fasilitas' => $validated['ketentuan_fasilitas'] ?? null,
                    'deskripsi_fasilitas' => $validated['deskripsi_fasilitas'] ?? null,
                    'foto_fasilitas' => $fotoBaru,
                ]);

                foreach ($validated['sesi'] as $sesi) {
                    $fasilitas->sesiFasilitas()->create([
                        'nama_sesi' => $sesi['nama_sesi'],
                        'jam_mulai' => $sesi['jam_mulai'],
                        'jam_selesai' => $sesi['jam_selesai'],
                        'durasi_menit' => $sesi['durasi_menit'],
                        'harga_sesi' => $sesi['harga_sesi'],
                        'status_aktif' => 1,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            if ($fotoBaru) {
                Storage::disk('public')->delete($fotoBaru);
            }

            throw $e;
        }

        return redirect()
            ->route('admin.fasilitas.index')
            ->with('success', 'Data fasilitas dan sesi berhasil ditambahkan.');
    }

    public function edit(Fasilitas $fasilitas)
    {
        $fasilitas->load('sesiFasilitas');

        return view('admin.fasilitas.edit', compact('fasilitas'));
    }

    public function update(Request $request, Fasilitas $fasilitas)
    {
        $validated = $this->validateFasilitas($request);

        $sesiDikirim = collect($validated['sesi']);
        $idSesiDikirim = $sesiDikirim
            ->pluck('id_sesi')
            ->filter()
            ->map(fn ($id) => (int) $id);

        $idSesiMilikFasilitas = $fasilitas->sesiFasilitas()
            ->pluck('id_sesi');

        $idTidakValid = $idSesiDikirim->diff($idSesiMilikFasilitas);

        if ($idTidakValid->isNotEmpty()) {
            throw ValidationException::withMessages([
                'sesi' => 'Ada sesi yang tidak terdaftar pada fasilitas ini.',
            ]);
        }

        $fotoLama = $fasilitas->foto_fasilitas;
        $fotoBaru = null;

        if ($request->hasFile('foto_fasilitas')) {
            $fotoBaru = $request->file('foto_fasilitas')
                ->store('fasilitas', 'public');
        }

        try {
            DB::transaction(function () use (
                $validated,
                $fasilitas,
                $sesiDikirim,
                $idSesiDikirim,
                $fotoBaru
            ) {
                $fasilitas->update([
                    'nama_fasilitas' => $validated['nama_fasilitas'],
                    'kapasitas_fasilitas' => $validated['kapasitas_fasilitas'],
                    'status_fasilitas' => $validated['status_fasilitas'],
                    'ketentuan_fasilitas' => $validated['ketentuan_fasilitas'] ?? null,
                    'deskripsi_fasilitas' => $validated['deskripsi_fasilitas'] ?? null,
                    'foto_fasilitas' => $fotoBaru ?? $fasilitas->foto_fasilitas,
                ]);

                foreach ($sesiDikirim as $sesi) {
                    $dataSesi = [
                        'nama_sesi' => $sesi['nama_sesi'],
                        'jam_mulai' => $sesi['jam_mulai'],
                        'jam_selesai' => $sesi['jam_selesai'],
                        'durasi_menit' => $sesi['durasi_menit'],
                        'harga_sesi' => $sesi['harga_sesi'],
                    ];

                    if (!empty($sesi['id_sesi'])) {
                        $fasilitas->sesiFasilitas()
                            ->where('id_sesi', $sesi['id_sesi'])
                            ->update($dataSesi);
                    } else {
                        $fasilitas->sesiFasilitas()->create(
                            $dataSesi + ['status_aktif' => 1]
                        );
                    }
                }

                // Hanya hapus sesi yang dihilangkan dari form.
                // Jika nantinya sesi sudah memiliki reservasi,
                // aturan penghapusan perlu diperketat.
                $fasilitas->sesiFasilitas()
                    ->whereNotIn('id_sesi', $idSesiDikirim->all())
                    ->delete();
            });
        } catch (\Throwable $e) {
            if ($fotoBaru) {
                Storage::disk('public')->delete($fotoBaru);
            }

            throw $e;
        }

        if (
            $fotoBaru &&
            $fotoLama &&
            !str_starts_with($fotoLama, 'http')
        ) {
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()
            ->route('admin.fasilitas.index')
            ->with('success', 'Data fasilitas dan sesi berhasil diperbarui.');
    }

    
    public function toggleStatus(Fasilitas $fasilitas)
    {
        $statusBaru = (int) $fasilitas->status_fasilitas === 1 ? 0 : 1;

        // Fasilitas yang akan diaktifkan harus memiliki minimal
        // satu sesi aktif.
        if ($statusBaru === 1) {
            $memilikiSesiAktif = $fasilitas->sesiFasilitas()
                ->where('status_aktif', 1)
                ->exists();

            if (! $memilikiSesiAktif) {
                return redirect()
                    ->route('admin.fasilitas.index')
                    ->with(
                        'error',
                        'Fasilitas belum bisa diaktifkan karena belum memiliki sesi aktif.'
                    );
            }
        }

        $fasilitas->update([
            'status_fasilitas' => $statusBaru,
        ]);

        $pesan = $statusBaru === 1
            ? 'Fasilitas berhasil diaktifkan.'
            : 'Fasilitas berhasil dinonaktifkan.';

        return redirect()
            ->route('admin.fasilitas.index')
            ->with('success', $pesan);
    }

    public function destroy(Fasilitas $fasilitas)
    {
        // Jangan menghapus fasilitas jika sudah memiliki reservasi.
        // Pemeriksaan ini akan digunakan setelah relasi reservasi tersedia.
        $fotoLama = $fasilitas->foto_fasilitas;

        DB::transaction(function () use ($fasilitas) {
            // Hapus semua sesi milik fasilitas ini terlebih dahulu.
            $fasilitas->sesiFasilitas()->delete();

            // Setelah sesi terhapus, hapus fasilitasnya.
            $fasilitas->delete();
        });

        // Hapus file foto hanya setelah transaksi database berhasil.
        if ($fotoLama && !str_starts_with($fotoLama, 'http')) {
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()
            ->route('admin.fasilitas.index')
            ->with('success', 'Data fasilitas beserta seluruh sesinya berhasil dihapus.');
    }


    private function validateFasilitas(Request $request): array
    {
        $validated = $request->validate([
            'nama_fasilitas' => 'required|string|max:100',
            'kapasitas_fasilitas' => 'required|integer|min:1',
            'status_fasilitas' => 'required|integer|in:0,1',
            'ketentuan_fasilitas' => 'nullable|string',
            'deskripsi_fasilitas' => 'nullable|string',
            'foto_fasilitas' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'sesi' => 'required|array|min:1',
            'sesi.*.id_sesi' => 'nullable|integer',
            'sesi.*.nama_sesi' => 'required|string|max:100',
            'sesi.*.jam_mulai' => 'required|date_format:H:i',
            'sesi.*.jam_selesai' => 'required|date_format:H:i',
            'sesi.*.durasi_menit' => 'required|integer|min:1',
            'sesi.*.harga_sesi' => 'required|numeric|min:0',
        ]);

        foreach ($validated['sesi'] as $index => $sesi) {
            $mulai = strtotime($sesi['jam_mulai']);
            $selesai = strtotime($sesi['jam_selesai']);

            if ($selesai <= $mulai) {
                throw ValidationException::withMessages([
                    "sesi.$index.jam_selesai" =>
                        'Jam selesai harus setelah jam mulai pada hari yang sama.',
                ]);
            }

            $durasiAktual = ($selesai - $mulai) / 60;

            if ((int) $sesi['durasi_menit'] !== (int) $durasiAktual) {
                throw ValidationException::withMessages([
                    "sesi.$index.durasi_menit" =>
                        'Durasi harus sama dengan selisih jam mulai dan jam selesai.',
                ]);
            }
        }

        return $validated;
    }
}
