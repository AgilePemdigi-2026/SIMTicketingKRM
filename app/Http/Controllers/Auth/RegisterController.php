<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'identity_number' => ['required', 'string', 'max:50', 'unique:users,identity_number'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'visitor_type' => ['required', 'string', Rule::in(['domestik', 'mancanegara'])],
            'terms' => ['accepted'],
        ]);

        User::create([
            'name' => $validated['name'],
            'identity_number' => $validated['identity_number'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'visitor_type' => $validated['visitor_type'],
        ]);

        return redirect()->route('login')
            ->with('success', 'Pendaftaran berhasil! Silakan login.');
    }
}
