<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wahana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WahanaController extends Controller
{
    public function index()
    {
        $wahana = Wahana::orderBy('id_wahana', 'desc')->get();

        return view('admin.wahana.index', compact('wahana'));
    }

    public function create()
    {
        return view('admin.wahana.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_wahana' => 'required|string|max:100',
            'durasi' => 'required|integer|min:1',
            'jam_buka' => 'required|date_format:H:i',
            'jam_tutup' => 'required|date_format:H:i',
            'kapasitas' => 'required|integer|min:1',
            'status_aktif' => 'required|integer|in:0,1',
            'deskripsi' => 'nullable|string|max:500',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')
                ->store('wahana', 'public');
        } else {
            unset($validated['foto']);
        }

        Wahana::create($validated);

        return redirect()
            ->route('admin.wahana.index')
            ->with('success', 'Data wahana berhasil ditambahkan.');
    }

    public function edit(Wahana $wahana)
    {
        return view('admin.wahana.edit', compact('wahana'));
    }

    public function update(Request $request, Wahana $wahana)
    {
        $validated = $request->validate([
            'nama_wahana' => 'required|string|max:100',
            'durasi' => 'required|integer|min:1',
            'jam_buka' => 'required|date_format:H:i',
            'jam_tutup' => 'required|date_format:H:i',
            'kapasitas' => 'required|integer|min:1',
            'status_aktif' => 'required|integer|in:0,1',
            'deskripsi' => 'nullable|string|max:500',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        unset($validated['foto']);

        if ($request->hasFile('foto')) {
            $fotoLama = $wahana->foto;

            $validated['foto'] = $request->file('foto')
                ->store('wahana', 'public');

            if ($fotoLama && !str_starts_with($fotoLama, 'http')) {
                Storage::disk('public')->delete($fotoLama);
            }
        }

        $wahana->update($validated);

        return redirect()
            ->route('admin.wahana.index')
            ->with('success', 'Data wahana berhasil diperbarui.');
    }

    public function destroy(Wahana $wahana)
    {
        $wahana->delete();

        if ($wahana->foto && !str_starts_with($wahana->foto, 'http')) {
            Storage::disk('public')->delete($wahana->foto);
        }

        return redirect()
            ->route('admin.wahana.index')
            ->with('success', 'Data wahana berhasil dihapus.');
    }
}
