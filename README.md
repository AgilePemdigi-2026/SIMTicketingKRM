# SIMTicketingKRM (MangroTix)

Sistem Informasi Manajemen Tiket dan Reservasi Fasilitas **Kebun Raya Mangrove Surabaya**.

Proyek ini dikembangkan untuk memenuhi tugas mata kuliah **Pembangunan Perangkat Lunak (PPL) Praktikum** Kelas **I4**, Program Studi S1 Sistem Informasi, Universitas Airlangga.

- **Website Live:** https://mangrotix.caiis.ai.id/
- **Simulator Pembayaran QRIS (Midtrans Sandbox):** https://simulator.sandbox.midtrans.com/v2/qris/index

---

## Akun Pengujian (Live Demo)

Gunakan akun berikut untuk mencoba seluruh alur sesuai peran pengguna di https://mangrotix.caiis.ai.id/:

| Peran Pengguna | Halaman Login | Email | Kata Sandi |
| :--- | :--- | :--- | :--- |
| **Pembeli (Wisatawan)** | `/login` | `pembeli@gmail.com` | `vIsheb-pubgem-7desjy` |
| **Petugas Operasional** | `/petugas/login` | `petugas@gmail.com` | `rohgac-veWvok-woxwe1` |
| **Administrator** | `/admin/login` | `admin@gmail.com` | `xuvbIw-fuvvac-xyvci4` |

### Cara Menguji Pembayaran QRIS (Midtrans Sandbox)

1. Masuk menggunakan akun **Pembeli (Wisatawan)** di https://mangrotix.caiis.ai.id/login.
2. Buat pesanan baru pada menu **Tiket** (`/wisatawan/pembelian-tiket`) atau **Reservasi** (`/reservasi`).
3. Pada halaman pembayaran, pilih metode **QRIS** lalu tekan tombol pembuatan QRIS hingga kode QR tampil.
4. Salin URL gambar kode QRIS (klik kanan pada gambar QR lalu pilih *Copy Image Address*, atau gunakan tombol salin URL QRIS pada halaman reservasi).
5. Buka [Midtrans QRIS Sandbox Simulator](https://simulator.sandbox.midtrans.com/v2/qris/index), tempel URL gambar QRIS tersebut, klik **Scan QR**, lalu klik **Pay**.
6. Kembali ke halaman pembayaran aplikasi: status akan otomatis diperbarui menjadi **Lunas** (atau tekan tombol **Perbarui Status** / **Cek Status Pembayaran**).

---

## Fitur Utama Berdasarkan Peran

### 1. Wisatawan (Pembeli) & Pengunjung Publik
- **Informasi Kawasan:** Halaman Beranda, Destinasi, Detail Wahana, Berita & Artikel, Tentang Kami, dan Kontak pengelola.
- **Pembelian Tiket Online:** Pemilihan tanggal kunjungan, kuota tiket real-time, pengisian identitas pengunjung, pembayaran **QRIS Midtrans Sandbox** maupun **Cash Booking** (bayar tunai di loket dengan E-Tiket Sementara `CASH-xxxx`).
- **Penerbitan E-Tiket:** Kode QR unik untuk setiap pengunjung dalam satu pesanan serta unduhan dokumen **E-Tiket PDF**.
- **Reservasi Fasilitas / Venue:** Pemilihan fasilitas, pengecekan ketersediaan sesi per tanggal, checkout reservasi, pembayaran QRIS Midtrans Sandbox, **Invoice PDF**, dan pengajuan perubahan jadwal (*reschedule*).
- **Manajemen Akun & Riwayat:** Pengelolaan profil, ubah kata sandi, dan halaman **Pesanan Saya** yang memuat riwayat transaksi tiket serta reservasi fasilitas.

### 2. Petugas Operasional (`/petugas/dashboard`)
- **Dashboard Operasional Harian:** Ringkasan metrik kunjungan, tiket terjual, pembayaran lunas, dan aktivitas pengunjung hari berjalan.
- **Jual Tiket Langsung (`/petugas/loket`):** Transaksi pembelian tiket *walk-in* di loket dengan metode tunai (kalkulasi kembalian otomatis) atau QRIS serta cetak E-Tiket langsung.
- **Bayar Cash Booking (`/petugas/pembayaran`):** Pencarian kode `CASH-xxxx` atau nama pemesan untuk verifikasi dan konfirmasi pelunasan pesanan tunai di loket.
- **Scan & Validasi Tiket (`/petugas/validasi-tiket`):** Pemindaian kode QR E-Tiket menggunakan kamera maupun input kode manual untuk proses *check-in* pengunjung.
- **Riwayat Transaksi (`/petugas/riwayat-transaksi`):** Daftar transaksi lengkap beserta filter dan ekspor data transaksi.

### 3. Administrator (`/admin/dashboard`)
- **Monitoring Operasional Harian:** Pemantauan jumlah transaksi tiket, pembayaran lunas, total pendapatan tiket, pengunjung *check-in*, dan reservasi fasilitas harian.
- **Manajemen Master Data:** CRUD produk **Tiket** (`/admin/daftar-tiket`), **Wahana** (`/admin/wahana`), dan **Fasilitas** beserta jadwal sesi & tarif sewa (`/admin/fasilitas`).
- **Manajemen Reservasi:** Antarmuka pemantauan daftar reservasi dan pengajuan *reschedule* (`/admin/reservasi-preview`).
- **Manajemen Konten Portal:** Pengelolaan artikel berita (`/admin/konten-berita`), galeri foto (`/admin/konten-galeri`), informasi destinasi (`/admin/konten-destinasi`), dan kontak resmi (`/admin/konten-kontak`).

---

## Teknologi & Arsitektur

- **Backend:** PHP 8.3+ (PHP 8.5 pada container produksi), Laravel 13
- **Frontend:** Blade Templates, Tailwind CSS v4, Vite 8
- **Database:** PostgreSQL 17
- **Payment Gateway:** Midtrans Core API v2 (QRIS Sandbox, sinkronisasi status, dan verifikasi webhook)
- **QR Code & PDF:** `endroid/qr-code` (SVG & PNG) dan `barryvdh/laravel-dompdf`
- **Automated Testing:** PHPUnit (Feature & Unit Test) serta Behat (BDD skenario fitur)
- **Infrastruktur Deployment:** Docker Compose & Apache di VPS melalui **Coolify**

---

## Panduan Menjalankan di Lokal

### Prasyarat
- PHP `^8.3` beserta ekstensi `pdo_pgsql`, `gd`, `mbstring`, dan `openssl`
- Composer 2
- Node.js 20+ & npm
- Docker & Docker Compose (untuk menjalankan PostgreSQL 17 lokal)

### Langkah Instalasi

1. **Pasang dependensi PHP dan frontend:**
   ```bash
   composer install
   npm install
   ```

2. **Siapkan konfigurasi environment:**
   ```bash
   cp .env.postgres.example .env
   php artisan key:generate
   ```
   Sesuaikan kredensial database lokal (`DB_PORT=5433`, `DB_DATABASE=simticketingkrm`, `DB_USERNAME`, `DB_PASSWORD`) serta kunci Midtrans Sandbox (`MIDTRANS_IS_PRODUCTION=false`, `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`) di file `.env`.

3. **Jalankan container PostgreSQL lokal:**
   ```bash
   docker compose -f compose.local.yaml up -d
   ```

4. **Jalankan migrasi database dan symlink storage:**
   ```bash
   php artisan migrate
   php artisan storage:link
   ```
   *(Opsional)* Untuk mengisi data master awal di lingkungan lokal yang masih kosong, lihat panduan di `docs/local-postgresql.md` (`PostgreSqlDemoSeeder` dan `PostgreSqlRoleSeeder`).

5. **Jalankan server pengembangan:**
   ```bash
   composer run dev
   ```
   Atau jalankan terpisah dengan `npm run build` lalu `php artisan serve`. Aplikasi dapat diakses di `http://127.0.0.1:8000`.

---

## Pengujian Otomatis

Pastikan container PostgreSQL lokal sedang berjalan dan file `.env.postgres.local` tersedia (serta database pengujian `simticketingkrm_purchase_test` telah dibuat di PostgreSQL lokal), lalu jalankan:

```bash
php artisan test --compact
```

Seluruh rangkaian pengujian mencakup 262 test (CRUD master data admin, autentikasi & otorisasi role, checkout tiket, verifikasi pembayaran tunai & QRIS Midtrans, webhook tiket & reservasi, E-Tiket QR/PDF, loket petugas, serta reservasi fasilitas).

---

## Deployment Produksi (Coolify)

Aplikasi di-deploy menggunakan konfigurasi Docker Compose pada file `compose.coolify.yaml` dan `Dockerfile` dari branch `main`:

- **Build multi-stage:** Kompilasi aset Vite (`node:24-bookworm-slim`), instalasi Composer tanpa paket dev, dan runtime `php:8.5-apache-bookworm` dengan ekstensi `pdo_pgsql` serta `gd`.
- **Migrasi otomatis:** Saat container `web` dijalankan, perintah `php artisan migrate --force --no-interaction` dieksekusi otomatis setelah layanan `postgres` berstatus *healthy*.
- **Webhook Midtrans:** Endpoint `/webhooks/midtrans/ticket` dan `/webhooks/midtrans/reservation` dikecualikan dari CSRF dan memverifikasi integritas tanda tangan transaksi sebelum memperbarui status pembayaran.

---

## Dokumentasi Tambahan

- `docs/local-postgresql.md`: Panduan setup database PostgreSQL lokal dan seeder pengujian.
- `docs/ticket-purchase-postgresql.md`: Spesifikasi teknis modul pembelian tiket pada PostgreSQL.
- `docs/ticket-payment.md`: Dokumentasi alur pembayaran tiket, QRIS Midtrans, dan verifikasi petugas.
- `docs/bdd/`: Dokumentasi ketertelusuran kebutuhan dan bukti pengujian BDD.
