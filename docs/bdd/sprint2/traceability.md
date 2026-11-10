# BDD Traceability Matrix - Sprint 2

## 1. Tujuan

Dokumen ini menghubungkan kebutuhan dalam Software Requirements
Specification (SRS), User Story, skenario Gherkin, hasil BDD,
dan evidence pengujian SIMTicketingKRM.

## 2. Referensi SRS dan User Story

| Referensi SRS | Kelompok Kebutuhan | User Story / Tujuan Pengujian | Jumlah Skenario |
|---|---|---|---:|
| Bagian 4.3 - REQ-TKT | Pembelian Tiket | Wisatawan memilih tiket dan menyimpan pesanan | 3 |
| Bagian 4.3 dan 4.8 - REQ-TKT / REQ-CSH | Pembayaran | Wisatawan membayar menggunakan Cash atau QRIS | 4 |
| Bagian 4.3 - REQ-TKT | QRIS dan Webhook | Sistem memverifikasi pembayaran Midtrans secara otomatis | 6 |
| Bagian 4.3 - REQ-TKT | Penerbitan E-Tiket | Wisatawan memperoleh E-Tiket setelah pembayaran lunas | 4 |
| Bagian 4.9 - REQ-VAL | Validasi E-Tiket | Petugas memverifikasi tiket dan mencatat check-in | 6 |
| Bagian 4.6 - REQ-HST | Riwayat Pesanan | Wisatawan melihat daftar dan detail pesanan miliknya | 6 |
| Bagian 4.7 dan 4.8 - REQ-LKT / REQ-CSH | Pembelian Loket | Petugas melayani pembelian dan konfirmasi Cash | 7 |
| **Total** | | | **36** |

Pemetaan menggunakan kelompok requirement SRS.
Nomor requirement individual perlu diverifikasi kembali terhadap
naskah SRS sebelum digunakan sebagai sitasi final.

## 3. Detail Traceability per Skenario

Tabel berikut dihasilkan dari file Gherkin yang diuji.

| Referensi SRS | File Feature | Scenario | Hasil | Evidence |
|---|---|---|---|---|

| 4.3 - REQ-TKT | `features/pembelian_tiket.feature` | Wisatawan membuka katalog tiket aktif | Passed | `baseline-20261111-014236.txt` |
| 4.3 - REQ-TKT | `features/pembelian_tiket.feature` | Wisatawan melihat detail checkout tiket | Passed | `baseline-20261111-014236.txt` |
| 4.3 - REQ-TKT | `features/pembelian_tiket.feature` | Wisatawan menyimpan transaksi pembelian | Passed | `baseline-20261111-014236.txt` |
| 4.3/4.8 - REQ-TKT/REQ-CSH | `features/pembayaran_tiket_sprint2.feature` | Wisatawan memilih pembayaran Cash | Passed | `payment-20261111-015347.txt` |
| 4.3/4.8 - REQ-TKT/REQ-CSH | `features/pembayaran_tiket_sprint2.feature` | Petugas mengonfirmasi pembayaran Cash | Passed | `payment-20261111-015347.txt` |
| 4.3/4.8 - REQ-TKT/REQ-CSH | `features/pembayaran_tiket_sprint2.feature` | Wisatawan memilih pembayaran QRIS | Passed | `payment-20261111-015347.txt` |
| 4.3/4.8 - REQ-TKT/REQ-CSH | `features/pembayaran_tiket_sprint2.feature` | Petugas tidak dapat mengonfirmasi QRIS secara manual | Passed | `payment-20261111-015347.txt` |
| 4.3 - REQ-TKT | `features/qris_webhook_sprint2.feature` | Sistem menyiapkan QRIS untuk pembayaran | Passed | `webhook-20261111-020451.txt` |
| 4.3 - REQ-TKT | `features/qris_webhook_sprint2.feature` | Midtrans mengonfirmasi pembayaran berhasil | Passed | `webhook-20261111-020451.txt` |
| 4.3 - REQ-TKT | `features/qris_webhook_sprint2.feature` | Webhook dengan signature palsu ditolak | Passed | `webhook-20261111-020451.txt` |
| 4.3 - REQ-TKT | `features/qris_webhook_sprint2.feature` | Webhook dengan nominal pembayaran berbeda ditolak | Passed | `webhook-20261111-020451.txt` |
| 4.3 - REQ-TKT | `features/qris_webhook_sprint2.feature` | Midtrans melaporkan QRIS kedaluwarsa | Passed | `webhook-20261111-020451.txt` |
| 4.3 - REQ-TKT | `features/qris_webhook_sprint2.feature` | Webhook settlement yang sama diterima dua kali | Passed | `webhook-20261111-020451.txt` |
| 4.3 - REQ-TKT | `features/e_tiket_sprint2.feature` | E-Tiket tidak dapat diakses sebelum pembayaran lunas | Passed | `e-tiket-20261111-022355.txt` |
| 4.3 - REQ-TKT | `features/e_tiket_sprint2.feature` | Pembayaran lunas menerbitkan E-Tiket pengunjung | Passed | `e-tiket-20261111-022355.txt` |
| 4.3 - REQ-TKT | `features/e_tiket_sprint2.feature` | Pemrosesan ulang tidak menduplikasi E-Tiket | Passed | `e-tiket-20261111-022355.txt` |
| 4.3 - REQ-TKT | `features/e_tiket_sprint2.feature` | Wisatawan melihat E-Tiket setelah pembayaran lunas | Passed | `e-tiket-20261111-022355.txt` |
| 4.9 - REQ-VAL | `features/validasi_tiket_sprint2.feature` | Petugas memverifikasi E-Tiket yang valid | Passed | `validasi-20261111-023320.txt` |
| 4.9 - REQ-VAL | `features/validasi_tiket_sprint2.feature` | Petugas memverifikasi token QR tidak dikenal | Passed | `validasi-20261111-023320.txt` |
| 4.9 - REQ-VAL | `features/validasi_tiket_sprint2.feature` | Petugas memverifikasi tiket yang belum lunas | Passed | `validasi-20261111-023320.txt` |
| 4.9 - REQ-VAL | `features/validasi_tiket_sprint2.feature` | Petugas memverifikasi tanggal kunjungan berbeda | Passed | `validasi-20261111-023320.txt` |
| 4.9 - REQ-VAL | `features/validasi_tiket_sprint2.feature` | Petugas berhasil melakukan check-in | Passed | `validasi-20261111-023320.txt` |
| 4.9 - REQ-VAL | `features/validasi_tiket_sprint2.feature` | E-Tiket tidak dapat digunakan untuk check-in dua kali | Passed | `validasi-20261111-023320.txt` |
| 4.6 - REQ-HST | `features/riwayat_pesanan_sprint2.feature` | Wisatawan belum memiliki riwayat pesanan | Passed | `riwayat-20261111-024235.txt` |
| 4.6 - REQ-HST | `features/riwayat_pesanan_sprint2.feature` | Wisatawan melihat riwayat pesanan miliknya | Passed | `riwayat-20261111-024235.txt` |
| 4.6 - REQ-HST | `features/riwayat_pesanan_sprint2.feature` | Riwayat menampilkan status pembayaran lunas | Passed | `riwayat-20261111-024235.txt` |
| 4.6 - REQ-HST | `features/riwayat_pesanan_sprint2.feature` | Wisatawan membuka detail pesanannya sendiri | Passed | `riwayat-20261111-024235.txt` |
| 4.6 - REQ-HST | `features/riwayat_pesanan_sprint2.feature` | Wisatawan tidak melihat pesanan akun lain | Passed | `riwayat-20261111-024235.txt` |
| 4.6 - REQ-HST | `features/riwayat_pesanan_sprint2.feature` | Wisatawan tidak dapat membuka detail akun lain | Passed | `riwayat-20261111-024235.txt` |
| 4.7/4.8 - REQ-LKT/REQ-CSH | `features/pembelian_loket_sprint2.feature` | Petugas dapat membuka halaman pembelian loket | Passed | `loket-20261111-025748.txt` |
| 4.7/4.8 - REQ-LKT/REQ-CSH | `features/pembelian_loket_sprint2.feature` | Wisatawan tidak dapat membuka halaman loket | Passed | `loket-20261111-025748.txt` |
| 4.7/4.8 - REQ-LKT/REQ-CSH | `features/pembelian_loket_sprint2.feature` | Petugas melihat preview harga tiket loket | Passed | `loket-20261111-025748.txt` |
| 4.7/4.8 - REQ-LKT/REQ-CSH | `features/pembelian_loket_sprint2.feature` | Petugas berhasil membuat transaksi Cash | Passed | `loket-20261111-025748.txt` |
| 4.7/4.8 - REQ-LKT/REQ-CSH | `features/pembelian_loket_sprint2.feature` | Petugas mengonfirmasi pembayaran Cash loket | Passed | `loket-20261111-025748.txt` |
| 4.7/4.8 - REQ-LKT/REQ-CSH | `features/pembelian_loket_sprint2.feature` | Checkout loket menolak identitas wisatawan tidak cocok | Passed | `loket-20261111-025748.txt` |
| 4.7/4.8 - REQ-LKT/REQ-CSH | `features/pembelian_loket_sprint2.feature` | Checkout loket menolak jumlah pengunjung tidak sesuai | Passed | `loket-20261111-025748.txt` |

## 4. Hasil Regression Testing

- Tanggal: 11 November 2026
- Framework: Behat
- Database: PostgreSQL testing
- Sprint 2: 33 scenarios Passed, 169 steps Passed
- Baseline: 3 scenarios Passed, 10 steps Passed
- Akumulasi: 36 scenarios Passed, 179 steps Passed
- Failed: 0 pada eksekusi terakhir masing-masing kelompok

Evidence utama:
`sprint2-full-20261111-025920.txt`

## 5. Kebutuhan yang Memerlukan Verifikasi Lanjutan

| Area | Status / Catatan |
|---|---|
| QR Code visual E-Tiket | Belum dibuktikan oleh BDD |
| Scanner QR berbasis kamera | Belum diuji otomatis |
| Event penerbitan setelah commit | Listener diuji langsung, belum end-to-end |
| Unduhan E-Tiket/PDF | Belum diuji |
| Riwayat reservasi | Belum tercakup |
| Variasi harga tiket | Belum diuji menyeluruh terhadap katalog |
| Check-in bersamaan oleh dua petugas | Belum diuji konkurensinya |
| QRIS setelah kedaluwarsa | Regenerasi QR memerlukan pemeriksaan lanjutan |

## 6. Kesimpulan

Skenario BDD yang telah dilaksanakan berhasil memenuhi
ekspektasi pengujian masing-masing.

Hasil Passed tidak berarti seluruh requirement SRS telah
terverifikasi sepenuhnya. Kebutuhan yang belum tercakup
harus dilacak dalam pengujian lanjutan dan SRS Change Log.
