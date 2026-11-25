<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // Menampilkan halaman Login.
    public function create()
    {
        return view('auth.login');
    }

    // Memproses Login.
    public function store(
        Request $request,
        AuditLogService $auditLog
    ) {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Menentukan apakah pengguna login dengan email
        // atau nomor telepon.
        $field = filter_var(
            $validated['login'],
            FILTER_VALIDATE_EMAIL
        ) ? 'email' : 'phone';

        // Memeriksa email/telepon dan password
        if (! Auth::attempt([
            $field => $validated['login'],
            'password' => $validated['password'],
            'is_active' => true,
        ])) {
            throw ValidationException::withMessages([
                'login' => 'Email/nomor telepon atau password salah, atau akun Anda sedang dinonaktifkan.',
            ]);
        }

        // Membuat session baru setelah autentikasi berhasil.
        $request->session()->regenerate();

        $user = Auth::user();

        // Menentukan tujuan berdasarkan role pengguna.
        $destination = match ($user->role) {
            'admin' => 'admin.dashboard',
            'petugas' => 'petugas.dashboard',
            'wisatawan' => 'wisatawan.beranda',
            default => null,
        };

        // Role tidak valid tidak dicatat sebagai login berhasil.
        if ($destination === null) {
            return $this->rejectUnknownRole($request);
        }

        // Mencatat aktivitas login setelah role dipastikan valid.
        try {
            $auditLog->record(
                actor: $user,
                module: 'Autentikasi',
                action: 'login',
                description: 'Pengguna berhasil login.',
                subjectType: 'User',
                subjectId: $user->getKey()
            );
        } catch (\Throwable $exception) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw $exception;
        }

        return redirect()->route($destination);
    }

    // Menolak role yang tidak dikenal.
    private function rejectUnknownRole(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        abort(403, 'Role pengguna tidak dikenali.');
    }

    // Logout pengguna.
    public function destroy(
        Request $request,
        AuditLogService $auditLog
    ) {
        // Simpan identitas pengguna sebelum session dihapus.
        $user = Auth::user();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Mencatat aktivitas logout normal.
        if ($user !== null) {
            try {
                $auditLog->record(
                    actor: $user,
                    module: 'Autentikasi',
                    action: 'logout',
                    description: 'Pengguna berhasil logout.',
                    subjectType: 'User',
                    subjectId: $user->getKey()
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('login')
            ->with('success', 'Anda berhasil logout.');
    }
}
