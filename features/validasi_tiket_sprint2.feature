@postgres_purchase @sprint2 @validasi_tiket
Feature: Validasi QR dan check-in E-Tiket
  Sebagai petugas
  Saya ingin memverifikasi dan memproses check-in E-Tiket
  Agar hanya tiket yang sah dapat digunakan untuk masuk

  Background:
    Given saya sudah login sebagai wisatawan
    And saya memiliki pesanan tiket yang belum dibayar
    And pembayaran Cash pesanan saya telah dikonfirmasi petugas
    And listener penerbitan memproses event pembayaran lunas
    And tanggal kunjungan pesanan disetel ke hari ini
    And saya masuk sebagai petugas validasi tiket

  Scenario: Petugas memverifikasi E-Tiket yang valid
    When petugas memverifikasi token QR E-Tiket
    Then hasil verifikasi menyatakan tiket valid

  Scenario: Petugas memverifikasi token QR tidak dikenal
    When petugas memverifikasi token QR tidak dikenal
    Then hasil verifikasi menolak token tidak ditemukan

  Scenario: Petugas memverifikasi tiket yang belum lunas
    Given status pembayaran tiket diubah menjadi menunggu untuk pengujian
    When petugas memverifikasi token QR E-Tiket
    Then hasil verifikasi menolak pembayaran belum lunas

  Scenario: Petugas memverifikasi tanggal kunjungan berbeda
    Given tanggal kunjungan tiket diubah menjadi hari lain
    When petugas memverifikasi token QR E-Tiket
    Then hasil verifikasi menolak tanggal tidak sesuai

  Scenario: Petugas berhasil melakukan check-in
    When petugas melakukan check-in E-Tiket
    Then E-Tiket berstatus used dan waktu penggunaan tercatat

  Scenario: E-Tiket tidak dapat digunakan untuk check-in dua kali
    When petugas melakukan check-in E-Tiket
    And petugas mengulangi check-in pada E-Tiket yang sama
    Then check-in kedua ditolak dan tiket tetap digunakan satu kali
