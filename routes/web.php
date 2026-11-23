<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Wisatawan\ProfileController;
use App\Http\Controllers\Admin\WahanaController as AdminWahanaController;
use App\Http\Controllers\Admin\DaftarTiketController;
use App\Http\Controllers\Admin\FasilitasController;
use App\Http\Controllers\Admin\KontenBeritaController;
use App\Http\Controllers\Admin\KontenGaleriController;
use App\Http\Controllers\Admin\InfoDestinasiController;
use App\Http\Controllers\Admin\InfoKontakController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Wisatawan\WahanaController;
use App\Http\Controllers\Wisatawan\BeritaController;
use App\Http\Controllers\Wisatawan\FacilityReservationController;
use App\Http\Controllers\Petugas\TransactionHistoryController;

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

    // Halaman Tentang Kebun Raya Mangrove
    Route::get(
        '/tentang',
        [\App\Http\Controllers\Wisatawan\TentangController::class, 'index']
    )->middleware('role:wisatawan')
      ->name('wisatawan.tentang');

    // Halaman Kontak Wisatawan
    Route::get(
        '/kontak',
        [\App\Http\Controllers\Wisatawan\KontakController::class, 'index']
    )->middleware('role:wisatawan')
      ->name('wisatawan.kontak');

    // Pengiriman pesan dari halaman Kontak ke Gmail.
    Route::post(
        '/kontak/kirim-pesan',
        [\App\Http\Controllers\Wisatawan\KontakController::class, 'kirimPesan']
    )->middleware(['role:wisatawan', 'throttle:3,10'])
      ->name('wisatawan.kontak.kirim');

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

    // Halaman daftar dan pemilihan fasilitas
    Route::get('/reservasi', [
        \App\Http\Controllers\Wisatawan\ReservasiPageController::class,
        'index'
    ])->middleware('role:wisatawan')
      ->name('wisatawan.reservasi.index');

    Route::get('/reservasi/ketersediaan', [FacilityReservationController::class, 'availability'])
        ->middleware('role:wisatawan')
        ->name('wisatawan.reservasi.availability');

    Route::post('/reservasi/checkout', [FacilityReservationController::class, 'store'])
        ->middleware('role:wisatawan')
        ->name('wisatawan.reservasi.checkout');

    Route::get('/reservasi/transaksi/{code}', [FacilityReservationController::class, 'invoice'])
        ->whereUuid('code')
        ->middleware('role:wisatawan')
        ->name('wisatawan.reservasi.invoice');

    // Form pengajuan Reschedule berdasarkan reservasi asli.
    Route::get(
        '/reservasi/transaksi/{code}/reschedule',
        [\App\Http\Controllers\Wisatawan\FacilityReservationRescheduleController::class, 'create']
    )
        ->whereUuid('code')
        ->middleware('role:wisatawan')
        ->name('wisatawan.reservasi.reschedule.create');

    // Simpan pengajuan Reschedule dari Wisatawan.
    Route::post(
        '/reservasi/transaksi/{code}/reschedule',
        [\App\Http\Controllers\Wisatawan\FacilityReservationRescheduleController::class, 'store']
    )
        ->whereUuid('code')
        ->middleware('role:wisatawan')
        ->name('wisatawan.reservasi.reschedule.store');

    // Reservasi - Pilih Tanggal dan Waktu
    Route::get('/reservasi/{fasilitas}/tanggal', [
        \App\Http\Controllers\Wisatawan\ReservasiPageController::class,
        'tanggal'
    ])->whereNumber('fasilitas')
      ->middleware('role:wisatawan')
      ->name('wisatawan.reservasi.tanggal');

    // Reservasi - Data Reservasi
    Route::get('/reservasi/{fasilitas}/data', [
        \App\Http\Controllers\Wisatawan\ReservasiPageController::class,
        'data'
    ])->whereNumber('fasilitas')
      ->middleware('role:wisatawan')
      ->name('wisatawan.reservasi.data');

    // Sprint 3 - Pratinjau UI Pembayaran Reservasi
    Route::get('/reservasi/{fasilitas}/pratinjau-pembayaran', [
        \App\Http\Controllers\Wisatawan\ReservasiPageController::class,
        'previewPembayaran'
    ])->whereNumber('fasilitas')
      ->middleware('role:wisatawan')
      ->name('wisatawan.reservasi.pembayaran.preview');

    // Sprint 3 - Pratinjau Desain Invoice Reservasi
    Route::get('/reservasi/{fasilitas}/pratinjau-invoice', [
        \App\Http\Controllers\Wisatawan\ReservasiPageController::class,
        'previewInvoice'
    ])->whereNumber('fasilitas')
      ->middleware('role:wisatawan')
      ->name('wisatawan.reservasi.invoice.preview');

    // Sprint 3 - Pratinjau UI Reschedule Reservasi
    Route::get('/reservasi/{fasilitas}/pratinjau-reschedule', [
        \App\Http\Controllers\Wisatawan\ReservasiPageController::class,
        'previewReschedule'
    ])->whereNumber('fasilitas')
      ->middleware('role:wisatawan')
      ->name('wisatawan.reservasi.reschedule.preview');

    // Dashboard Petugas
    Route::get(
        '/petugas/dashboard',
        [\App\Http\Controllers\Petugas\PetugasDashboardController::class, 'index']
    )
        ->middleware([
            'role:petugas',
            \App\Http\Middleware\EnsureMasterTicketDatabase::class,
        ])
        ->name('petugas.dashboard');

    // Loket pembelian tiket Petugas
    Route::get('/petugas/loket', [\App\Http\Controllers\Petugas\TicketCounterController::class, 'index'])
        ->middleware([
            'auth',
            'role:petugas',
            \App\Http\Middleware\EnsureMasterTicketDatabase::class])
        ->name('petugas.loket.index');

    Route::post('/petugas/loket/preview', [\App\Http\Controllers\Petugas\TicketCounterController::class, 'preview'])
        ->middleware([
            'auth',
            'role:petugas',
            \App\Http\Middleware\EnsureMasterTicketDatabase::class,
        ])
        ->name('petugas.loket.preview');

    Route::post('/petugas/loket/checkout', [\App\Http\Controllers\Petugas\TicketCounterController::class, 'checkout'])
        ->middleware(['role:petugas', \App\Http\Middleware\EnsureMasterTicketDatabase::class])
        ->name('petugas.loket.checkout');

    // Halaman transaksi berhasil untuk pembelian loket.
    Route::get(
        '/petugas/loket/transaksi/{payment}/berhasil',
        [\App\Http\Controllers\Petugas\CounterTransactionController::class, 'success']
    )
        ->middleware([
            'role:petugas',
            \App\Http\Middleware\EnsureMasterTicketDatabase::class,
        ])
        ->whereNumber('payment')
        ->name('petugas.loket.success');

    // Cetak E-Tiket asli dari transaksi loket yang sudah lunas.
    Route::get(
        '/petugas/loket/transaksi/{payment}/cetak',
        [\App\Http\Controllers\Petugas\CounterTransactionController::class, 'print']
    )
        ->middleware([
            'role:petugas',
            \App\Http\Middleware\EnsureMasterTicketDatabase::class,
        ])
        ->whereNumber('payment')
        ->name('petugas.loket.print');

    Route::get('/petugas/validasi-tiket', [\App\Http\Controllers\Petugas\TicketValidationController::class, 'index'])
        ->middleware(['role:petugas', \App\Http\Middleware\EnsureMasterTicketDatabase::class])
        ->name('petugas.validasi-tiket.index');

    Route::post('/petugas/validasi-tiket/verify', [\App\Http\Controllers\Petugas\TicketValidationController::class, 'verify'])
        ->middleware(['role:petugas', \App\Http\Middleware\EnsureMasterTicketDatabase::class])
        ->name('petugas.validasi-tiket.verify');

    Route::post('/petugas/validasi-tiket/check-in', [\App\Http\Controllers\Petugas\TicketValidationController::class, 'checkIn'])
        ->middleware(['role:petugas', \App\Http\Middleware\EnsureMasterTicketDatabase::class])
        ->name('petugas.validasi-tiket.check-in');

    // Riwayat Transaksi Petugas
    Route::get(
        '/petugas/riwayat-transaksi',
        [TransactionHistoryController::class, 'index']
    )
        ->middleware([
            'role:petugas',
            \App\Http\Middleware\EnsureMasterTicketDatabase::class,
        ])
        ->name('petugas.riwayat-transaksi.index');

    // Export Riwayat Transaksi Petugas
    Route::get(
        '/petugas/riwayat-transaksi/export',
        [TransactionHistoryController::class, 'export']
    )
        ->middleware([
            'role:petugas',
            \App\Http\Middleware\EnsureMasterTicketDatabase::class,
        ])
        ->name('petugas.riwayat-transaksi.export');

    // Detail Riwayat Transaksi Petugas
    Route::get(
        '/petugas/riwayat-transaksi/{payment}',
        [TransactionHistoryController::class, 'show']
    )
        ->middleware([
            'role:petugas',
            \App\Http\Middleware\EnsureMasterTicketDatabase::class,
        ])
        ->whereNumber('payment')
        ->name('petugas.riwayat-transaksi.show');

    // Dashboard Admin - Monitoring Operasional Harian
    Route::get('/admin/dashboard', [
        \App\Http\Controllers\Admin\AdminDashboardController::class,
        'index'
    ])->middleware('role:admin')
      ->name('admin.dashboard');

    // TEMPORARY - Preview UI Manajemen Reservasi
    Route::get('/admin/reservasi-preview', function () {
        return view('admin.reservasi.index');
    })->middleware('role:admin')
    ->name('admin.reservasi.preview');

    // TEMPORARY - Preview UI Detail Reservasi
    Route::get('/admin/reservasi-preview/detail', function () {
        return view('admin.reservasi.show');
    })->middleware('role:admin')
    ->name('admin.reservasi.preview.detail');

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

        // Manajemen Konten - Berita & Artikel
        Route::resource('konten-berita', KontenBeritaController::class)
            ->parameters(['konten-berita' => 'konten'])
            ->except(['show']);
        
        // Manajemen Konten - Galeri Foto
        Route::resource('konten-galeri', KontenGaleriController::class)
            ->parameters(['konten-galeri' => 'konten'])
            ->except(['show']);
        
        // ==========================================
        // MANAJEMEN KONTEN - INFO DESTINASI
        // ==========================================

        // Menampilkan halaman pengelolaan Info Destinasi
        Route::get(
            'konten-destinasi',
            [InfoDestinasiController::class, 'index']
        )->name('konten-destinasi.index');

        // Menyimpan dan memperbarui Info Destinasi
        Route::put(
            'konten-destinasi',
            [InfoDestinasiController::class, 'update']
        )->name('konten-destinasi.update');

        // ==========================================
        // MANAJEMEN KONTEN - INFO KONTAK
        // ==========================================

        Route::get(
            'konten-kontak',
            [InfoKontakController::class, 'index']
        )->name('konten-kontak.index');

        Route::put(
            'konten-kontak',
            [InfoKontakController::class, 'update']
        )->name('konten-kontak.update');

    });

});
