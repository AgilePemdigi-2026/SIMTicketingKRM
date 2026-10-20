<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

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
