<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DaftarTiket;
use App\Models\Wahana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DaftarTiketController extends Controller
{
    public function index()
    {
        $daftarTiket = DaftarTiket::with('daftarWahana')
            ->orderBy('id_tiket', 'desc')
            ->get();

        return view('admin.daftar-tiket.index', compact('daftarTiket'));
    }

    public function create()
    {
        $wahana = Wahana::orderBy('nama_wahana')->get();

        return view('admin.daftar-tiket.create', compact('wahana'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTiket($request);

        $wahanaIds = $validated['wahana_ids'] ?? [];
        unset($validated['wahana_ids']);

        $validated['id_wahana'] = $wahanaIds[0] ?? null;

        DB::transaction(function () use ($validated, $wahanaIds) {
            $tiket = DaftarTiket::create($validated);
            $tiket->daftarWahana()->sync($wahanaIds);
        });

        return redirect()
            ->route('admin.daftar-tiket.index')
            ->with('success', 'Data tiket berhasil ditambahkan.');
    }

    public function edit(DaftarTiket $daftarTiket)
    {
        $daftarTiket->load('daftarWahana');
        $wahana = Wahana::orderBy('nama_wahana')->get();

        return view(
            'admin.daftar-tiket.edit',
            compact('daftarTiket', 'wahana')
        );
    }

    public function update(Request $request, DaftarTiket $daftarTiket)
    {
        $validated = $this->validateTiket($request);

        $wahanaIds = $validated['wahana_ids'] ?? [];
        unset($validated['wahana_ids']);

        $validated['id_wahana'] = $wahanaIds[0] ?? null;

        DB::transaction(function () use ($daftarTiket, $validated, $wahanaIds) {
            $daftarTiket->update($validated);
            $daftarTiket->daftarWahana()->sync($wahanaIds);
        });

        return redirect()
            ->route('admin.daftar-tiket.index')
            ->with('success', 'Data tiket berhasil diperbarui.');
    }

    private function validateTiket(Request $request): array
    {
        return $request->validate([
            'wahana_ids' => ['nullable', 'array'],
            'wahana_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('wahana', 'id_wahana'),
            ],
            'nama_tiket' => 'required|string|max:100',
            'kategori_tiket' => [
                'required',
                Rule::in(['Tiket Masuk', 'Tiket Wahana']),
            ],
            'kuota' => 'required|integer|min:0',
            'kategori_wisatawan' => [
                'required',
                Rule::in(['Domestik (WNI)', 'Mancanegara (WNA)']),
            ],
            'harga_dewasa' => 'required|numeric|min:0',
            'harga_anak' => 'required|numeric|min:0',
            'status_aktif' => 'required|integer|in:0,1',
            'ketentuan' => 'nullable|string',
        ]);
    }

    public function destroy(DaftarTiket $daftarTiket)
    {
        $daftarTiket->delete();

        return redirect()
            ->route('admin.daftar-tiket.index')
            ->with('success', 'Data tiket berhasil dihapus.');
    }
}
