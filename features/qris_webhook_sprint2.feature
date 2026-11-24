@postgres_purchase @sprint2 @webhook_qris
Feature: Pembayaran QRIS otomatis melalui Midtrans
  Sebagai wisatawan
  Saya ingin pembayaran QRIS dikonfirmasi secara otomatis
  Agar status pesanan diperbarui dengan aman

  Scenario: Sistem menyiapkan QRIS untuk pembayaran
    Given saya memiliki pesanan QRIS dari gateway simulasi
    Then QRIS simulasi tersedia dan pembayaran masih menunggu

  Scenario: Midtrans mengonfirmasi pembayaran berhasil
    Given saya memiliki pesanan QRIS dari gateway simulasi
    When webhook Midtrans simulasi mengonfirmasi settlement
    Then pembayaran QRIS otomatis lunas

  Scenario: Webhook dengan signature palsu ditolak
    Given saya memiliki pesanan QRIS dari gateway simulasi
    When webhook Midtrans memakai signature palsu
    Then webhook ditolak karena signature tidak valid

  Scenario: Webhook dengan nominal pembayaran berbeda ditolak
    Given saya memiliki pesanan QRIS dari gateway simulasi
    When webhook Midtrans melaporkan nominal berbeda
    Then webhook ditolak karena nominal tidak cocok

  Scenario: Midtrans melaporkan QRIS kedaluwarsa
    Given saya memiliki pesanan QRIS dari gateway simulasi
    When webhook Midtrans melaporkan QRIS kedaluwarsa
    Then attempt QRIS kedaluwarsa dan pembayaran belum lunas

  Scenario: Webhook settlement yang sama diterima dua kali
    Given saya memiliki pesanan QRIS dari gateway simulasi
    When webhook settlement Midtrans yang sama dikirim dua kali
    Then pembayaran tetap lunas tanpa percobaan QRIS tambahan