
# PostgreSQL Lokal — SIMTicketingKRM

## Teknologi
- Laravel
- PostgreSQL 17
- Docker Desktop
- Docker Compose

## Konfigurasi Database
- DB_CONNECTION: pgsql
- DB_HOST: 127.0.0.1
- DB_PORT: 5433
- DB_DATABASE: simticketingkrm
- DB_USERNAME: postgres
- DB_PASSWORD: password lokal masing-masing

## Status Migrasi
Database utama telah berhasil disalin dari
Supabase PostgreSQL 17 ke PostgreSQL lokal.

Backup dan restore mencakup schema public,
termasuk tabel, data, sequence, dan constraint.

Pengujian awal berhasil untuk:
- Login Wisatawan, Admin, dan Petugas
- Logout dan edit profil Wisatawan
- Menampilkan Master Data Tiket

## Catatan Penting
1. File .env tidak boleh di-commit.
2. Backup database tidak boleh diunggah ke GitHub.
3. Database lokal setiap developer berdiri sendiri.
4. Perubahan Supabase tidak otomatis tersinkronisasi.
5. SQLite demo pembelian tiket masih perlu
   disesuaikan dengan master data PostgreSQL.
6. Container PostgreSQL saat ini dibuat manual.
   Jangan menjalankan Docker Compose bersamaan
   pada port 5433 tanpa penyesuaian.
7. File Compose hanya menyiapkan PostgreSQL.
   Struktur dan data aplikasi perlu diinisialisasi
   atau dipulihkan secara terpisah.


## Panduan Awal Setup Anggota Kelompok

Status: panduan ini masih perlu diuji pada
lingkungan developer baru.

1. Instal Docker Desktop, PHP, Composer, dan Node.js
   sesuai kebutuhan project Laravel.

2. Ambil kode project dari GitHub.

3. Salin file `.env.postgres.example` menjadi `.env`.

4. Isi `DB_PASSWORD` di `.env` dengan password
   PostgreSQL lokal masing-masing.
   Jangan mengunggah `.env` ke GitHub.

5. Pastikan port 5433 belum digunakan container
   atau aplikasi lain.

6. Jalankan PostgreSQL menggunakan:

   docker compose -f compose.local.yaml up -d

7. Tunggu hingga PostgreSQL siap digunakan.

8. Database yang baru dibuat belum otomatis berisi
   tabel dan data hasil migrasi dari Supabase.
   Struktur database perlu disiapkan menggunakan
   migrasi Laravel atau restore backup yang aman.

9. Jalankan aplikasi Laravel setelah dependensi,
   APP_KEY, dan database selesai disiapkan.


## Keamanan Data Docker

- Jangan menjalankan Docker Compose pada port 5433
  jika sudah ada container yang menggunakan port itu.
- Jangan menjalankan `docker compose down -v`
  sembarangan karena dapat menghapus volume database.
- Backup yang berisi data pengguna tidak boleh
  dibagikan tanpa izin dan pemeriksaan keamanan.
- Database Supabase lama tidak otomatis tersinkronisasi
  dengan PostgreSQL lokal.
