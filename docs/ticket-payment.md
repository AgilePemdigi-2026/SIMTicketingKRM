# Pembayaran tiket manual — Sprint 2 Naufal

Modul melanjutkan pesanan tersimpan ke pembayaran QRIS statis atau cash dan verifikasi Petugas. Tidak ada sinkronisasi bank, QRIS dinamis, refund, perubahan kuota, penerbitan E-Tiket, atau validasi tiket. `paid` adalah titik serah ke modul E-Tiket Dana, bukan bukti seluruh proses ticketing selesai.

## Status dan aturan

| Pembayaran | Pesanan | Makna |
| --- | --- | --- |
| belum dibuat / `pending` | `pending_payment` | Metode belum dipilih / menunggu pembayaran |
| `under_review` | `pending_payment` | Bukti dikirim, belum terverifikasi |
| `rejected` | `pending_payment` | Bukti ditolak dengan alasan; boleh mengajukan ulang |
| `paid` | `paid` | Penerimaan sah telah dikonfirmasi Petugas |

Satu pesanan memiliki satu pembayaran. Metode `qris` atau `cash` dikunci setelah pertama disimpan. Nominal `decimal:2` berasal dari snapshot total pesanan. Input nominal/status dari browser tidak menentukan nilai tersimpan.

QRIS menggunakan merchant **MUHAMMAD NAUFAL ZAKI, SOFTWARE**, untuk demonstrasi proyek, bukan rekening resmi destinasi. Pelanggan memasukkan nominal di aplikasi bank/e-wallet. Penyelesaian transaksi menggunakan uang sungguhan. Tidak ada hitung mundur atau konfirmasi otomatis. Hasil crop `public/images/pembayaran/qris-code.png` mempertahankan payload QR asli (dibandingkan dengan zxing-cpp); pemindaian pada aplikasi bank tetap perlu diperiksa pemilik merchant sendiri.

Petugas mencocokkan penerimaan pada riwayat GoPay Merchant, nominal, dan bukti. Persetujuan QRIS wajib menunjuk bukti terbaru serta referensi merchant unik, dinormalisasi huruf besar. Gambar bukti saja tidak cukup. Cash hanya dikonfirmasi setelah uang diterima. Semua pemeriksaan wajib menyertakan `receipt_checked=1`.

Penguncian urutan pesanan lalu pembayaran dan transaksi database menjaga perubahan pembayaran, pesanan, hasil pemeriksaan, dan riwayat tetap atomik. Pilihan metode identik dan konfirmasi identik boleh dikirim ulang tanpa riwayat ganda; metode/keputusan yang bertentangan atau bukti yang sudah berganti ditolak `409`. Pengajuan ulang setelah penolakan membuat record baru dan mempertahankan bukti sebelumnya.

## Endpoint HTML dan JSON

Gunakan session login dan CSRF Laravel. Tambahkan `Accept: application/json` untuk respons JSON. Akses pembelian wisatawan dibatasi pada pemilik pesanan; UUID `code` adalah UUID pesanan, bukan ID numerik.

| Metode dan URL | Akses / input |
| --- | --- |
| `GET /wisatawan/pembelian-tiket/{code}/pembayaran` | Wisatawan pemilik; ringkasan dan pembayaran |
| `POST /wisatawan/pembelian-tiket/{code}/pembayaran` | Wisatawan pemilik; `method=qris\|cash` |
| `POST /wisatawan/pembelian-tiket/{code}/pembayaran/bukti` | Wisatawan pemilik; multipart `proof`, JPG/PNG maksimal 5 MB; 20 permintaan/menit |
| `GET /petugas/pembayaran` | Petugas; terbaru dahulu, 20 baris/halaman |
| `GET /petugas/pembayaran/{payment}` | Petugas; ID pembayaran numerik |
| `POST /petugas/pembayaran/{payment}/konfirmasi` | Petugas; `receipt_checked=1`; QRIS juga `submission_id` terbaru dan `merchant_reference` |
| `POST /petugas/pembayaran/{payment}/tolak` | Petugas; `receipt_checked=1`, `submission_id` terbaru, `reason` wajib |
| `GET /pembayaran/bukti/{submission}` | Petugas atau wisatawan pemilik; ID pengajuan numerik |

Write sukses: JSON `200` atau redirect HTML. JSON write mengembalikan `currency`, `order_code`, `order_status`, dan `payment` beserta submissions/events. GET wisatawan menambahkan `total`. Nilai nominal berupa string desimal IDR. JSON tidak menyertakan path privat atau hash berkas. Validasi `422`; konflik `409`; resource pengguna lain `404`; role tidak berhak `403`.

Bukti memakai disk `local` privat, nama acak pada `ticket-payment-proofs/`, validasi ekstensi, MIME, isi gambar, dan ukuran. Endpoint bukti menggunakan autentikasi/otorisasi dan `private, no-store`. Tidak ada URL storage publik. Berkas baru dibersihkan jika penyimpanan database gagal.

## Kontrak untuk Dana

`TicketOrder::payment()` menyediakan `code` UUID pembayaran, `method`, `amount`, `status`, dan `paid_at`; UUID pesanan ada di `TicketOrder::code`. Total/item/pengunjung pesanan tetap memakai snapshot tersimpan. Hanya `status=paid` menandakan pembayaran sah dikonfirmasi.

Event `App\Events\TicketPaymentConfirmed` dikirim **setelah transaksi terluar berhasil commit**, dengan properti readonly:

```php
$orderCode;   // string UUID pesanan
$paymentCode; // string UUID pembayaran
$method;      // qris atau cash
$amount;      // string desimal, contoh 20000.00 (IDR)
$paidAt;      // string ISO 8601
```

Dana dapat memasang listener yang mengambil pesanan memakai `orderCode`. Listener penerbitan harus idempoten berdasarkan UUID pesanan. Event ini belum memakai outbox atau mekanisme replay tahan kegagalan listener; proses pemulihan dapat membaca pembayaran `paid` dari database. Modul ini tidak menyediakan endpoint pembayaran eksternal atau menerbitkan E-Tiket.

## Migrasi dan pengujian

Periksa `php artisan migrate:status` sebelum menerapkan migration tambahan `2026_11_02_072239_create_ticket_payment_tables.php`. Jangan reset database. Tiga tabel baru menyimpan pembayaran, pengajuan bukti, serta riwayat pelaku/tindakan/alasan/waktu; constraint unik melindungi satu pembayaran per pesanan dan referensi merchant.

```sh
vendor/bin/phpunit --do-not-cache-result tests/Feature/TicketPaymentTest.php tests/Feature/FigmaContentTest.php tests/Feature/Wisatawan tests/Unit/TicketPurchaseServiceTest.php
```

Tes transaksi dibatasi ke PostgreSQL `simticketingkrm_purchase_test`, termasuk persaingan dua konfirmasi, rollback, event sesudah commit, ownership, berkas privat, nominal server, penolakan dan pengajuan ulang. Browser memakai fixture terpisah pada database itu; tidak melakukan transaksi percobaan pada akun aplikasi atau pembayaran uang sungguhan.

## Foto Figma dan konten contoh lokal

Aset diekspor dari layer gambar asli file `PPL Prak`, halaman **Revisi Final & High Fidelity**, bukan screenshot halaman atau foto internet:

| Aset | Layer sumber |
| --- | --- |
| `public/images/figma/jembatan.jpg` | `172:944`, Image (Kebun Raya Mangrove) |
| `public/images/figma/danau.jpg` | `172:957`, Image (Sepeda Air) |
| `public/images/figma/lanskap.jpg` | `172:970`, Image (ATV) |
| `public/images/figma/sungai.jpg` | `172:996`, Image (Perahu Air) |

Acuan Beranda `172:857`, pembelian `173:1688` dan `173:2067`, QRIS `173:2392`, dan Cash `173:2723`. MCP menolak akses editor; pemeriksaan dan ekspor menggunakan Figma Desktop secara baca saja. Susunan utama diikuti dengan penyesuaian status pembayaran manual; kesamaan piksel seluruh desain tidak diklaim.

`FIGMA_DEMO_CONTENT=true` hanya aktif jika `APP_ENV=local`. Ketika bagian kosong, delapan posisi galeri mengikuti urutan jembatan, lanskap, sungai, danau, lanskap, jembatan, danau, sungai; satu artikel berlabel **Demo Proyek** memakai thumbnail lanskap. Konten Admin terbit tetap diprioritaskan dan unggahan yang berkasnya tersedia dipertahankan. Referensi berkas rusak memakai ilustrasi tanpa mengubah master. Artikel dan galeri contoh tidak ditulis ke database dan tidak aktif di production. Dua belas path foto lama dipulihkan menggunakan empat foto Figma yang sama.


## Hasil verifikasi lokal 2 November 2026

- Tes terarah pembelian, pembayaran, dan konten Figma: **147 tes / 1.029 assertions lulus**.
- Regresi penuh sesudah pembersihan dependency: **163 dari 164 tes lulus / 1.066 assertions**. Satu kegagalan tetap pada `Tests\Feature\ExampleTest`: konfigurasi SQLite in-memory bawaan tes tidak mempunyai tabel `wahana`. Ini bukan koneksi aplikasi utama, yang telah diperiksa memakai PostgreSQL.
- Build Vite dan kompilasi Blade berhasil; `git diff --check` bersih. Peringatan build tentang paket fontaine opsional tidak menggagalkan build.
- Browser desktop 1280 piksel dan ponsel 390 piksel: pembelian hingga pesanan, pilihan QRIS, unggah dan penolakan/pengajuan ulang, persetujuan QRIS, konfirmasi cash, serta pembukaan ulang pembayaran lunas diperiksa dengan akun sintetis. Tidak ada pembayaran bank sungguhan dilakukan. Foto Beranda termuat, galeri delapan posisi tersedia, dan tidak ada overflow horizontal pada ukuran tersebut. Detail artikel serta prioritas konten Admin/lingkungan produksi diverifikasi melalui feature tests.
- Database aplikasi sesudah uji tetap 1 pengguna, 1 pesanan, 0 pembayaran, 0 konten artikel/galeri; kuota master id 1/4/5 tetap 100/10/10. Seluruh migration berstatus Ran.
- Fixture browser dan transaksi pada `simticketingkrm_purchase_test` dibersihkan; pesanan dan pembayaran pengujian kembali kosong. Server uji port 8002 dihentikan; aplikasi utama tetap tersedia di port 8000.
- Composer/lock dan panduan bootstrap dikembalikan ke keadaan sebelum pemasangan Boost sementara. Tidak ada commit, push, pull, atau reset database dalam penyelesaian ini.
