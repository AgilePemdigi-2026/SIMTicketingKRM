Feature: Informasi destinasi dan wahana
  Sebagai wisatawan
  Saya ingin melihat informasi destinasi dan wahana
  Agar dapat mengetahui tempat wisata yang tersedia

  Scenario: Menampilkan halaman destinasi
    Given saya sudah login sebagai wisatawan
    When saya membuka halaman destinasi
    Then halaman destinasi tampil

  Scenario: Menampilkan detail wahana aktif
    Given saya sudah login sebagai wisatawan
    And terdapat wahana aktif
    When saya membuka detail wahana aktif
    Then halaman detail wahana tampil

  Scenario: Detail wahana yang tidak tersedia
    Given saya sudah login sebagai wisatawan
    When saya membuka detail wahana yang tidak tersedia
    Then halaman tidak ditemukan ditampilkan