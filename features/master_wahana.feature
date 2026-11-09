Feature: Pengelolaan master data wahana
  Sebagai admin
  Saya ingin mengelola data wahana
  Agar informasi wahana wisata tersimpan dengan benar

  Background:
    Given saya login sebagai admin

  Scenario: Menampilkan daftar master wahana
    When saya membuka daftar master wahana
    Then halaman daftar master wahana tampil

  Scenario: Menambahkan wahana dengan data valid
    When saya menambahkan wahana dengan data valid
    Then wahana berhasil disimpan

  Scenario: Mengubah data wahana
    Given terdapat wahana untuk diedit
    When saya mengubah data wahana
    Then data wahana berhasil diperbarui

  Scenario: Menolak penambahan wahana dengan durasi tidak valid
    When saya menambahkan wahana dengan durasi 0
    Then sistem menolak data wahana
    And terdapat pesan validasi untuk durasi

  Scenario: Menghapus wahana
    Given terdapat wahana untuk dihapus
    When saya menghapus wahana tersebut
    Then wahana berhasil dihapus