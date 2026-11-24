# BDD Test Results - Sprint 2

## 1. Informasi Pengujian

- Proyek: SIMTicketingKRM
- Metode: Behavior-Driven Development (BDD)
- Framework: Behat dengan Laravel
- Database: PostgreSQL khusus pengujian
- Lingkungan pembayaran: Midtrans Sandbox dengan gateway simulasi
- Tanggal pengujian: 11 November 2026

## 2. Hasil Pengujian

| Modul | Scenarios | Steps | Hasil |
|---|---:|---:|---|
| Baseline Pembelian Tiket | 3 | 10 | Passed |
| Pembayaran Cash dan QRIS | 4 | 21 | Passed |
| Webhook Midtrans | 6 | 17 | Passed |
| Penerbitan E-Tiket | 4 | 21 | Passed |
| Validasi QR dan Check-in | 6 | 51 | Passed |
| Riwayat Pesanan | 6 | 26 | Passed |
| Pembelian Petugas Loket | 7 | 33 | Passed |
| **Total** | **36** | **179** | **Passed** |

## 3. Regression Testing Sprint 2

Pengujian regresi dilakukan dengan menjalankan seluruh
skenario bertag @sprint2 secara bersamaan.

Perintah:

.\vendor\bin\behat.bat --tags="@sprint2" --format=pretty

Hasil:

- 33 scenarios (33 passed)
- 169 steps (169 passed)
- 0 failed
- Waktu eksekusi: 13,38 detik

Tiga skenario baseline pembelian tiket telah diuji
secara terpisah dengan hasil seluruhnya Passed.

## 4. Bukti Pengujian

Seluruh log disimpan dalam folder docs/bdd/sprint2/evidence/.

| Modul | File Evidence |
|---|---|
| Baseline | baseline-20261111-014236.txt |
| Pembayaran | payment-20261111-015347.txt |
| Webhook | webhook-20261111-020451.txt |
| E-Tiket | e-tiket-20261111-022355.txt |
| Validasi | validasi-20261111-023320.txt |
| Riwayat | riwayat-20261111-024235.txt |
| Loket | loket-20261111-025748.txt |
| Full Regression | sprint2-full-20261111-025920.txt |

## 5. Ringkasan Hasil

Seluruh 33 skenario BDD Sprint 2 berhasil dijalankan
secara bersamaan tanpa kegagalan.

Pengujian mencakup pemilihan dan konfirmasi pembayaran,
webhook QRIS, penerbitan data E-Tiket, validasi token QR,
check-in, riwayat pesanan, dan transaksi petugas loket.

Hasil Passed menunjukkan bahwa perilaku yang dicakup
oleh skenario berjalan sesuai ekspektasi pengujian.

## 6. Batasan Pengujian

- Webhook Midtrans diuji menggunakan payload dan gateway simulasi.
- Listener penerbitan E-Tiket diuji secara langsung,
  bukan melalui pembuktian event after-commit end-to-end.
- Tampilan QR Code E-Tiket belum dibuktikan melalui BDD.
- Pemindaian kamera belum diuji secara otomatis.
- Ketepatan harga seluruh variasi tiket belum diuji.
- Riwayat reservasi dan unduhan dokumen belum diuji secara menyeluruh.
- Pengujian konkurensi check-in belum termasuk dalam skenario Behat ini.

## 7. Kesimpulan

Pelaksanaan BDD Testing Sprint 2 menunjukkan hasil
33 dari 33 skenario Passed dengan 169 dari 169 langkah
berhasil.

Apabila digabungkan dengan baseline pembelian tiket,
terdapat 36 skenario dan 179 langkah yang telah Passed
pada pengujian terpisah.

Kebutuhan yang belum tercakup tetap memerlukan
verifikasi lanjutan dan pencatatan pada traceability
matrix serta SRS change log.
