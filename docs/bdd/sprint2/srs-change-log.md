# SRS Change Log - Sprint 2

## 1. Informasi Dokumen

- Proyek: SIMTicketingKRM
- Tahap: Sprint 2
- Tanggal: 11 November 2026
- Metode pengujian: Behavior-Driven Development (BDD)
- Framework pengujian: Behat
- Status: Draft evaluasi kesesuaian SRS

## 2. Tujuan

Dokumen ini mencatat perbedaan yang teridentifikasi
antara rancangan SRS dan implementasi Sprint 2,
serta batasan pengujian yang masih memerlukan verifikasi.

Catatan ini tidak otomatis mengubah atau menyetujui
requirement SRS. Perubahan harus dikonfirmasi kepada
tim proyek dan disesuaikan dengan dokumen SRS resmi.

## 3. Perbedaan Rancangan dan Implementasi

| ID | Area | Rancangan SRS / Ekspektasi | Implementasi Sprint 2 | Status |
|---|---|---|---|---|
| CHG-01 | Database | MySQL pada rancangan awal | PostgreSQL digunakan dalam implementasi dan pengujian | Perbedaan teknologi |
| CHG-02 | Payment Gateway | Xendit pada rancangan awal | Midtrans Sandbox digunakan untuk QRIS | Perbedaan integrasi |
| CHG-03 | Pembelian Loket | Alur pembelian dan pembayaran oleh petugas | Preview menerima Cash/QRIS, tetapi checkout loket hanya menerima Cash | Perlu evaluasi konsistensi |
| CHG-04 | Penerbitan E-Tiket | Tiket diterbitkan setelah pembayaran berhasil | Listener IssueTicketsAfterPayment digunakan untuk menerbitkan tiket | Implementasi tersedia, pengujian integrasi perlu dilengkapi |

Catatan:
- Deskripsi rancangan awal perlu dicocokkan kembali
  dengan versi SRS yang disahkan.
- Perbedaan teknologi belum otomatis berarti
  ketidaksesuaian fungsional.
- Penetapan perubahan resmi membutuhkan persetujuan tim.

## 4. Batasan Verifikasi Sprint 2

| ID | Area | Hasil yang Sudah Dibuktikan | Pengujian yang Belum Tercakup |
|---|---|---|---|
| LIM-01 | QRIS | Webhook simulasi, signature, nominal, settlement, expiry, dan idempotensi | Transaksi nyata melalui gateway produksi |
| LIM-02 | E-Tiket | Penerbitan data, akses, dan pencegahan duplikasi | Tampilan QR visual dan unduhan dokumen |
| LIM-03 | Event Pembayaran | Listener penerbitan dapat memproses event | Pembuktian event after-commit secara end-to-end |
| LIM-04 | Validasi E-Tiket | Verifikasi token, tanggal, pembayaran, dan check-in | Pengujian otomatis scanner kamera |
| LIM-05 | Riwayat Pesanan | Riwayat dan detail pesanan tiket milik wisatawan | Riwayat reservasi jika termasuk cakupan SRS |
| LIM-06 | Harga Tiket | Total pesanan dan pembayaran Cash konsisten | Seluruh variasi harga berdasarkan katalog |
| LIM-07 | Check-in | Penggunaan berulang tiket yang sama ditolak | Pengujian konkurensi check-in |
| LIM-08 | QRIS Kedaluwarsa | Status expiry diproses oleh webhook simulasi | Regenerasi QRIS dan seluruh variasi status gateway |

Keterangan:
Belum tercakup pengujian tidak selalu berarti
fitur belum diimplementasikan.

## 5. Rekomendasi Tindak Lanjut

1. Konfirmasi perubahan database dan payment gateway
   terhadap dokumen SRS dan keputusan tim.
2. Evaluasi perbedaan opsi metode pembayaran
   pada preview dan checkout petugas loket.
3. Verifikasi tampilan QR dan kemampuan unduh E-Tiket.
4. Tambahkan pengujian otomatis untuk bagian yang
   belum tercakup apabila masih termasuk lingkup proyek.
5. Pastikan seluruh requirement individual pada SRS
   telah dipetakan ke acceptance criteria yang sesuai.
6. Perbarui SRS resmi hanya setelah perubahan disetujui.

## 6. Referensi Evidence

Seluruh evidence berada di:

docs/bdd/sprint2/evidence/

Evidence utama regression testing:

sprint2-full-20261111-025920.txt

Hasil:
- 33 skenario Sprint 2 Passed
- 169 langkah Sprint 2 Passed
- 0 Failed

Hasil baseline pembelian tiket:
- 3 skenario Passed
- 10 langkah Passed

Total akumulasi:
- 36 skenario Passed
- 179 langkah Passed

## 7. Kesimpulan

Pengujian BDD menunjukkan bahwa seluruh skenario
yang dieksekusi berhasil memenuhi ekspektasi pengujian.

Perbedaan antara rancangan SRS dan implementasi
dicatat untuk proses evaluasi dan persetujuan perubahan.

Kebutuhan yang belum sepenuhnya terverifikasi
tidak dinyatakan Passed secara otomatis hanya karena
skenario terkait telah berhasil dijalankan.
