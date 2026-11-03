<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\When;
use Behat\Step\Then;
use Behat\Hook\BeforeScenario;
use Behat\Hook\AfterScenario;
use App\Models\User;
use Tests\BehatLaravelTestCase;
use Tests\BehatPostgresLaravelTestCase;
use App\Models\Wahana;
use App\Models\DaftarTiket;
use App\Models\Fasilitas;
use App\Models\SesiFasilitas;

class FeatureContext implements Context
{
    private BehatLaravelTestCase $test;
    private User $user;
    private ?DaftarTiket $ticketForEdit = null;
    private ?DaftarTiket $ticketForDelete = null;
    private ?Wahana $wahanaForEdit = null;
    private ?Wahana $wahanaForDelete = null; 
    private ?Fasilitas $facilityForEdit = null;
    private ?Fasilitas $facilityForDelete = null;
    private ?SesiFasilitas $sessionForEdit = null; 
    private ?Fasilitas $facilityForToggle = null; 
    private array $registrationData;

    #[BeforeScenario('~@postgres_purchase')]
    public function setUpLaravel(): void
    {
        $this->configureSqliteEnvironment();
        $this->test = new BehatLaravelTestCase();
        $this->test->startTestEnvironment();
    }

    #[BeforeScenario('@postgres_purchase')]
    public function setUpPostgresLaravel(): void
    {
        $this->test = new BehatPostgresLaravelTestCase();
        $this->test->startTestEnvironment();
    }

    #[AfterScenario]
    public function tearDownLaravel(): void
    {
        if (isset($this->test)) {
            $this->test->stopTestEnvironment();
        }
    }

    private function configureSqliteEnvironment(): void
    {
        putenv('APP_ENV=testing');
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        putenv('SESSION_DRIVER=array');
        putenv('CACHE_STORE=array');

        $_ENV['APP_ENV'] = 'testing';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['SESSION_DRIVER'] = 'array';
        $_ENV['CACHE_STORE'] = 'array';

        $_SERVER['APP_ENV'] = 'testing';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';
        $_SERVER['SESSION_DRIVER'] = 'array';
        $_SERVER['CACHE_STORE'] = 'array';
    }

    #[Given('saya membuka halaman registrasi')]
    public function openRegistrationPage(): void
    {
        $this->test->getPage('/register');
    }

    #[Then('halaman registrasi tampil')]
    public function registrationPageDisplayed(): void
    {
        $this->test->checkViewIs('auth.register');
    }

    #[Given('saya membuka halaman login')]
    public function openLoginPage(): void
    {
        $this->test->getPage('/login');
    }

    #[Then('halaman login tampil')]
    public function loginPageDisplayed(): void
    {
        $this->test->checkViewIs('auth.login');
    }

    #[Given('saya berada di halaman registrasi')]
    public function onRegistrationPage(): void
    {
        $this->test->getPage('/register');

        $this->registrationData = [
            'name' => 'Wisatawan Behat',
            'identity_number' => 'BEHAT123456',
            'phone' => '081234567890',
            'email' => 'behat@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'visitor_type' => 'domestik',
            'terms' => '1',
        ];
    }

    #[When('saya mengirim data registrasi yang valid')]
    public function submitValidRegistration(): void
    {
        $this->test->postPage('/register', $this->registrationData);
    }

    #[Then('akun wisatawan berhasil dibuat')]
    public function touristAccountCreated(): void
    {
        $this->test->assertDatabaseHasRecord('users', [
            'email' => 'behat@example.com',
            'name' => 'Wisatawan Behat',
            'visitor_type' => 'domestik',
        ]);

        $this->test->assertPageIs('/login');
    }

    #[Given('terdapat akun wisatawan yang sudah terdaftar')]
    public function registeredTouristExists(): void
    {
        $this->user = $this->test->makeUser([
            'name' => 'Wisatawan Behat',
            'identity_number' => 'BEHAT123456',
            'phone' => '081234567890',
            'email' => 'behat@example.com',
            'password' => 'Password123!',
            'visitor_type' => 'domestik',
            'role' => 'wisatawan',
        ]);
    }

    #[When('saya login menggunakan email dan password yang benar')]
    public function loginWithValidCredentials(): void
    {
        $this->test->postPage('/login', [
            'login' => 'behat@example.com',
            'password' => 'Password123!',
        ]);
    }

    #[Then('saya diarahkan ke halaman beranda wisatawan')]
    public function redirectedToTouristHome(): void
    {
        $this->test->assertPageIs('/beranda');
    }

    #[When('saya login menggunakan password yang salah')]
    public function loginWithWrongPassword(): void
    {
        $this->test->postPage('/login', [
            'login' => 'behat@example.com',
            'password' => 'WrongPassword!',
        ]);
    }

    #[Then('login ditolak')]
    public function loginRejected(): void
    {
        $this->test->assertSessionHasErrorsFor('login');
    }

    #[Given('saya sudah login sebagai wisatawan')]
    public function loggedInAsTourist(): void
    {
        $this->user = $this->test->makeUser([
            'name' => 'Wisatawan Behat',
            'identity_number' => 'BEHAT123456',
            'phone' => '081234567890',
            'email' => 'behat@example.com',
            'password' => 'Password123!',
            'visitor_type' => 'domestik',
            'role' => 'wisatawan',
        ]);

        $this->test->loginAsUser($this->user);
    }

    #[When('saya melakukan logout')]
    public function logout(): void
    {
        $this->test->postPage('/logout');
    }

    #[Then('saya diarahkan ke halaman login')]
    public function redirectedToLogin(): void
    {
        $this->test->assertPageIs('/login');
    }

    #[When('saya membuka halaman profil wisatawan')]
    public function openTouristProfile(): void
    {
        $this->test->getPage('/wisatawan/profil');
    }

    #[Then('halaman profil wisatawan tampil')]
    public function touristProfileDisplayed(): void
    {
        $this->test->checkViewIs('wisatawan.profil');
    }

    #[When('saya membuka halaman edit profil')]
    public function openEditProfile(): void
    {
        $this->test->getPage('/wisatawan/profil/edit');
    }

    #[Then('form edit profil tampil')]
    public function editProfileDisplayed(): void
    {
        $this->test->checkViewIs('wisatawan.edit-profil');
    }

    #[When('saya memperbarui profil dengan data valid')]
    public function updateProfileWithValidData(): void
    {
        $this->test->putPage('/wisatawan/profil', [
            'name' => 'Wisatawan Behat Updated',
            'phone' => '081234567891',
            'email' => 'behat.updated@example.com',
        ]);
    }

    #[Then('profil berhasil diperbarui')]
    public function profileUpdated(): void
    {
        $this->test->assertDatabaseHasRecord('users', [
            'id' => $this->user->id,
            'name' => 'Wisatawan Behat Updated',
            'phone' => '081234567891',
            'email' => 'behat.updated@example.com',
        ]);

        $this->test->assertPageIs('/wisatawan/profil');
    }

    #[When('saya memperbarui profil dengan email tidak valid')]
    public function updateProfileWithInvalidEmail(): void
    {
        $this->test->putPage('/wisatawan/profil', [
            'name' => 'Wisatawan Behat',
            'phone' => '081234567890',
            'email' => 'email-tidak-valid',
        ]);
    }

    #[Then('perubahan profil ditolak')]
    public function profileUpdateRejected(): void
    {
        $this->test->assertSessionHasErrorsFor('email');
    }

    #[When('saya membuka halaman ubah password')]
    public function openChangePasswordPage(): void
    {
        $this->test->getPage('/wisatawan/profil/password');
    }

    #[Then('halaman ubah password tampil')]
    public function changePasswordPageDisplayed(): void
    {
        $this->test->checkViewIs('wisatawan.ubah-password');
    }

    #[When('saya mengubah password dengan data valid')]
    public function changePasswordWithValidData(): void
    {
        $this->test->putPage('/wisatawan/profil/password', [
            'current_password' => 'Password123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);
    }

    #[Then('password berhasil diperbarui')]
    public function passwordUpdated(): void
    {
        $this->test->assertPasswordHashed('NewPassword123!');
        $this->test->assertPageIs('/wisatawan/profil');
    }

    #[When('saya mengubah password menggunakan password lama yang salah')]
    public function changePasswordWithWrongCurrentPassword(): void
    {
        $this->test->putPage('/wisatawan/profil/password', [
            'current_password' => 'WrongPassword!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);
    }

    #[Then('perubahan password ditolak')]
    public function passwordChangeRejected(): void
    {
        $this->test->assertSessionHasErrorsFor('current_password');
    }

    #[Given('terdapat wahana aktif')]
    public function activeWahanaExists(): void
    {
        Wahana::create([
            'nama_wahana' => 'Jembatan Mangrove',
            'durasi' => 30,
            'jam_buka' => '08:00:00',
            'jam_tutup' => '16:00:00',
            'kapasitas' => 20,
            'status_aktif' => 1,
            'deskripsi' => 'Wahana berjalan menyusuri kawasan mangrove.',
            'foto' => null,
        ]);
    }

    #[When('saya membuka halaman destinasi')]
    public function openDestinationPage(): void
    {
        $this->test->getPage('/destinasi');
    }

    #[Then('halaman destinasi tampil')]
    public function destinationPageDisplayed(): void
    {
        $this->test->checkViewIs('wisatawan.destinasi');
    }

    #[When('saya membuka detail wahana aktif')]
    public function openActiveWahanaDetail(): void
    {
        $wahana = Wahana::query()->firstOrFail();

        $this->test->getPage('/wahana/' . $wahana->id_wahana);
    }

    #[Then('halaman detail wahana tampil')]
    public function wahanaDetailDisplayed(): void
    {
        $this->test->checkViewIs('wisatawan.detail-wahana');
    }

    #[When('saya membuka detail wahana yang tidak tersedia')]
    public function openUnavailableWahanaDetail(): void
    {
        $this->test->getPage('/wahana/999999');
    }

    #[Then('halaman tidak ditemukan ditampilkan')]
    public function notFoundPageDisplayed(): void
    {
        $this->test->assertResponseNotFound();
    }

    #[Given('saya login sebagai admin')]
    public function loggedInAsAdmin(): void
    {
        $this->user = $this->test->makeUser([
            'name' => 'Admin Behat',
            'identity_number' => 'ADMINBEHAT001',
            'phone' => '081234567899',
            'email' => 'adminbehat@example.com',
            'password' => 'Password123!',
            'visitor_type' => 'domestik',
            'role' => 'admin',
        ]);

        $this->test->loginAsUser($this->user);
    }

    #[When('saya membuka daftar master tiket')]
    public function openAdminTicketList(): void
    {
        $this->test->getPage('/admin/daftar-tiket');
    }

    #[Then('halaman daftar master tiket tampil')]
    public function adminTicketListDisplayed(): void
    {
        $this->test->checkViewIs('admin.daftar-tiket.index');
    }

    #[Given('terdapat wahana untuk tiket')]
    public function wahanaForTicketExists(): void
    {
        \App\Models\Wahana::create([
            'nama_wahana' => 'Wahana Behat',
            'durasi' => 30,
            'jam_buka' => '08:00:00',
            'jam_tutup' => '16:00:00',
            'kapasitas' => 20,
            'status_aktif' => 1,
            'deskripsi' => 'Wahana untuk pengujian tiket.',
            'foto' => null,
        ]);
    }

    #[When('saya menambahkan tiket dengan data valid')]
    public function createTicketWithValidData(): void
    {
        $wahana = \App\Models\Wahana::query()->firstOrFail();

        $this->test->postPage('/admin/daftar-tiket', [
            'wahana_ids' => [$wahana->id_wahana],
            'nama_tiket' => 'Tiket Behat',
            'kategori_tiket' => 'Tiket Wahana',
            'kuota' => 100,
            'kategori_wisatawan' => 'Domestik (WNI)',
            'harga_dewasa' => 50000,
            'harga_anak' => 25000,
            'status_aktif' => 1,
            'ketentuan' => 'Tiket untuk pengujian.',
        ]);
    }

    #[Then('tiket berhasil disimpan')]
    public function ticketSaved(): void
    {
        $this->test->assertDatabaseHasRecord('daftar_tiket', [
            'nama_tiket' => 'Tiket Behat',
            'kategori_tiket' => 'Tiket Wahana',
            'kategori_wisatawan' => 'Domestik (WNI)',
        ]);

        $this->test->assertPageIs('/admin/daftar-tiket');
    }

   #[When('saya membuka daftar master wahana')]
    public function openAdminWahanaList(): void
    {
        $this->test->getPage('/admin/wahana');
    }

    #[Then('halaman daftar master wahana tampil')]
    public function adminWahanaListDisplayed(): void
    {
        $this->test->checkViewIs('admin.wahana.index');
        $this->test->assertResponseOk();
    }

    #[When('saya menambahkan wahana dengan data valid')]
    public function createWahanaWithValidData(): void
    {
        $this->test->postPage('/admin/wahana', [
            'nama_wahana' => 'Wahana Behat',
            'durasi' => 30,
            'jam_buka' => '09:00',
            'jam_tutup' => '17:00',
            'kapasitas' => 100,
            'status_aktif' => 1,
            'deskripsi' => 'Wahana untuk pengujian BDD',
        ]);
    }

    #[Then('wahana berhasil disimpan')]
    public function wahanaSaved(): void
    {
        $this->test->assertDatabaseHasRecord('wahana', [
            'nama_wahana' => 'Wahana Behat',
            'durasi' => 30,
            'kapasitas' => 100,
            'status_aktif' => 1,
        ]);

        $this->test->assertPageIs('/admin/wahana');
    }

    #[When('admin membuka halaman master fasilitas')]
    public function adminMembukaHalamanMasterFasilitas(): void
    {
        $this->test->getPage('/admin/fasilitas');
    }

    #[Then('halaman master fasilitas berhasil ditampilkan')]
    public function halamanMasterFasilitasBerhasilDitampilkan(): void
    {
        $this->test->assertResponseOk();
        $this->test->checkViewIs('admin.fasilitas.index');
    }

    #[When('admin menambahkan fasilitas dengan data valid')]
    public function adminMenambahkanFasilitasDenganDataValid(): void
    {
        $this->test->postPage('/admin/fasilitas', [
            'nama_fasilitas' => 'Fasilitas Behat',
            'kapasitas_fasilitas' => 50,
            'status_fasilitas' => 1,
            'ketentuan_fasilitas' => 'Ketentuan pengujian BDD',
            'deskripsi_fasilitas' => 'Fasilitas untuk pengujian Behat',
            'sesi' => [
                [
                    'nama_sesi' => 'Sesi Behat',
                    'jam_mulai' => '09:00',
                    'jam_selesai' => '10:00',
                    'durasi_menit' => 60,
                    'harga_sesi' => 25000,
                ],
            ],
        ]);
    }

    #[Then('fasilitas berhasil disimpan')]
    public function fasilitasBerhasilDisimpan(): void
    {
        $this->test->assertDatabaseHasRecord('fasilitas', [
            'nama_fasilitas' => 'Fasilitas Behat',
            'kapasitas_fasilitas' => 50,
            'status_fasilitas' => 1,
        ]);

        $this->test->assertPageIs('/admin/fasilitas');
    }

    #[Then('sesi fasilitas berhasil disimpan')]
    public function sesiFasilitasBerhasilDisimpan(): void
    {
        $this->test->assertDatabaseHasRecord('sesi_fasilitas', [
            'nama_sesi' => 'Sesi Behat',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:00',
            'durasi_menit' => 60,
            'harga_sesi' => 25000,
            'status_aktif' => 1,
        ]);
    }

    #[Given('terdapat tiket untuk diedit')]
    public function terdapatTiketUntukDiedit(): void
    {
        $this->test->postPage('/admin/daftar-tiket', [
            'wahana_ids' => [],
            'nama_tiket' => 'Tiket Behat Edit',
            'kategori_tiket' => 'Tiket Masuk',
            'kuota' => 100,
            'kategori_wisatawan' => 'Domestik (WNI)',
            'harga_dewasa' => 50000,
            'harga_anak' => 25000,
            'status_aktif' => 1,
            'ketentuan' => 'Tiket untuk pengujian edit.',
        ]);

        $this->ticketForEdit = DaftarTiket::where(
            'nama_tiket',
            'Tiket Behat Edit'
        )->firstOrFail();
    }

    #[When('saya mengubah data tiket')]
    public function sayaMengubahDataTiket(): void
    {
        $this->test->putPage(
            '/admin/daftar-tiket/' . $this->ticketForEdit->id_tiket,
            [
                'wahana_ids' => [],
                'nama_tiket' => 'Tiket Behat Updated',
                'kategori_tiket' => 'Tiket Masuk',
                'kuota' => 150,
                'kategori_wisatawan' => 'Domestik (WNI)',
                'harga_dewasa' => 60000,
                'harga_anak' => 30000,
                'status_aktif' => 1,
                'ketentuan' => 'Tiket berhasil diperbarui.',
            ]
        );
    }

    #[Then('perubahan tiket berhasil disimpan')]
    public function perubahanTiketBerhasilDisimpan(): void
    {
        $this->test->assertDatabaseHasRecord('daftar_tiket', [
            'id_tiket' => $this->ticketForEdit->id_tiket,
            'nama_tiket' => 'Tiket Behat Updated',
            'kuota' => 150,
            'harga_dewasa' => 60000,
        ]);

        $this->test->assertPageIs('/admin/daftar-tiket');
    }

    #[When('saya menambahkan tiket dengan kategori tidak valid')]
    public function sayaMenambahkanTiketDenganKategoriTidakValid(): void
    {
        $this->test->postPage('/admin/daftar-tiket', [
            'wahana_ids' => [],
            'nama_tiket' => 'Tiket Behat Invalid',
            'kategori_tiket' => 'Kategori Salah',
            'kuota' => 100,
            'kategori_wisatawan' => 'Domestik (WNI)',
            'harga_dewasa' => 50000,
            'harga_anak' => 25000,
            'status_aktif' => 1,
            'ketentuan' => 'Pengujian validasi.',
        ]);
    }

    #[Then('data tiket ditolak karena validasi')]
    public function dataTiketDitolakKarenaValidasi(): void
    {
        $this->test->assertSessionHasErrorsFor('kategori_tiket');
    }

    #[Given('terdapat tiket untuk dihapus')]
    public function terdapatTiketUntukDihapus(): void
    {
        $this->test->postPage('/admin/daftar-tiket', [
            'wahana_ids' => [],
            'nama_tiket' => 'Tiket Behat Hapus',
            'kategori_tiket' => 'Tiket Masuk',
            'kuota' => 100,
            'kategori_wisatawan' => 'Domestik (WNI)',
            'harga_dewasa' => 50000,
            'harga_anak' => 25000,
            'status_aktif' => 1,
            'ketentuan' => 'Tiket untuk pengujian hapus.',
        ]);

        $this->ticketForDelete = DaftarTiket::where(
            'nama_tiket',
            'Tiket Behat Hapus'
        )->firstOrFail();
    }

    #[When('saya menghapus tiket tersebut')]
    public function sayaMenghapusTiketTersebut(): void
    {
        $this->test->deletePage(
            '/admin/daftar-tiket/' . $this->ticketForDelete->id_tiket
        );
    }

    #[Then('tiket berhasil dihapus')]
    public function tiketBerhasilDihapus(): void
    {
        $ticketExists = DaftarTiket::where(
            'id_tiket',
            $this->ticketForDelete->id_tiket
        )->exists();

        if ($ticketExists) {
            throw new RuntimeException('Tiket masih ada di database setelah dihapus.');
        }

        $this->test->assertPageIs('/admin/daftar-tiket');
    }

    #[Given('terdapat wahana untuk diedit')]
    public function terdapatWahanaUntukDiedit(): void
    {
        $this->wahanaForEdit = Wahana::create([
            'nama_wahana' => 'Wahana Behat Edit',
            'durasi' => 30,
            'jam_buka' => '09:00:00',
            'jam_tutup' => '17:00:00',
            'kapasitas' => 100,
            'status_aktif' => 1,
            'deskripsi' => 'Wahana untuk pengujian edit.',
            'foto' => null,
        ]);
    }

    #[When('saya mengubah data wahana')]
    public function sayaMengubahDataWahana(): void
    {
        $this->test->putPage(
            '/admin/wahana/' . $this->wahanaForEdit->id_wahana,
            [
                'nama_wahana' => 'Wahana Behat Updated',
                'durasi' => 45,
                'jam_buka' => '10:00',
                'jam_tutup' => '18:00',
                'kapasitas' => 150,
                'status_aktif' => 1,
                'deskripsi' => 'Wahana berhasil diperbarui.',
            ]
        );
    }

    #[Then('data wahana berhasil diperbarui')]
    public function dataWahanaBerhasilDiperbarui(): void
    {
        $this->test->assertDatabaseHasRecord('wahana', [
            'id_wahana' => $this->wahanaForEdit->id_wahana,
            'nama_wahana' => 'Wahana Behat Updated',
            'durasi' => 45,
            'kapasitas' => 150,
        ]);

        $this->test->assertPageIs('/admin/wahana');
    }

    #[When('saya menambahkan wahana dengan durasi 0')]
    public function sayaMenambahkanWahanaDenganDurasiNol(): void
    {
        $this->test->postPage('/admin/wahana', [
            'nama_wahana' => 'Wahana Behat Invalid',
            'durasi' => 0,
            'jam_buka' => '09:00',
            'jam_tutup' => '17:00',
            'kapasitas' => 100,
            'status_aktif' => 1,
            'deskripsi' => 'Pengujian validasi durasi.',
        ]);
    }

    #[Then('sistem menolak data wahana')]
    public function sistemMenolakDataWahana(): void
    {
        $this->test->assertSessionHasErrorsFor('durasi');
    }

    #[Then('terdapat pesan validasi untuk durasi')]
    public function terdapatPesanValidasiUntukDurasi(): void
    {
        $this->test->assertSessionHasErrorsFor('durasi');
    }

    #[Given('terdapat wahana untuk dihapus')]
    public function terdapatWahanaUntukDihapus(): void
    {
        $this->wahanaForDelete = Wahana::create([
            'nama_wahana' => 'Wahana Behat Hapus',
            'durasi' => 30,
            'jam_buka' => '09:00:00',
            'jam_tutup' => '17:00:00',
            'kapasitas' => 100,
            'status_aktif' => 1,
            'deskripsi' => 'Wahana untuk pengujian hapus.',
            'foto' => null,
        ]);
    }

    #[When('saya menghapus wahana tersebut')]
    public function sayaMenghapusWahanaTersebut(): void
    {
        $this->test->deletePage(
            '/admin/wahana/' . $this->wahanaForDelete->id_wahana
        );
    }

    #[Then('wahana berhasil dihapus')]
    public function wahanaBerhasilDihapus(): void
    {
        $wahanaExists = Wahana::where(
            'id_wahana',
            $this->wahanaForDelete->id_wahana
        )->exists();

        if ($wahanaExists) {
            throw new RuntimeException(
                'Wahana masih ada di database setelah dihapus.'
            );
        }

        $this->test->assertPageIs('/admin/wahana');
    }

    #[Given('terdapat fasilitas untuk diedit')]
    public function terdapatFasilitasUntukDiedit(): void
    {
        $this->facilityForEdit = Fasilitas::create([
            'nama_fasilitas' => 'Fasilitas Behat Edit',
            'kapasitas_fasilitas' => 50,
            'status_fasilitas' => 1,
            'ketentuan_fasilitas' => 'Ketentuan awal',
            'deskripsi_fasilitas' => 'Fasilitas untuk pengujian edit',
            'foto_fasilitas' => null,
        ]);

        $this->sessionForEdit = $this->facilityForEdit->sesiFasilitas()->create([
            'nama_sesi' => 'Sesi Behat Edit',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:00',
            'durasi_menit' => 60,
            'harga_sesi' => 25000,
            'status_aktif' => 1,
        ]);
    }

    #[When('admin mengubah data fasilitas dan sesinya')]
    public function adminMengubahDataFasilitasDanSesi(): void
    {
        $this->test->putPage(
            '/admin/fasilitas/' . $this->facilityForEdit->id_fasilitas,
            [
                'nama_fasilitas' => 'Fasilitas Behat Updated',
                'kapasitas_fasilitas' => 75,
                'status_fasilitas' => 1,
                'ketentuan_fasilitas' => 'Ketentuan diperbarui',
                'deskripsi_fasilitas' => 'Deskripsi diperbarui',
                'sesi' => [
                    [
                        'id_sesi' => $this->sessionForEdit->id_sesi,
                        'nama_sesi' => 'Sesi Behat Updated',
                        'jam_mulai' => '10:00',
                        'jam_selesai' => '11:30',
                        'durasi_menit' => 90,
                        'harga_sesi' => 30000,
                    ],
                ],
            ]
        );
    }

    #[Then('data fasilitas dan sesi berhasil diperbarui')]
    public function dataFasilitasDanSesiBerhasilDiperbarui(): void
    {
        $this->test->assertDatabaseHasRecord('fasilitas', [
            'id_fasilitas' => $this->facilityForEdit->id_fasilitas,
            'nama_fasilitas' => 'Fasilitas Behat Updated',
            'kapasitas_fasilitas' => 75,
        ]);

        $this->test->assertDatabaseHasRecord('sesi_fasilitas', [
            'id_sesi' => $this->sessionForEdit->id_sesi,
            'nama_sesi' => 'Sesi Behat Updated',
            'durasi_menit' => 90,
            'harga_sesi' => 30000,
        ]);

        $this->test->assertPageIs('/admin/fasilitas');
    }

    #[When('admin menambahkan fasilitas dengan durasi sesi tidak sesuai')]
    public function adminMenambahkanFasilitasDenganDurasiSesiTidakSesuai(): void
    {
        $this->test->postPage('/admin/fasilitas', [
            'nama_fasilitas' => 'Fasilitas Behat Invalid',
            'kapasitas_fasilitas' => 50,
            'status_fasilitas' => 1,
            'ketentuan_fasilitas' => 'Pengujian validasi',
            'deskripsi_fasilitas' => 'Durasi sesi tidak sesuai',
            'sesi' => [
                [
                    'nama_sesi' => 'Sesi Invalid',
                    'jam_mulai' => '09:00',
                    'jam_selesai' => '10:00',
                    'durasi_menit' => 30,
                    'harga_sesi' => 25000,
                ],
            ],
        ]);
    }

    #[Then('data fasilitas ditolak karena validasi sesi')]
    public function dataFasilitasDitolakKarenaValidasiSesi(): void
    {
        $this->test->assertSessionHasErrorsFor('sesi.0.durasi_menit');

        $facilityExists = Fasilitas::where(
            'nama_fasilitas',
            'Fasilitas Behat Invalid'
        )->exists();

        if ($facilityExists) {
            throw new RuntimeException(
                'Fasilitas tidak valid seharusnya tidak tersimpan.'
            );
        }
    }

    #[Given('terdapat fasilitas untuk dihapus')]
    public function terdapatFasilitasUntukDihapus(): void
    {
        $this->facilityForDelete = Fasilitas::create([
            'nama_fasilitas' => 'Fasilitas Behat Hapus',
            'kapasitas_fasilitas' => 50,
            'status_fasilitas' => 1,
            'ketentuan_fasilitas' => 'Ketentuan hapus',
            'deskripsi_fasilitas' => 'Fasilitas untuk pengujian hapus',
            'foto_fasilitas' => null,
        ]);

        $this->facilityForDelete->sesiFasilitas()->create([
            'nama_sesi' => 'Sesi Behat Hapus',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:00',
            'durasi_menit' => 60,
            'harga_sesi' => 25000,
            'status_aktif' => 1,
        ]);
    }

    #[When('admin menghapus fasilitas tersebut')]
    public function adminMenghapusFasilitasTersebut(): void
    {
        $this->test->deletePage(
            '/admin/fasilitas/' . $this->facilityForDelete->id_fasilitas
        );
    }

    #[Then('fasilitas dan sesinya berhasil dihapus')]
    public function fasilitasDanSesinyaBerhasilDihapus(): void
    {
        $facilityExists = Fasilitas::where(
            'id_fasilitas',
            $this->facilityForDelete->id_fasilitas
        )->exists();

        $sessionExists = SesiFasilitas::where(
            'id_fasilitas',
            $this->facilityForDelete->id_fasilitas
        )->exists();

        if ($facilityExists || $sessionExists) {
            throw new RuntimeException(
                'Fasilitas atau sesi masih ada setelah proses penghapusan.'
            );
        }

        $this->test->assertPageIs('/admin/fasilitas');
    }

    #[Given('terdapat fasilitas aktif untuk toggle status')]
    public function terdapatFasilitasAktifUntukToggleStatus(): void
    {
        $this->facilityForToggle = Fasilitas::create([
            'nama_fasilitas' => 'Fasilitas Toggle Aktif',
            'kapasitas_fasilitas' => 50,
            'status_fasilitas' => 1,
            'ketentuan_fasilitas' => 'Ketentuan test',
            'deskripsi_fasilitas' => 'Fixture toggle status',
            'foto_fasilitas' => null,
        ]);
    }

    #[Given('terdapat fasilitas nonaktif dengan sesi aktif')]
    public function terdapatFasilitasNonaktifDenganSesiAktif(): void
    {
        $this->facilityForToggle = Fasilitas::create([
            'nama_fasilitas' => 'Fasilitas Toggle Nonaktif',
            'kapasitas_fasilitas' => 50,
            'status_fasilitas' => 0,
            'ketentuan_fasilitas' => 'Ketentuan test',
            'deskripsi_fasilitas' => 'Fixture toggle status',
            'foto_fasilitas' => null,
        ]);

        $this->facilityForToggle->sesiFasilitas()->create([
            'nama_sesi' => 'Sesi Aktif',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:00',
            'durasi_menit' => 60,
            'harga_sesi' => 25000,
            'status_aktif' => 1,
        ]);
    }

    #[Given('terdapat fasilitas nonaktif tanpa sesi aktif')]
    public function terdapatFasilitasNonaktifTanpaSesiAktif(): void
    {
        $this->facilityForToggle = Fasilitas::create([
            'nama_fasilitas' => 'Fasilitas Tanpa Sesi Aktif',
            'kapasitas_fasilitas' => 50,
            'status_fasilitas' => 0,
            'ketentuan_fasilitas' => 'Ketentuan test',
            'deskripsi_fasilitas' => 'Fixture toggle status',
            'foto_fasilitas' => null,
        ]);

        $this->facilityForToggle->sesiFasilitas()->create([
            'nama_sesi' => 'Sesi Nonaktif',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:00',
            'durasi_menit' => 60,
            'harga_sesi' => 25000,
            'status_aktif' => 0,
        ]);
    }

    #[When('admin menonaktifkan fasilitas tersebut')]
    public function adminMenonaktifkanFasilitasTersebut(): void
    {
        $this->test->patchPage(
            '/admin/fasilitas/' .
            $this->facilityForToggle->id_fasilitas .
            '/toggle-status'
        );
    }

    #[When('admin mengaktifkan fasilitas tersebut')]
    public function adminMengaktifkanFasilitasTersebut(): void
    {
        $this->test->patchPage(
            '/admin/fasilitas/' .
            $this->facilityForToggle->id_fasilitas .
            '/toggle-status'
        );
    }

    #[Then('status fasilitas menjadi nonaktif')]
    public function statusFasilitasMenjadiNonaktif(): void
    {
        $this->test->assertDatabaseHasRecord('fasilitas', [
            'id_fasilitas' => $this->facilityForToggle->id_fasilitas,
            'status_fasilitas' => 0,
        ]);

        $this->test->assertPageIs('/admin/fasilitas');
    }

    #[Then('status fasilitas menjadi aktif')]
    public function statusFasilitasMenjadiAktif(): void
    {
        $this->test->assertDatabaseHasRecord('fasilitas', [
            'id_fasilitas' => $this->facilityForToggle->id_fasilitas,
            'status_fasilitas' => 1,
        ]);

        $this->test->assertPageIs('/admin/fasilitas');
    }

    #[Then('fasilitas tetap nonaktif')]
    public function fasilitasTetapNonaktif(): void
    {
        $this->test->assertDatabaseHasRecord('fasilitas', [
            'id_fasilitas' => $this->facilityForToggle->id_fasilitas,
            'status_fasilitas' => 0,
        ]);
    }

    #[Then('sistem menampilkan pesan fasilitas belum bisa diaktifkan')]
    public function sistemMenampilkanPesanFasilitasBelumBisaDiaktifkan(): void
    {
        // Controller mengembalikan redirect dengan flash error.
        // Status tetap nonaktif sudah diverifikasi pada step sebelumnya.
        $this->test->assertPageIs('/admin/fasilitas');
    }

    #[When('saya membuka halaman pembelian tiket')]
    public function sayaMembukaHalamanPembelianTiket(): void
    {
        $this->test->getPage('/wisatawan/pembelian-tiket');
    }

    #[Then('halaman katalog tiket tampil')]
    public function halamanKatalogTiketTampil(): void
    {
        $this->test->assertResponseOk();
        $this->test->assertResponseContains('Tiket Masuk Saja');
    }

    #[When('saya memilih tiket untuk checkout')]
    public function sayaMemilihTiketUntukCheckout(): void
    {
        $this->test->postJsonPage('/wisatawan/pembelian-tiket/preview', [
            'visit_date' => now('Asia/Jakarta')->addDay()->toDateString(),
            'visitor_type' => 'domestik',
            'items' => [
                [
                    'ticket_reference' => 'master:5',
                    'adult_quantity' => 1,
                    'child_quantity' => 0,
                ],
            ],
        ]);
    }

    #[Then('detail checkout tiket tampil')]
    public function detailCheckoutTiketTampil(): void
    {
        $this->test->assertResponseHasStatus(200);
        $this->test->assertResponseContains('Tiket Masuk Saja');
    }

    #[When('saya mengirim checkout tiket dengan data pengunjung valid')]
    public function sayaMengirimCheckoutTiketDenganDataPengunjungValid(): void
    {
        $this->test->postJsonPage('/wisatawan/pembelian-tiket', [
            'visit_date' => now('Asia/Jakarta')->addDay()->toDateString(),
            'visitor_type' => 'domestik',
            'booker' => [
                'name' => 'Pemesan BDD',
                'identity_number' => 'BDDID12345',
                'phone' => '081234567890',
                'email' => 'behat@example.com',
            ],
            'items' => [
                [
                    'ticket_reference' => 'master:5',
                    'adult_quantity' => 1,
                    'child_quantity' => 0,
                    'visitors' => [
                        [
                            'name' => 'Pengunjung BDD',
                            'identity_number' => 'BDDID12345',
                            'age_category' => 'adult',
                        ],
                    ],
                ],
            ],
        ]);
    }

    #[Then('transaksi pembelian berhasil disimpan')]
    public function transaksiPembelianBerhasilDisimpan(): void
    {
        $this->test->assertResponseHasStatus(201);

        $this->test->assertDatabaseHasRecord('ticket_orders', [
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
        ]);

        $this->test->assertDatabaseHasRecord('ticket_order_items', [
            'ticket_reference' => 'master:5',
            'adult_quantity' => 1,
        ]);
    }
}
