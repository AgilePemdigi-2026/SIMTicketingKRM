# Backend Pembelian Tiket — demo SQLite lokal

Status: prototipe Sprint 1. Seluruh katalog dan harga bertanda dummy, bukan tarif
resmi Kebun Raya Mangrove atau schema Master Data Dana. Tidak ada pembayaran,
E-Tiket, QR, kuota, atau perubahan frontend.

## File dan tanggung jawab

- `.env.ticket-local.example`: template tanpa credential; salin ke
  `.env.ticket-local` yang diabaikan Git.
- `config/ticket_purchase.php`: flag demo dan pembacaan password environment.
- `app/Support/LocalTicketDatabase.php`: pemeriksaan environment, cache,
  konfigurasi koneksi, path tanpa symlink, driver PDO, foreign keys, dan target
  SQLite sebenarnya. Testing hanya menerima `:memory:`.
- `app/Console/Commands/SetupLocalTickets.php`: perintah setup non-destruktif.
- `app/Providers/TicketPurchaseServiceProvider.php` dan `bootstrap/providers.php`:
  registrasi terbatas pada demo/testing, isolasi koneksi/session/cache.
- `database/local/migrations/2026_10_20_000001_create_local_ticket_tables.php`:
  lima tabel lokal, terpisah dari migration production.
- `database/seeders/LocalTicketSeeder.php`: tiga akun dan katalog idempotent.
- `app/Models/LocalDummyTicket.php`, `LocalDummyTicketRate.php`, `TicketOrder.php`,
  `TicketOrderItem.php`, `TicketOrderVisitor.php`: model lokal dan relationship.
- `app/Contracts/TicketCatalogProvider.php`: kontrak sumber katalog dan tarif.
- `app/Services/LocalTicketCatalogProvider.php`: adapter katalog SQLite dummy.
- `app/Services/TicketPurchaseService.php`: kalkulasi tepercaya dan penyimpanan
  atomik header/item/pengunjung dengan `DB::transaction()`.
- `app/Http/Requests/CheckoutTicketPurchaseRequest.php`: mewarisi validasi dasar,
  menambahkan identitas dan kecocokan pengunjung. Request lama tidak diubah.
- `app/Http/Controllers/Wisatawan/TicketPurchaseController.php`,
  `app/Http/Middleware/EnsureLocalTicketDatabase.php`, `routes/ticket-local.php`:
  endpoint JSON demo dengan session, auth, role, dan CSRF.
- `tests/Support/LocalTicketTestCase.php`, `FakeTicketCatalogProvider.php`:
  boot terisolasi, fixture pengguna, dan fake tarif khusus tes.
- `tests/Feature/Wisatawan/TicketCheckoutTest.php`,
  `tests/Feature/LocalTicketDatabaseTest.php`,
  `tests/Feature/TicketRoutesIsolationTest.php`,
  `tests/Unit/TicketPurchaseServiceTest.php`: tes checkout, isolasi, dan harga.

## Menjalankan pada laptop anggota kelompok

Prasyarat: dependency Laravel project sudah terpasang dan PHP memiliki
`pdo_sqlite`. Tidak perlu server database atau dependency tambahan.

1. Pastikan menggunakan branch `feature/ticket-purchase` dan amankan pekerjaan
   lokal sendiri sebelum berpindah branch. Jangan menimpa `.env` Supabase.
2. Salin template `.env.ticket-local.example` ke `.env.ticket-local` **hanya jika
   file lokal belum ada**. Isi `DB_DATABASE` dengan path absolut:
   `<folder-project>/database/simticketingkrm_local.sqlite`. Hindari symlink.
3. Isi `LOCAL_ADMIN_PASSWORD`, `LOCAL_WISATAWAN_PASSWORD`,
   `LOCAL_PETUGAS_PASSWORD` dengan password akun yang telah disepakati Naufal.
   Tidak ada password asli dalam template, source code, atau dokumentasi ini.
4. Pastikan tidak ada `bootstrap/cache/config.php` atau route cache aktif.
   Setup akan menolak cache; jangan menghapus cache aplikasi kelompok tanpa
   memahami penggunaannya. Jangan menjalankan `config:cache`/`route:cache` pada
   workspace demo yang juga digunakan server utama.
5. Jika `APP_KEY` lokal masih kosong, buat key khusus demo:

   ```sh
   php artisan key:generate --env=ticket-local
   ```

6. Jalankan setup:

   ```sh
   php artisan ticket:local-setup --env=ticket-local
   ```

   Perintah memeriksa seluruh password sebelum membuat file/migration/seeding.
   File baru dibuat eksklusif jika belum ada. Migration hanya menjalankan yang
   belum diterapkan. Seeder mencocokkan email/kode tiket, tidak menghapus pesanan.
   Database existing tanpa riwayat migration ditolak.

7. Jalankan server lokal terpisah:

   ```sh
   php artisan serve --env=ticket-local --host=127.0.0.1 --port=8001
   ```

   Jangan gunakan port `8000` jika aplikasi Supabase masih berjalan di sana.
   Session cookie `simticketingkrm_ticket_local_session` dan file session/cache
   berada di `storage/framework/ticket-local/`. Browser berbagi cookie antarport
   pada host yang sama; nama cookie demo yang berbeda menjaga session login
   terpisah. Untuk request JSON gunakan token CSRF dari session demo yang sama.

## Akun lokal

| Role | Email |
| --- | --- |
| Admin | admin.testing@example.com |
| Wisatawan | tester.wisatawan@example.com |
| Petugas | petugas.testing@example.com |

Password berasal dari environment lokal dan disimpan sebagai hash Laravel.
Identitas/telepon seed seluruhnya fiktif. Semua akun seed memiliki jenis
wisatawan `domestik`; role menentukan akses. Seeder tidak dipanggil oleh
`DatabaseSeeder` production.

Login melalui `/login`, `/admin/login`, atau `/petugas/login` pada port `8001`.
Authentication/controller anggota lain tetap digunakan. Admin dan Petugas
dapat login tetapi tidak boleh mengakses endpoint pembelian wisatawan.

## Schema dan katalog

Migration bawaan menyediakan pengguna dan tabel infrastruktur Laravel.
Migration lokal menambah:

| Tabel | Isi/relasi |
| --- | --- |
| local_dummy_tickets | Kode unik, nama, kategori masuk/wahana, status aktif |
| local_dummy_ticket_rates | FK tiket dummy, jenis wisatawan, harga dewasa/anak; pasangan unik |
| ticket_orders | UUID unik, FK users, tanggal, jenis wisatawan, snapshot pemesan, total, pending_payment |
| ticket_order_items | FK pesanan, referensi sumber tiket tanpa FK master, snapshot nama/kategori/harga/quantity/subtotal |
| ticket_order_visitors | FK item, nama, nomor identitas string, kategori adult/child |

| Kode | Aktif | Domestik dewasa/anak | Mancanegara dewasa/anak |
| --- | --- | --- | --- |
| dummy:masuk | Ya | 25000 / 15000 | 50000 / 30000 |
| dummy:wahana | Ya | 20000 / 10000 | 40000 / 20000 |
| dummy:nonaktif | Tidak | 25000 / 15000 | 50000 / 30000 |

Semua nominal adalah integer rupiah dummy. Harga nol, negatif, pecahan,
hilang, atau tidak valid ditolak pada demo; aturan tiket gratis production
belum ditentukan. Tarif demo tidak berubah berdasarkan hari/tanggal.

## Kontrak endpoint

Ketiganya hanya terdaftar ketika `TICKET_LOCAL_ENABLED=true`, environment
`ticket-local`/`testing`, serta konfigurasi SQLite memenuhi pemeriksaan.
Middleware memverifikasi koneksi nyata saat endpoint diakses.

| Method dan URL | Hasil |
| --- | --- |
| GET /wisatawan/pembelian-tiket | 200, katalog aktif dan tarif, `is_dummy: true`, `currency: IDR` |
| POST /wisatawan/pembelian-tiket | 201, `order` berisi UUID, snapshot harga, items/visitors, status |
| GET /wisatawan/pembelian-tiket/{code} | 200 untuk pemilik; 404 untuk kode tidak ada atau milik orang lain |

Gunakan `Accept: application/json`. Guest mendapat 401 untuk request JSON,
role lain 403, input/tarif tidak valid 422. POST tanpa token CSRF valid mendapat
419. Jangan memakai nama route `tiket.index`; tombol frontend tetap menunggu
halaman pembelian yang belum masuk scope.

Contoh payload (ganti tanggal dengan hari ini atau tanggal mendatang WIB):

```json
{
  "visit_date": "2026-12-01",
  "visitor_type": "domestik",
  "booker": {
    "name": "Pemesan Demo",
    "identity_number": "DEMO-PEMESAN-01",
    "phone": "080000000004",
    "email": "pemesan@example.com"
  },
  "items": [
    {
      "ticket_reference": "dummy:masuk",
      "adult_quantity": 1,
      "child_quantity": 1,
      "visitors": [
        {"name": "Dewasa Demo", "identity_number": "DEMO-01", "age_category": "adult"},
        {"name": "Anak Demo", "identity_number": "DEMO-02", "age_category": "child"}
      ]
    }
  ]
}
```

Hasil contoh: total 40000, status `pending_payment`. Pemilik diambil dari
session. Nominal/status/user_id yang disisipkan browser tidak dipakai.

Untuk percobaan manual tanpa membuat frontend: login di port `8001`, buka
Developer Tools pada halaman tersebut, lalu gunakan `fetch` ke endpoint JSON
dengan `credentials: 'same-origin'`, header `Content-Type: application/json`,
`Accept: application/json`, dan `X-CSRF-TOKEN` dari
`document.querySelector('meta[name="csrf-token"]').content`.
Kirim payload di atas sebagai JSON. Simpan kode respons untuk GET detail.
Halaman profil existing memakai layout yang menyediakan meta CSRF.
Jangan menjalankan request demo pada tab port `8000`.

Aturan demo: satu pengunjung per unit tiket; jumlah adult/child per item harus
tepat; batas 1–4 berlaku lintas semua item. Pemesan tidak wajib ikut, identitas
anak wajib diisi, dan orang yang sama boleh muncul pada beberapa item. Jenis
wisatawan dipilih per transaksi dan tidak mengubah profil. Nomor identitas
bukan otomatis NIK 16 digit. Kebijakan kelayakan tarif dan identitas production
masih memerlukan kesepakatan kelompok.

## Pengujian terisolasi

```sh
vendor/bin/phpunit --do-not-cache-result tests/Feature/Wisatawan tests/Feature/LocalTicketDatabaseTest.php tests/Feature/TicketRoutesIsolationTest.php tests/Unit/TicketPurchaseServiceTest.php
```

Tes baru mematok koneksi SQLite `:memory:` sebelum service provider boot dan
menghapus alternatif koneksi dari konfigurasi proses testing. Pengujian lama
51 kasus tetap memakai konfigurasi dan kode existing. Password fixture testing
dibuat sementara di memori; bukan password tiga akun demo. Test tidak memakai
file demo dan tidak membaca/menulis database Supabase.

Tes rollback sengaja menggagalkan insert pengunjung kedua setelah header,
item, dan pengunjung pertama ditulis. Tes CSRF menonaktifkan bypass testing
Laravel untuk memeriksa penolakan tanpa token serta penerimaan token yang benar.

## Integrasi master Dana dan batas verifikasi

- Adapter Dana nanti menggantikan `LocalTicketCatalogProvider` melalui
  `TicketCatalogProvider`, tanpa menjadikan tabel dummy sebagai master resmi.
- Pemetaan yang dibutuhkan: ID/kode, nama/kategori, aktif, tipe wisatawan,
  tarif usia, pengaruh tanggal, aturan gratis, serta kuota jika memang ada.
- Referensi `dummy:*` pada transaksi historis adalah sumber demo. Jangan
  mengimpor akun/pesanan demo ke Supabase.
- Model transaksi saat ini secara eksplisit menggunakan SQLite. Untuk integrasi
  production, tinjau koneksi, tipe PK/FK, constraint, status pembayaran dan
  migration terhadap schema PostgreSQL yang sebenarnya.
- Migration lokal **tidak** otomatis dijalankan oleh `php artisan migrate`.
  Belum ada migration transaksi production atau bukti kompatibilitas PostgreSQL.
- Keamanan workflow berlaku pada command/seeder/migration demo yang disediakan.
  Jangan menjalankan perintah migration/seeding lain dengan `.env` Supabase.

Tidak ada commit, push, atau merge final otomatis. Setelah Naufal memerintahkan:
commit/push branch fitur, perbarui develop, merge langsung ke develop, push,
lalu kembali ke branch fitur. Berhenti jika konflik atau push ditolak; tidak
memakai force push.
