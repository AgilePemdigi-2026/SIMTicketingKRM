<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('admin.profile.index', [
            'user' => $request->user(),
            'mode' => 'show',
        ]);
    }

    public function edit(Request $request)
    {
        return view('admin.profile.index', [
            'user' => $request->user(),
            'mode' => 'edit',
        ]);
    }

    public function update(
        Request $request,
        AuditLogService $auditLog
    ) {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:25',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
        ]);

        DB::transaction(function () use ($user, $validated, $auditLog) {
            $user->fill($validated);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            if ($user->isDirty()) {
                $user->save();

                $auditLog->record(
                    actor: $user,
                    module: 'Profil',
                    action: 'profile_updated',
                    description: 'Administrator memperbarui profil sendiri.',
                    subjectType: 'User',
                    subjectId: $user->getKey()
                );
            }
        });

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function editPassword(Request $request)
    {
        return view('admin.profile.index', [
            'user' => $request->user(),
            'mode' => 'password',
        ]);
    }

    public function updatePassword(
        Request $request,
        AuditLogService $auditLog
    ) {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:72',
                'different:current_password',
                'confirmed',
            ],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $validated, $auditLog) {
            // The User model hashes passwords automatically.
            $user->password = $validated['password'];
            $user->setRememberToken(Str::random(60));
            $user->save();

            $auditLog->record(
                actor: $user,
                module: 'Profil',
                action: 'password_updated',
                description: 'Administrator mengubah kata sandi akun sendiri.',
                subjectType: 'User',
                subjectId: $user->getKey()
            );
        });

        $request->session()->regenerate();

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Password berhasil diubah.');
    }
}
