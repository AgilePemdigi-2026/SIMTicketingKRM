@postgres_purchase @sprint2 @riwayat_pesanan
Feature: Melihat riwayat pesanan tiket
  Sebagai wisatawan
  Saya ingin melihat riwayat pembelian tiket
  Agar saya dapat memantau transaksi dan membuka detail pesanan

  Background:
    Given saya sudah login sebagai wisatawan

  Scenario: Wisatawan belum memiliki riwayat pesanan
    When saya membuka halaman riwayat pesanan tiket
    Then daftar riwayat tiket masih kosong

  Scenario: Wisatawan melihat riwayat pesanan miliknya
    Given saya memiliki pesanan tiket yang belum dibayar
    When saya membuka halaman riwayat pesanan tiket
    Then riwayat mencantumkan pesanan saya yang menunggu pembayaran

  Scenario: Riwayat menampilkan status pembayaran lunas
    Given saya memiliki pesanan tiket yang belum dibayar
    And pembayaran Cash pesanan saya telah dikonfirmasi petugas
    When saya membuka halaman riwayat pesanan tiket
    Then riwayat mencantumkan pesanan saya yang sudah lunas

  Scenario: Wisatawan membuka detail pesanannya sendiri
    Given saya memiliki pesanan tiket yang belum dibayar
    When saya membuka detail pesanan tiket saya
    Then detail transaksi sesuai dengan pesanan saya

  Scenario: Wisatawan tidak melihat pesanan akun lain
    Given saya memiliki pesanan tiket yang belum dibayar
    And saya beralih ke akun wisatawan lain
    When saya membuka halaman riwayat pesanan tiket
    Then pesanan wisatawan pertama tidak muncul dalam riwayat

  Scenario: Wisatawan tidak dapat membuka detail akun lain
    Given saya memiliki pesanan tiket yang belum dibayar
    And saya beralih ke akun wisatawan lain
    When saya membuka detail pesanan wisatawan pertama
    Then akses detail pesanan tersebut ditolak