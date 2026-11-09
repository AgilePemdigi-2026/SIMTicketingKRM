Feature: Pengelolaan master data tiket
  Sebagai admin
  Saya ingin mengelola data tiket
  Agar informasi tiket wisata tersimpan dengan benar

  Background:
    Given saya login sebagai admin

  Scenario: Menampilkan daftar master tiket
    When saya membuka daftar master tiket
    Then halaman daftar master tiket tampil

  Scenario: Menambahkan tiket dengan data valid
    Given terdapat wahana untuk tiket
    When saya menambahkan tiket dengan data valid
    Then tiket berhasil disimpan

  Scenario: Mengedit data tiket
    Given terdapat tiket untuk diedit
    When saya mengubah data tiket
    Then perubahan tiket berhasil disimpan

  Scenario: Menolak tiket dengan kategori tidak valid
    Given terdapat wahana untuk tiket
    When saya menambahkan tiket dengan kategori tidak valid
    Then data tiket ditolak karena validasi

  Scenario: Menghapus data tiket
    Given terdapat tiket untuk dihapus
    When saya menghapus tiket tersebut
    Then tiket berhasil dihapus
