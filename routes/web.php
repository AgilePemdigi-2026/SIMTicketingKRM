<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Wisatawan\ProfileController;
use App\Http\Controllers\Admin\WahanaController as AdminWahanaController;
use App\Http\Controllers\Admin\DaftarTiketController;
use App\Http\Controllers\Admin\FasilitasController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Wisatawan\WahanaController;
use App\Http\Controllers\Wisatawan\BeritaController;

// Home Publik bagi Pengguna Publik (Tidak Login)
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

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

    // Login Admin
    Route::get('/admin/login', function () {
        return view('auth.portal-login', [
            'portalRole' => 'admin',
        ]);
    })->name('admin.login');

    // Login Petugas
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

    // Beranda Wisatawan — wajib login
    Route::get('/beranda', [HomeController::class, 'index'])
        ->middleware('role:wisatawan')
        ->name('wisatawan.beranda');

    // Dashboard Wisatawan
    Route::get('/wisatawan/dashboard', function () {
        return redirect()->route('wisatawan.beranda');
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

    // Halaman Destinasi
    Route::get(
        '/destinasi',
        [\App\Http\Controllers\Wisatawan\DestinasiController::class, 'index']
    )->middleware('role:wisatawan')
    ->name('wisatawan.destinasi');

    // Detail informasi wahana
    Route::get('/wahana/{slug}', [
        WahanaController::class, 'show'
    ])->where('slug', '[a-z0-9-]+')
    ->middleware('role:wisatawan')
    ->name('wahana.show');

    // Daftar Berita & Artikel
    Route::get('/berita', [BeritaController::class, 'index'])
        ->middleware('role:wisatawan')
        ->name('wisatawan.berita.index');

    // Detail Artikel
    Route::get('/berita/{slug}', [BeritaController::class, 'show'])
        ->where('slug', '[a-z0-9-]+')
        ->middleware('role:wisatawan')
        ->name('wisatawan.berita.show');

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


    // Master Data Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('wahana', AdminWahanaController::class)
            ->except(['show']);

        Route::resource('daftar-tiket', DaftarTiketController::class)
            ->parameters(['daftar-tiket' => 'daftarTiket'])
            ->except(['show']);

        Route::patch('/fasilitas/{fasilitas}/toggle-status', [
            FasilitasController::class,
            'toggleStatus',
        ])->name('fasilitas.toggle-status');

        Route::resource('fasilitas', FasilitasController::class)
            ->parameters(['fasilitas' => 'fasilitas'])
            ->except(['show']);
    });

});
