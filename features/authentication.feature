Feature: Autentikasi wisatawan
  Sebagai wisatawan
  Saya ingin mendaftar, login, dan logout
  Agar dapat mengakses fitur wisatawan

  Scenario: Menampilkan halaman registrasi
    Given saya membuka halaman registrasi
    Then halaman registrasi tampil

  Scenario: Menampilkan halaman login
    Given saya membuka halaman login
    Then halaman login tampil

  Scenario: Registrasi dengan data valid
    Given saya berada di halaman registrasi
    When saya mengirim data registrasi yang valid
    Then akun wisatawan berhasil dibuat

  Scenario: Login dengan email dan password valid
    Given terdapat akun wisatawan yang sudah terdaftar
    When saya login menggunakan email dan password yang benar
    Then saya diarahkan ke halaman beranda wisatawan

  Scenario: Login dengan password salah
    Given terdapat akun wisatawan yang sudah terdaftar
    When saya login menggunakan password yang salah
    Then login ditolak

  Scenario: Logout dari akun wisatawan
    Given saya sudah login sebagai wisatawan
    When saya melakukan logout
    Then saya diarahkan ke halaman login