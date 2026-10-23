
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
   