@postgres_purchase @sprint2
Feature: Pembayaran tiket wisatawan
  Sebagai wisatawan
  Saya ingin melakukan pembayaran Cash atau QRIS
  Agar transaksi pembelian tiket dapat diproses

  Background:
    Given saya sudah login sebagai wisatawan
    And saya memiliki pesanan tiket yang belum dibayar

  Scenario: Wisatawan memilih pembayaran Cash
    When saya memilih pembayaran Cash untuk pesanan
    Then pembayaran Cash menunggu konfirmasi petugas
    And halaman pembayaran menampilkan kode Cash

  Scenario: Petugas mengonfirmasi pembayaran Cash
    When saya memilih pembayaran Cash untuk pesanan
    And saya login sebagai petugas pembayaran
    And saya mengonfirmasi pembayaran Cash tersebut
    Then transaksi dan pembayaran Cash berstatus lunas

  Scenario: Wisatawan memilih pembayaran QRIS
    When saya memilih pembayaran QRIS untuk pesanan
    Then pembayaran QRIS masih menunggu konfirmasi Midtrans

  Scenario: Petugas tidak dapat mengonfirmasi QRIS secara manual
    When saya memilih pembayaran QRIS untuk pesanan
    And saya login sebagai petugas pembayaran
    And petugas mencoba mengonfirmasi QRIS secara manual
    Then sistem menolak konfirmasi QRIS manual