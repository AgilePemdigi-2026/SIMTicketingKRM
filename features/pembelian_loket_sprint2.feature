@postgres_purchase @sprint2 @loket
Feature: Pembelian tiket oleh Petugas Loket
  Sebagai petugas loket
  Saya ingin melayani pembelian tiket wisatawan
  Agar transaksi pembelian dapat diproses langsung

  Scenario: Petugas dapat membuka halaman pembelian loket
    Given saya sudah login sebagai wisatawan
    And saya login sebagai petugas pembayaran
    When saya membuka halaman pembelian tiket loket
    Then halaman pembelian tiket loket berhasil ditampilkan

  Scenario: Wisatawan tidak dapat membuka halaman loket
    Given saya sudah login sebagai wisatawan
    When saya membuka halaman pembelian tiket loket
    Then akses halaman loket ditolak


  Scenario: Petugas melihat preview harga tiket loket
    Given saya sudah login sebagai wisatawan
    And saya login sebagai petugas pembayaran
    And petugas menyiapkan data pembelian loket yang valid
    When petugas meminta preview harga tiket loket
    Then preview loket berhasil menampilkan rincian harga

  Scenario: Petugas berhasil membuat transaksi Cash
    Given saya sudah login sebagai wisatawan
    And saya login sebagai petugas pembayaran
    And petugas menyiapkan data pembelian loket yang valid
    When petugas melakukan checkout tiket loket
    Then pesanan loket tersimpan dengan pembayaran Cash pending

  Scenario: Petugas mengonfirmasi pembayaran Cash loket
    Given saya sudah login sebagai wisatawan
    And saya login sebagai petugas pembayaran
    And petugas menyiapkan data pembelian loket yang valid
    When petugas melakukan checkout tiket loket
    And petugas mengonfirmasi pembayaran Cash loket
    Then transaksi loket dan pembayaran berstatus lunas

  Scenario: Checkout loket menolak identitas wisatawan tidak cocok
    Given saya sudah login sebagai wisatawan
    And saya login sebagai petugas pembayaran
    And petugas menyiapkan data pembelian loket yang valid
    When petugas checkout menggunakan identitas wisatawan tidak cocok
    Then checkout loket ditolak tanpa membuat pesanan

  Scenario: Checkout loket menolak jumlah pengunjung tidak sesuai
    Given saya sudah login sebagai wisatawan
    And saya login sebagai petugas pembayaran
    And petugas menyiapkan data pembelian loket yang valid
    When petugas checkout dengan jumlah pengunjung tidak sesuai
    Then validasi checkout loket menolak jumlah pengunjung