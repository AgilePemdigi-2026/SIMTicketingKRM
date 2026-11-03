Feature: Master Fasilitas
  Sebagai admin
  Saya ingin mengelola data fasilitas
  Agar data fasilitas dapat digunakan dalam sistem ticketing

  Background:
    Given saya login sebagai admin

  Scenario: Admin melihat daftar fasilitas
    When admin membuka halaman master fasilitas
    Then halaman master fasilitas berhasil ditampilkan

  Scenario: Admin menambahkan fasilitas dengan sesi valid
    When admin menambahkan fasilitas dengan data valid
    Then fasilitas berhasil disimpan
    And sesi fasilitas berhasil disimpan

  Scenario: Admin mengubah data fasilitas dan sesinya
    Given terdapat fasilitas untuk diedit
    When admin mengubah data fasilitas dan sesinya
    Then data fasilitas dan sesi berhasil diperbarui

  Scenario: Admin menolak fasilitas dengan durasi sesi tidak sesuai
    When admin menambahkan fasilitas dengan durasi sesi tidak sesuai
    Then data fasilitas ditolak karena validasi sesi

  Scenario: Admin menghapus fasilitas beserta sesinya
    Given terdapat fasilitas untuk dihapus
    When admin menghapus fasilitas tersebut
    Then fasilitas dan sesinya berhasil dihapus

  Scenario: Admin menonaktifkan fasilitas
    Given terdapat fasilitas aktif untuk toggle status
    When admin menonaktifkan fasilitas tersebut
    Then status fasilitas menjadi nonaktif

  Scenario: Admin mengaktifkan fasilitas dengan sesi aktif
    Given terdapat fasilitas nonaktif dengan sesi aktif
    When admin mengaktifkan fasilitas tersebut
    Then status fasilitas menjadi aktif

  Scenario: Admin menolak aktivasi fasilitas tanpa sesi aktif
    Given terdapat fasilitas nonaktif tanpa sesi aktif
    When admin mengaktifkan fasilitas tersebut
    Then fasilitas tetap nonaktif
    And sistem menampilkan pesan fasilitas belum bisa diaktifkan