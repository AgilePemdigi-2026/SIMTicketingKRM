@postgres_purchase @sprint2 @e_tiket
Feature: Penerbitan dan akses E-Tiket wisatawan
  Sebagai wisatawan
  Saya ingin memperoleh E-Tiket setelah pembayaran lunas
  Agar tiket dapat digunakan saat berkunjung

  Scenario: E-Tiket tidak dapat diakses sebelum pembayaran lunas
    Given saya sudah login sebagai wisatawan
    And saya memiliki pesanan tiket yang belum dibayar
    When saya membuka E-Tiket pesanan
    Then saya diarahkan kembali ke halaman pembayaran


  Scenario: Pembayaran lunas menerbitkan E-Tiket pengunjung
    Given saya sudah login sebagai wisatawan
    And saya memiliki pesanan tiket yang belum dibayar
    And pembayaran Cash pesanan saya telah dikonfirmasi petugas
    When listener penerbitan memproses event pembayaran lunas
    Then satu E-Tiket aktif diterbitkan untuk setiap pengunjung

  Scenario: Pemrosesan ulang tidak menduplikasi E-Tiket
    Given saya sudah login sebagai wisatawan
    And saya memiliki pesanan tiket yang belum dibayar
    And pembayaran Cash pesanan saya telah dikonfirmasi petugas
    When listener penerbitan memproses event pembayaran lunas dua kali
    Then satu E-Tiket aktif diterbitkan untuk setiap pengunjung
    And kode E-Tiket tidak berubah setelah pemrosesan ulang

  Scenario: Wisatawan melihat E-Tiket setelah pembayaran lunas
    Given saya sudah login sebagai wisatawan
    And saya memiliki pesanan tiket yang belum dibayar
    And pembayaran Cash pesanan saya telah dikonfirmasi petugas
    When listener penerbitan memproses event pembayaran lunas
    And saya membuka E-Tiket pesanan
    Then halaman E-Tiket menampilkan identitas dan kode tiket