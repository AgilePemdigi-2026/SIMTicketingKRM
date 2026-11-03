@postgres_purchase
Feature: Pembelian tiket wisatawan
  Sebagai wisatawan
  Saya ingin memilih tiket dan mengisi data pengunjung
  Agar transaksi pembelian tiket tersimpan

  Scenario: Wisatawan membuka katalog tiket aktif
    Given saya sudah login sebagai wisatawan
    When saya membuka halaman pembelian tiket
    Then halaman katalog tiket tampil

  Scenario: Wisatawan melihat detail checkout tiket
    Given saya sudah login sebagai wisatawan
    When saya membuka halaman pembelian tiket
    And saya memilih tiket untuk checkout
    Then detail checkout tiket tampil

  Scenario: Wisatawan menyimpan transaksi pembelian
    Given saya sudah login sebagai wisatawan
    When saya mengirim checkout tiket dengan data pengunjung valid
    Then transaksi pembelian berhasil disimpan