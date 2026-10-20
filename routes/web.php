<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Wisatawan\ProfileController;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Route untuk pengguna yang belum login
Route::middleware('guest')->group(function () {

    // Register
    Route::get('/register', [RegisterController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisterController::class, 'store'])
        ->name('register.store');

    // Login
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');
        
    // Halaman Login Admin
    Route::get('/admin/login', function () {
        return view('auth.portal-login', [
            'portalRole' => 'admin',
        ]);
    })->name('admin.login');

    // Halaman Login Petugas
    Route::get('/petugas/login', function () {
        return view('auth.portal-login', [
            'portalRole' => 'petugas',
        ]);
    })->name('petugas.login');
});

// Route untuk pengguna yang sudah login
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'destroy'])
        ->name('logout');

    // Dashboard Wisatawan
    Route::get('/wisatawan/dashboard', function () {
        return view('wisatawan.dashboard');
    })->middleware('role:wisatawan')
      ->name('wisatawan.dashboard');

    // Profil Wisatawan
    Route::get('/wisatawan/profil', [
        ProfileController::class, 'show'
    ])->middleware('role:wisatawan')
      ->name('wisatawan.profil');
    
    // Form Edit Profil Wisatawan
    Route::get('/wisatawan/profil/edit', [
        ProfileController::class, 'edit'
    ])->middleware('role:wisatawan')
      ->name('wisatawan.profil.edit');
    
    // Halaman Ubah Password Wisatawan
    Route::get('/wisatawan/profil/password', [
        ProfileController::class, 'editPassword'
    ])->middleware('role:wisatawan')
      ->name('wisatawan.password.edit');

    // Simpan Password Baru
    Route::put('/wisatawan/profil/password', [
        ProfileController::class, 'updatePassword'
    ])->middleware('role:wisatawan')
      ->name('wisatawan.password.update');

    // Simpan Perubahan Profil
    Route::put('/wisatawan/profil', [
        ProfileController::class, 'update'
    ])->middleware('role:wisatawan')
      ->name('wisatawan.profil.update');

    // Dashboard Petugas
    Route::get('/petugas/dashboard', function () {
        return 'Dashboard Petugas - SIMTicketingKRM';
    })->middleware('role:petugas')
      ->name('petugas.dashboard');

    // Dashboard Admin
    Route::get('/admin/dashboard', function () {
        return 'Dashboard Admin - SIMTicketingKRM';
    })->middleware('role:admin')
      ->name('admin.dashboard');

});
