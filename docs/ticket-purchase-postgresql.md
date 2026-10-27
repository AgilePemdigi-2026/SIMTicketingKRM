# Pembelian Tiket dengan Master Dana dan PostgreSQL Lokal

Backend ini menggunakan master `daftar_tiket` dan menyimpan pesanan, detail,
serta pengunjung di PostgreSQL lokal. Frontend pembelian, pembayaran, E-Tiket,
QR, reservasi kuota, dan deployment aplikasi belum termasuk tahap ini.

## Menjalankan lokal

1. Jalankan Docker Desktop yang sudah terpasang.
2. Gunakan konfigurasi `.env` lokal sesuai `.env.postgres.example`, dengan
   `DB_HOST=127.0.0.1`, `DB_PORT=5433`, `DB_DATABASE=simticketingkrm`,
   `DB_CONNECTION=pgsql`, `DB_SSLMODE=disable`, dan `DB_URL` kosong.
   Isi password PostgreSQL lokal sendiri.
   Pertahankan APP_KEY aplikasi yang sudah digunakan.
3. Aktifkan `TICKET_MASTER_ENABLED=true`. Mode demo SQLite sudah dihapus.
4. Jalankan dari root project:

   ```sh
   docker compose -f compose.local.yaml up -d --wait
   php artisan migrate --database=pgsql --no-interaction
   php artisan serve --host=127.0.0.1 --port=8000
   ```

Migration pembelian memeriksa konfigurasi dan identitas PostgreSQL sebelum
membuat tiga tabel baru. Migration hanya berlaku untuk PostgreSQL. Jangan
menjalankan migrate:fresh/refresh pada data
yang sudah dipakai. Backend pembelian hanya menggunakan PostgreSQL lokal yang
diverifikasi; cadangan konfigurasi koneksi lama sudah dihapus dari laptop ini.

Jika `.env` utama belum dipindah ke lokal tetapi `.env.postgres.local` sudah siap,
Compose dapat menggunakan `--env-file .env.postgres.local`. Pilihan ini hanya
mengatur Compose, bukan otomatis mengubah environment Laravel.

Untuk menampilkan halaman Blade existing, jalankan Vite jika build asset belum ada.
Tidak ada perubahan pada frontend pada implementasi ini.

## Akun testing

Gunakan seeder kelompok `PostgreSqlRoleSeeder`, bukan membuat autentikasi baru.
Isi `PG_DEMO_ADMIN_PASSWORD`, `PG_DEMO_WISATAWAN_PASSWORD`, dan
`PG_DEMO_PETUGAS_PASSWORD` pada `.env` pribadi dengan password yang disepakati.
Jangan mengisi atau commit password pada template.

Sebelum menjalankan seeder, periksa apakah tabel users sudah berisi pengguna.
Seeder kelompok akan membuat akun hanya jika tabel kosong, atau melewati akun
yang sudah cocok; konflik data tidak diselesaikan dengan menghapus pengguna.

```sh
php artisan db:seed --class=PostgreSqlRoleSeeder --no-interaction
```

| Role | Email |
| --- | --- |
| Admin | admin.testing@example.com |
| Wisatawan | tester.wisatawan@example.com |
| Petugas | petugas.testing@example.com |

Login melalui halaman existing. Hanya wisatawan dapat menggunakan endpoint
pembelian. Jika password environment belum terisi, seeding akun tidak dilakukan.

## Endpoint dan payload

| Endpoint | Perilaku |
| --- | --- |
| GET /wisatawan/pembelian-tiket | Katalog master aktif, tarif dan relasi wahana |
| POST /wisatawan/pembelian-tiket | 201 setelah pesanan tersimpan atomik |
| GET /wisatawan/pembelian-tiket/{code} | Detail pemilik; 404 untuk pengguna lain |

Gunakan `Accept: application/json`. POST menggunakan session login dan token CSRF
dari session yang sama. Tanpa token yang valid, respons 419. Guest menerima 401
pada request JSON; Admin/Petugas menerima 403. Database tidak tersedia menghasilkan
503, tanpa beralih ke sumber tarif atau database pengganti.

Contoh payload untuk tiket master ID 5 (gunakan tanggal hari ini/mendatang WIB):

```json
{
  "visit_date": "2026-12-01",
  "visitor_type": "domestik",
  "booker": {
    "name": "Pemesan Lokal",
    "identity_number": "LOKAL-PEMESAN-01",
    "phone": "080000000004",
    "email": "pemesan@example.com"
  },
  "items": [{
    "ticket_reference": "master:5",
    "adult_quantity": 1,
    "child_quantity": 1,
    "visitors": [
      {"name": "Dewasa Lokal", "identity_number": "LOKAL-01", "age_category": "adult"},
      {"name": "Anak Lokal", "identity_number": "LOKAL-02", "age_category": "child"}
    ]
  }]
}
```

Respons memuat `is_dummy: false`, `currency: IDR`, dan `order`. Pada snapshot
kelompok ID 5 berharga dewasa 20000.00 dan anak 10000.00, sehingga total contoh
30000.00. Gunakan harga hasil GET terbaru, bukan menganggap snapshot tidak berubah.
Seluruh field uang pada hasil checkout/detail berupa string desimal dua digit,
misalnya `"30000.00"`. Ini perubahan kontrak nominal dari hasil integer demo lama.
Perhitungan menggunakan integer satuan terkecil; tidak memakai float.

Percobaan dari browser tanpa menambah frontend:

1. Login sebagai wisatawan di server lokal, lalu buka halaman profil.
2. Di Developer Tools Console, jalankan request GET katalog:

   ```js
   await fetch('/wisatawan/pembelian-tiket', {
     headers: { Accept: 'application/json' }, credentials: 'same-origin'
   }).then(response => response.json())
   ```

3. Untuk checkout, isi variabel `payload` dengan contoh di atas, lalu:

   ```js
   await fetch('/wisatawan/pembelian-tiket', {
     method: 'POST', credentials: 'same-origin',
     headers: {
       Accept: 'application/json', 'Content-Type': 'application/json',
       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
     },
     body: JSON.stringify(payload)
   }).then(response => response.json())
   ```

Gunakan data fiktif untuk demo dan lakukan ini hanya pada aplikasi lokal.

## Aturan dan batas integrasi

- Batas 1–4 unit berlaku lintas seluruh item; tanggal mengikuti Asia/Jakarta.
- Nama dan identitas pengunjung wajib, termasuk anak. Jumlah adult/child harus
  tepat per item. Pemesan tidak wajib ikut dan identitas yang sama boleh
  muncul pada kategori tiket berbeda, mengikuti aturan demo sebelumnya.
- `domestik` cocok dengan label master `Domestik` atau `Domestik (WNI)`;
  `mancanegara` cocok dengan `Mancanegara (WNA)`. Tarif yang tidak ada ditolak.
- Nama/kategori/harga mengikuti master. Kategori legacy `Reguler` dipertahankan.
  Relasi langsung dan pivot wahana digabung tanpa biaya wahana tambahan.
- Harga nol yang benar-benar berasal dari master diizinkan selama total pesanan
  positif. Tarif hilang, negatif, pecahan melebihi dua digit, atau invalid ditolak.
  Belum ada aturan pembebasan berdasarkan usia.
- Pemilik dan status ditentukan backend. Harga/total/status/user_id input diabaikan.
- Pesanan berstatus `pending_payment`, dengan UUID unik dan snapshot independen.
- Master tidak diubah saat checkout. Kuota ditampilkan pada master tetapi tidak
  dikurangi/direservasi; pengamanan stok produksi belum termasuk tahap ini.
- Tiga tabel transaksi mempunyai FK internal, tanpa FK ke master Dana. Penghapusan
  master tidak menghapus snapshot pesanan. Model transaksi memakai koneksi default
  yang diverifikasi, bukan sqlite yang dipatok pada kode.
- Pengaman tahap ini membatasi master ke PostgreSQL lokal. Deployment server dan
  kebijakan host/credential production perlu pekerjaan terpisah.
- Katalog, seeder, migration, route, command setup, environment dan model demo
  SQLite sudah dihapus. Tidak ada fallback ke katalog pengganti. `is_dummy: false`
  dipertahankan pada JSON agar kontrak respons existing tetap konsisten.
- Konfigurasi SQLite bawaan Laravel dan isolasi tes validasi awal tetap tersedia;
  keduanya tidak menjalankan demo atau menyimpan pembelian pada SQLite.

## Pengujian

Seluruh tes modul pembelian (PostgreSQL Docker harus berjalan):

```sh
vendor/bin/phpunit --do-not-cache-result tests/Feature/Wisatawan tests/Feature/TicketPurchaseDatabaseTest.php tests/Feature/TicketRoutesIsolationTest.php tests/Unit/TicketPurchaseServiceTest.php
```

Pengujian PostgreSQL menggunakan credential **lokal** pada `.env.postgres.local`,
tetapi nama database dipatok `simticketingkrm_purchase_test`. Database aplikasi
`simticketingkrm`, SQLite, host/port lain, dan connection URL ditolak oleh pengaman testing.
Siapkan database test baru hanya jika belum ada:

```sh
docker compose --env-file .env.postgres.local -f compose.local.yaml exec -T postgres psql -U postgres -d postgres -tAc "SELECT datname FROM pg_database WHERE datname = 'simticketingkrm_purchase_test'"
# Jalankan berikut hanya jika hasil query kosong:
docker compose --env-file .env.postgres.local -f compose.local.yaml exec -T postgres createdb -U postgres simticketingkrm_purchase_test
vendor/bin/phpunit --do-not-cache-result tests/Feature/Wisatawan/PostgresTicketPurchaseTest.php
```

Setiap tes memverifikasi target sebelum migration, kemudian membungkus fixture
dan checkout dalam transaction yang di-rollback pada akhir tes. Struktur database
test dipertahankan; tidak ada migrate:fresh atau penghapusan database. Password
fixture tes bukan password akun demo.

Master pengujian sekarang berasal langsung dari `PostgreSqlDemoSeeder` milik
kelompok: 3 tiket snapshot dan relasinya, bukan katalog dummy SQLite atau katalog
pengganti. Seeder anggota lain tidak diubah. Konteks environment `local` digunakan
hanya selama pemanggilan seeder existing setelah PDO database test diverifikasi,
lalu dikembalikan ke `testing`. Variasi nonaktif/harga nol/mancanegara pada kasus
negatif hanya perubahan sementara atas record master di dalam transaction tes.

Tes mencakup adapter master, mapping label, tarif desimal/gratis, manipulasi harga,
pengunjung, login tiga role, ownership, rollback insert kedua, CSRF aktif, dan
penolakan target/kegagalan koneksi. Tidak ada klaim pengujian frontend/Playwright.

## File utama yang berubah

Adapter `MasterTicketCatalogProvider`, pengaman `TicketPurchaseDatabase`, helper
`TicketMoney`, middleware master, route `ticket-purchase`, dan migration transaksi
PostgreSQL ditambahkan. Service/provider/controller dan tiga model transaksi
disesuaikan. Template PostgreSQL dan aturan gitignore diperbarui. Authentication,
model/controller master Dana, profil Diandra, dan 13 migration sebelumnya tidak
diubah. Gunakan panduan ini untuk backend master PostgreSQL; panduan demo SQLite
lama sudah dihapus.

Tidak ada commit/push otomatis. Penghapusan gambar lokal yang sudah ada sebelum
pekerjaan ini tidak termasuk perubahan modul pembelian.

## Hasil verifikasi integrasi sebelum pembersihan pada 27 Oktober 2026

- Regresi dan integrasi yang ditargetkan: **132 tests, 577 assertions, lulus**.
  Termasuk 51 tes validasi lama yang file request/test-nya tidak berubah.
- Pint dan syntax check seluruh file PHP baru/berubah lulus; git diff --check lulus.
- Migration pembelian berstatus Ran pada batch 2. Tiga belas migration master lama
  tetap pada batch 1. Total sekarang 17 tabel PostgreSQL aplikasi/infrastruktur.
- Katalog utama tetap 3 tiket snapshot kelompok. Tabel pesanan/pengunjung utama
  masih kosong; data pengujian tidak dimasukkan ke database aplikasi.
- Database simticketingkrm_purchase_test memiliki schema yang diuji, tetapi seluruh
  fixture users/master/pesanan/pengunjung sudah di-rollback setelah suite selesai.
- APP_KEY aktif dipertahankan saat mengganti koneksi database.
- **20 pemeriksaan HTTP nyata melalui terminal lulus**, memakai session dan CSRF
  Laravel asli pada server terpisah dengan database test. Katalog berasal dari
  seeder master kelompok; akun/transaksi sintetis hanya untuk pengujian tersebut.
- Query PostgreSQL dari koneksi terpisah membuktikan commit HTTP: pesanan
  pending_payment 30000.00, satu item master:5 (20000.00 dewasa + 10000.00 anak),
  dan dua pengunjung. Kegagalan insert pengunjung kedua melalui trigger khusus
  database test menghasilkan 500 dan tidak menambah baris transaksi.
- Setelah pengujian HTTP, hanya baris dan trigger yang dibuat oleh run tersebut
  dibersihkan. Runner menolak database test yang sudah berisi data sebelum mulai.
  Tidak ada penghapusan data existing atau volume. Database test kembali kosong.
- Tiga akun demo permanen belum dibuat karena PG_DEMO_*_PASSWORD masih kosong.
  Keberhasilan HTTP di atas menggunakan akun sintetis dengan credential sementara
  dalam memori, bukan pengganti password kelompok dan tidak mencetak credential.

File baru:

```text
app/Http/Middleware/EnsureMasterTicketDatabase.php
app/Services/MasterTicketCatalogProvider.php
app/Support/TicketMoney.php
app/Support/TicketPurchaseDatabase.php
database/migrations/2026_10_27_000001_create_ticket_purchase_tables.php
routes/ticket-purchase.php
tests/Feature/Wisatawan/PostgresTicketPurchaseTest.php
tests/Support/PostgresTicketTestCase.php
docs/ticket-purchase-postgresql.md
```

File tracked yang disesuaikan:

```text
.env.postgres.example
.gitignore
app/Http/Controllers/Wisatawan/TicketPurchaseController.php
app/Models/TicketOrder.php
app/Models/TicketOrderItem.php
app/Models/TicketOrderVisitor.php
app/Providers/TicketPurchaseServiceProvider.php
app/Services/TicketPurchaseService.php
config/ticket_purchase.php
tests/Unit/TicketPurchaseServiceTest.php
```

## Pembersihan demo SQLite pada 27 Oktober 2026

- `develop` diperbarui dengan `git pull --ff-only origin develop` dari `5879e42`
  ke `03a7bc0` (pembaruan Admin rekan kelompok), tanpa konflik dan tanpa commit/push.
  File hasil pull tidak diedit selama pembersihan modul pembelian.
- Dihapus: `.env.ticket-local.example`, `.env.ticket-local` privat, command
  `SetupLocalTickets`, middleware `EnsureLocalTicketDatabase`, model `LocalDummyTicket`
  dan `LocalDummyTicketRate`, `LocalTicketCatalogProvider`, `LocalTicketDatabase`,
  `LocalTicketSeeder`, migration di `database/local/migrations`, `routes/ticket-local.php`,
  `docs/ticket-local.md`, `LocalTicketTestCase`, serta `LocalTicketDatabaseTest`.
- Tidak ditemukan file `database/simticketingkrm_local.sqlite` maupun sidecar atau
  direktori session/cache demo. Tidak ada database PostgreSQL atau volume yang dihapus.
- Flag dan password khusus demo dihapus dari konfigurasi pembelian; key lama pada
  `.env` aktif dan `.env.postgres.local` dihapus tanpa mengubah koneksi atau credential
  PostgreSQL. Aturan gitignore lama tetap melindungi file privat sisa laptop anggota.
- `TicketCheckoutTest` dipindahkan ke fixture PostgreSQL kelompok untuk mempertahankan
  pengujian jumlah tiket, identitas pemesan, dan kecocokan pengunjung. Pengujian
  login/ownership/harga/rollback tetap pada `PostgresTicketPurchaseTest`.
- `TicketPurchaseServiceTest` menggunakan fake provider dalam memori tanpa schema,
  seeder, atau database SQLite. Tes pengaman baru `TicketPurchaseDatabaseTest`
  menolak target salah sebelum koneksi dibuka.
- Hasil setelah pembersihan: **113 tes, 503 assertions lulus**, termasuk seluruh
  51 tes validasi awal. Jumlah berbeda dari hasil integrasi historis karena tes
  seeder/isolasi SQLite dan kasus duplikat dihapus, sementara pengujian checkout
  dipindahkan ke PostgreSQL dan pengaman target PostgreSQL ditambahkan.
- **20 pemeriksaan HTTP nyata diulang dan lulus** dengan session/CSRF existing:
  login tiga role, katalog master, checkout, validasi, ownership, manipulasi nilai,
  dan rollback. Query koneksi terpisah melihat 1 pesanan `pending_payment`
  `30000.00`, 1 item `master:5`, dan 2 pengunjung. Kegagalan insert kedua menghasilkan
  500 dengan jumlah header/item/pengunjung tetap `[2,3,6]`, tanpa baris parsial.
- Fixture dan trigger HTTP khusus run dibersihkan; database pengujian kembali
  tanpa pesanan/item/pengunjung. Database aplikasi tetap memakai `pgsql` pada
  `127.0.0.1:5433`, 3 tiket master aktif, dan 0 pesanan. Tidak ada migration baru
  atau migration ulang yang diterapkan selama pembersihan.
- Syntax check 19 file PHP serta Pint dan `git diff --check` lulus. Konfigurasi
  database/testing bawaan Laravel milik project tidak diubah; tidak ada perubahan
  tambahan pada authentication, CRUD Dana, frontend, atau modul anggota lain.

File .env aktif dan cadangannya merupakan konfigurasi privat, bukan file commit.
