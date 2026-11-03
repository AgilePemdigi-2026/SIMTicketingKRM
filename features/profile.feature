Feature: Pengelolaan Profil Wisatawan
  Sebagai wisatawan yang sudah login
  Saya ingin melihat dan mengelola profil
  Agar data pribadi dan password saya tetap terbarui

  Scenario: Menampilkan halaman profil
    Given saya sudah login sebagai wisatawan
    When saya membuka halaman profil wisatawan
    Then halaman profil wisatawan tampil

  Scenario: Menampilkan form edit profil
    Given saya sudah login sebagai wisatawan
    When saya membuka halaman edit profil
    Then form edit profil tampil

  Scenario: Memperbarui profil dengan data valid
    Given saya sudah login sebagai wisatawan
    When saya memperbarui profil dengan data valid
    Then profil berhasil diperbarui

  Scenario: Menolak email yang tidak valid
    Given saya sudah login sebagai wisatawan
    When saya memperbarui profil dengan email tidak valid
    Then perubahan profil ditolak

  Scenario: Menampilkan halaman ubah password
    Given saya sudah login sebagai wisatawan
    When saya membuka halaman ubah password
    Then halaman ubah password tampil

  Scenario: Mengubah password dengan data valid
    Given saya sudah login sebagai wisatawan
    When saya mengubah password dengan data valid
    Then password berhasil diperbarui

  Scenario: Menolak perubahan password dengan password lama salah
    Given saya sudah login sebagai wisatawan
    When saya mengubah password menggunakan password lama yang salah
    Then perubahan password ditolak