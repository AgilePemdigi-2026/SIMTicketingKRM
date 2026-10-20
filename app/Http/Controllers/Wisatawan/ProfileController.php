<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // Menampilkan profil wisatawan
    public function show(Request $request)
    {
        $user = $request->user();

        return view('wisatawan.profil', [
            'user' => $user
        ]);
    }

    // Menampilkan form edit profil
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('wisatawan.edit-profil', [
            'user' => $user
        ]);
    }

    // Memproses perubahan profil
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'phone' => [
                'required',
                'string',
                'max:25',
                Rule::unique('users', 'phone')
                    ->ignore($user->id),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],
        ]);

        // Menyimpan perubahan ke database
        $user->update($validated);

        return redirect()
            ->route('wisatawan.profil')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    // Menampilkan halaman ubah password
    public function editPassword()
    {
        return view('wisatawan.ubah-password');
    }

    // Memproses perubahan password
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'different:current_password',
                'confirmed',
            ],
        ]);

        $user = $request->user();

        // Hash password baru sebelum disimpan
        $user->password = Hash::make(
            $validated['password']
        );

        $user->save();

        return redirect()
            ->route('wisatawan.profil')
            ->with(
                'success',
                'Password berhasil diubah.'
            );
    }
}