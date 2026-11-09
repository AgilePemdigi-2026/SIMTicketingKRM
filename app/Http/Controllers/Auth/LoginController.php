<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // Menampilkan halaman Login
    public function create()
    {
        return view('auth.login');
    }

    // Memproses Login
    public function store(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Menentukan apakah pengguna login dengan email
        // atau nomor telepon
        $field = filter_var(
            $validated['login'],
            FILTER_VALIDATE_EMAIL
        ) ? 'email' : 'phone';

        // Memeriksa email/telepon dan password
        if (! Auth::attempt([
            $field => $validated['login'],
            'password' => $validated['password'],
        ])) {
            throw ValidationException::withMessages([
                'login' => 'Email/nomor telepon atau password salah.',
            ]);
        }

        // Membuat session baru setelah Login berhasil
        $request->session()->regenerate();

        // Mengarahkan pengguna berdasarkan role
        return match (Auth::user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'petugas' => redirect()->route('petugas.dashboard'),
            'wisatawan' => redirect()->route('wisatawan.beranda'),
            default => $this->rejectUnknownRole($request),
        };
    }

    // Menolak role yang tidak dikenal
    private function rejectUnknownRole(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        abort(403, 'Role pengguna tidak dikenali.');
    }

    // Logout pengguna
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda berhasil logout.');
    }
}

