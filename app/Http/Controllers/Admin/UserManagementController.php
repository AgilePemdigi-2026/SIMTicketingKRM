<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
    'status' => ['nullable', 'in:0,1'],
    'activity_date' => ['nullable', 'date'],
    'search' => ['nullable', 'string', 'max:100'],
]);
        $tab = $request->input('tab', 'wisatawan');

        if (!in_array($tab, ['wisatawan', 'petugas'])) {
            $tab = 'wisatawan';
        }

        $jumlahWisatawan = User::where('role', 'wisatawan')->count();
        $jumlahPetugas = User::where('role', 'petugas')->count();

        $query = User::query()
            ->where('role', $tab)
            ->addSelect([
                'last_activity' => DB::table('sessions')
                    ->selectRaw('MAX(last_activity)')
                    ->whereColumn('sessions.user_id', 'users.id'),
            ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('activity_date')) {
            $date = $request->activity_date;

            $query->whereExists(function ($q) use ($date) {
                $q->select(DB::raw(1))
                    ->from('sessions')
                    ->whereColumn('sessions.user_id', 'users.id')
                    ->whereDate(
                        DB::raw('to_timestamp(sessions.last_activity)'),
                        $date
                    );
            });
        }

        $pengguna = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pengguna.index', compact(
            'pengguna',
            'tab',
            'jumlahWisatawan',
            'jumlahPetugas'
        ));
    }

    public function show(User $user)
    {
        return view('admin.pengguna.show', compact('user'));
    }

    public function updateRole(Request $request, User $user)
    {
        if (
    $user->role === 'admin' &&
    $user->is_active &&
    $validated['role'] !== 'admin' &&
    User::where('role', 'admin')
        ->where('is_active', true)
        ->count() <= 1
) {
    return back()->with(
        'error',
        'Admin aktif terakhir tidak dapat diubah role-nya.'
    );
}

        $validated = $request->validate([
            'role' => [
                'required',
                Rule::in(['admin', 'petugas', 'wisatawan']),
            ],
        ]);

        if (
            $user->role === 'admin' &&
            $validated['role'] !== 'admin' &&
            User::where('role', 'admin')->count() <= 1
        ) {
            return back()->with(
                'error',
                'Role admin terakhir tidak dapat diubah.'
            );
        }

        $oldRole = $user->role;

        $user->update([
            'role' => $validated['role'],
        ]);

        app(AuditLogService::class)->record(
    Auth::user(),
    'manajemen_pengguna',
    'update_role',
    'Mengubah role pengguna ' . $user->name,
    User::class,
    $user->id,
    ['role' => $oldRole],
    ['role' => $user->role]
);

        return back()->with('success', 'Role pengguna berhasil diperbarui.');
    }

    public function updateStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        if (
            $user->role === 'admin' &&
            $user->is_active &&
            User::where('role', 'admin')
                ->where('is_active', true)
                ->count() <= 1
        ) {
            return back()->with(
                'error',
                'Admin aktif terakhir tidak dapat dinonaktifkan.'
            );
        }

        $oldStatus = $user->is_active;

$user->update([
    'is_active' => !$oldStatus,
]);

app(AuditLogService::class)->record(
    Auth::user(),
    'manajemen_pengguna',
    'update_status',
    'Mengubah status akun pengguna ' . $user->name,
    User::class,
    $user->id,
    ['is_active' => $oldStatus],
    ['is_active' => $user->is_active]
);
        return back()->with(
            'success',
            $user->is_active
                ? 'Akun pengguna berhasil diaktifkan.'
                : 'Akun pengguna berhasil dinonaktifkan.'
        );
    }
}
