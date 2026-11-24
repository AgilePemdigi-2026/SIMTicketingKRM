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
use App\Models\TicketOrder;
use App\Models\TicketPayment;
use App\Contracts\TicketQrisGateway;
use App\Models\TicketPaymentGatewayAttempt;
use Illuminate\Support\Facades\Http;
use App\Events\TicketPaymentConfirmed;
use App\Listeners\IssueTicketsAfterPayment;
use App\Models\IssuedTicket;

class FeatureContext implements Context
{
    private BehatLaravelTestCase $test;
    private User $user;
    private ?TicketOrder $sprint2Order = null;
    private ?TicketPayment $sprint2Payment = null;
    private ?User $sprint2Staff = null;
    private ?DaftarTiket $ticketForEdit = null;
    private ?DaftarTiket $ticketForDelete = null;
    private ?Wahana $wahanaForEdit = null;
    private ?Wahana $wahanaForDelete = null; 
    private ?Fasilitas $facilityForEdit = null;
    private ?Fasilitas $facilityForDelete = null;
    private ?SesiFasilitas $sessionForEdit = null; 
    private ?Fasilitas $facilityForToggle = null; 
    private array $registrationData;
    private ?TicketPaymentGatewayAttempt $sprint2Attempt = null;
    private ?int $sprint2WebhookStatus = null;
    private ?string $sprint2FirstPaidAt = null;
    private array $sprint2KodeTiketSebelumPengulangan = [];
    private ?array $sprint2ValidationResult = null;
    private ?int $sprint2CheckInHttpStatus = null;
    private ?string $sprint2FirstUsedAt = null;
    private array $sprint2LoketData = [];
    private ?\Illuminate\Testing\TestResponse $sprint2LoketResponse = null;
    private const SPRINT2_MIDTRANS_KEY = 'bdd-sandbox-dummy-key';

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

    #[Given('saya memiliki pesanan tiket yang belum dibayar')]
    public function memilikiPesananSprint2(): void
    {
        // Menggunakan step checkout Sprint 1 yang sudah Passed.
        $this->sayaMengirimCheckoutTiketDenganDataPengunjungValid();
        $this->test->assertResponseHasStatus(201);

        $this->sprint2Order = TicketOrder::query()
            ->where('user_id', $this->user->id)
            ->orderByDesc('id')
            ->firstOrFail();
    }

    private function paymentUrlSprint2(): string
    {
        if ($this->sprint2Order === null) {
            throw new \RuntimeException('Pesanan BDD belum dibuat.');
        }

        return '/wisatawan/pembelian-tiket/'
            . $this->sprint2Order->code
            . '/pembayaran';
    }

    private function pilihPembayaranSprint2(string $method): void
    {
        $response = $this->test->postJsonPage(
            $this->paymentUrlSprint2(),
            ['method' => $method]
        );

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                'Pemilihan pembayaran gagal. HTTP '
                . $response->getStatusCode()
            );
        }

        $this->sprint2Payment = TicketPayment::query()
            ->where('ticket_order_id', $this->sprint2Order->id)
            ->firstOrFail();
    }

    #[When('saya memilih pembayaran Cash untuk pesanan')]
    public function memilihCashSprint2(): void
    {
        $this->pilihPembayaranSprint2('cash');
    }

    #[When('saya memilih pembayaran QRIS untuk pesanan')]
    public function memilihQrisSprint2(): void
    {
        $this->pilihPembayaranSprint2('qris');
    }

    #[Then('pembayaran Cash menunggu konfirmasi petugas')]
    public function cashPendingSprint2(): void
    {
        $this->test->assertDatabaseHasRecord('ticket_payments', [
            'id' => $this->sprint2Payment->id,
            'method' => 'cash',
            'status' => 'pending',
        ]);

        $this->test->assertDatabaseHasRecord('ticket_orders', [
            'id' => $this->sprint2Order->id,
            'status' => 'pending_payment',
        ]);
    }

    #[Then('halaman pembayaran menampilkan kode Cash')]
    public function halamanCashSprint2(): void
    {
        $this->test->getPage($this->paymentUrlSprint2());
        $this->test->assertResponseOk();
        $this->test->assertResponseContains('KODE PEMBAYARAN CASH');
    }

    #[When('saya login sebagai petugas pembayaran')]
    public function loginPetugasSprint2(): void
    {
        $this->sprint2Staff = $this->test->makeUser([
            'name' => 'Petugas BDD Sprint 2',
            'identity_number' => 'PETUGASBDD002',
            'phone' => '081234567898',
            'email' => 'petugas.bdd.sprint2@example.com',
            'password' => 'Password123!',
            'visitor_type' => 'domestik',
            'role' => 'petugas',
        ]);

        $this->test->loginAsUser($this->sprint2Staff);
    }

    #[When('saya mengonfirmasi pembayaran Cash tersebut')]
    public function konfirmasiCashSprint2(): void
    {
        $response = $this->test->postJsonPage(
            '/petugas/pembayaran/'
            . $this->sprint2Payment->id
            . '/konfirmasi',
            ['receipt_checked' => 1]
        );

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                'Konfirmasi Cash gagal. HTTP '
                . $response->getStatusCode()
            );
        }
    }

    #[Then('transaksi dan pembayaran Cash berstatus lunas')]
    public function cashLunasSprint2(): void
    {
        $this->test->assertDatabaseHasRecord('ticket_orders', [
            'id' => $this->sprint2Order->id,
            'status' => 'paid',
        ]);

        $this->test->assertDatabaseHasRecord('ticket_payments', [
            'id' => $this->sprint2Payment->id,
            'method' => 'cash',
            'status' => 'paid',
            'confirmed_by' => $this->sprint2Staff->id,
        ]);
    }

    #[Then('pembayaran QRIS masih menunggu konfirmasi Midtrans')]
    public function qrisPendingSprint2(): void
    {
        $this->test->assertDatabaseHasRecord('ticket_payments', [
            'id' => $this->sprint2Payment->id,
            'method' => 'qris',
            'status' => 'pending',
        ]);

        $this->test->assertDatabaseHasRecord('ticket_orders', [
            'id' => $this->sprint2Order->id,
            'status' => 'pending_payment',
        ]);
    }

    #[When('petugas mencoba mengonfirmasi QRIS secara manual')]
    public function konfirmasiManualQrisSprint2(): void
    {
        $response = $this->test->postJsonPage(
            '/petugas/pembayaran/'
            . $this->sprint2Payment->id
            . '/konfirmasi',
            [
                'receipt_checked' => 1,
                'merchant_reference' => 'BDD-MANUAL-001',
            ]
        );

        if ($response->getStatusCode() !== 409) {
            throw new \RuntimeException(
                'QRIS manual seharusnya ditolak HTTP 409, tetapi HTTP '
                . $response->getStatusCode()
            );
        }
    }

    #[Then('sistem menolak konfirmasi QRIS manual')]
    public function qrisManualDitolakSprint2(): void
    {
        $this->test->assertDatabaseHasRecord('ticket_payments', [
            'id' => $this->sprint2Payment->id,
            'method' => 'qris',
            'status' => 'pending',
        ]);

        $this->test->assertDatabaseHasRecord('ticket_orders', [
            'id' => $this->sprint2Order->id,
            'status' => 'pending_payment',
        ]);
    }

    #[Given('saya memiliki pesanan QRIS dari gateway simulasi')]
    public function pesananQrisDariGatewaySimulasi(): void
    {
        // Gunakan step pembayaran yang sudah Passed.
        $this->loggedInAsTourist();
        $this->memilikiPesananSprint2();
        $this->memilihQrisSprint2();

        // Credential khusus BDD, bukan key Midtrans asli.
        config()->set('midtrans.is_production', false);
        config()->set('midtrans.server_key', self::SPRINT2_MIDTRANS_KEY);

        // Tidak boleh mengirim HTTP eksternal sungguhan.
        Http::preventStrayRequests();

        $gateway = \Mockery::mock(TicketQrisGateway::class);
        $gateway->shouldReceive('createQris')
            ->once()
            ->andReturn([
                'transaction_id' => 'bdd-midtrans-transaction-001',
                'status' => 'pending',
                'qr_image_url' => 'https://example.test/qris-bdd.png',
                'qr_string' => 'DUMMY-QR-FOR-BDD',
                'expires_at' => now('Asia/Jakarta')
                    ->addMinutes(15)
                    ->format('Y-m-d H:i:s P'),
            ]);

        app()->instance(TicketQrisGateway::class, $gateway);

        // Lewat endpoint Laravel, bukan memanggil Midtrans.
        $response = $this->test->postJsonPage(
            $this->paymentUrlSprint2() . '/qris'
        );

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                'Gagal membuat QRIS simulasi. HTTP '
                . $response->getStatusCode()
                . ': ' . substr($response->getContent(), 0, 500)
            );
        }

        $this->sprint2Attempt = TicketPaymentGatewayAttempt::query()
            ->where('ticket_payment_id', $this->sprint2Payment->id)
            ->latest('id')
            ->firstOrFail();
    }

    private function payloadWebhookSprint2(
        string $status,
        array $changes = []
    ): array {
        $payload = array_replace([
            'order_id' => (string) $this->sprint2Attempt->reference_id,
            'transaction_id' => (string) $this->sprint2Attempt->provider_payment_id,
            'transaction_status' => $status,
            'status_code' => $status === 'settlement' ? '200' : '201',
            'gross_amount' => (string) $this->sprint2Payment->amount,
            'payment_type' => 'qris',
            'currency' => 'IDR',
            'fraud_status' => 'accept',
        ], $changes);

        // Signature SHA-512 sesuai controller Midtrans.
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id']
            . $payload['status_code']
            . $payload['gross_amount']
            . self::SPRINT2_MIDTRANS_KEY
        );

        return $payload;
    }

    private function kirimWebhookSprint2(array $payload): void
    {
        $response = $this->test->postJsonPage(
            '/webhooks/midtrans/ticket',
            $payload
        );

        $this->sprint2WebhookStatus = $response->getStatusCode();
    }

    private function cekStatusWebhookSprint2(int $expected): void
    {
        if ($this->sprint2WebhookStatus !== $expected) {
            throw new \RuntimeException(
                "Webhook: expected HTTP {$expected}, actual HTTP "
                . var_export($this->sprint2WebhookStatus, true)
            );
        }
    }

    private function cekPembayaranSprint2(
        string $orderStatus,
        string $paymentStatus
    ): void {
        $this->test->assertDatabaseHasRecord('ticket_orders', [
            'id' => $this->sprint2Order->id,
            'status' => $orderStatus,
        ]);

        $this->test->assertDatabaseHasRecord('ticket_payments', [
            'id' => $this->sprint2Payment->id,
            'status' => $paymentStatus,
        ]);
    }

    #[Then('QRIS simulasi tersedia dan pembayaran masih menunggu')]
    public function qrisSimulasiTersedia(): void
    {
        if (
            !$this->sprint2Attempt->qr_image_url
            || !$this->sprint2Attempt->expires_at
        ) {
            throw new \RuntimeException(
                'QRIS atau waktu kedaluwarsa tidak tersedia.'
            );
        }

        $this->cekPembayaranSprint2('pending_payment', 'pending');
    }

    #[When('webhook Midtrans simulasi mengonfirmasi settlement')]
    public function webhookSettlementSprint2(): void
    {
        $this->kirimWebhookSprint2(
            $this->payloadWebhookSprint2('settlement')
        );
    }

    #[Then('pembayaran QRIS otomatis lunas')]
    public function qrisOtomatisLunasSprint2(): void
    {
        $this->cekStatusWebhookSprint2(200);
        $this->cekPembayaranSprint2('paid', 'paid');

        $this->test->assertDatabaseHasRecord(
            'ticket_payment_gateway_attempts',
            [
                'id' => $this->sprint2Attempt->id,
                'status' => 'paid',
            ]
        );

        if ($this->sprint2Payment->fresh()->confirmed_by !== null) {
            throw new \RuntimeException(
                'QRIS tidak boleh dikonfirmasi oleh petugas.'
            );
        }
    }

    #[When('webhook Midtrans memakai signature palsu')]
    public function webhookSignaturePalsuSprint2(): void
    {
        $payload = $this->payloadWebhookSprint2('settlement');
        $payload['signature_key'] = str_repeat('0', 128);

        $this->kirimWebhookSprint2($payload);
    }

    #[Then('webhook ditolak karena signature tidak valid')]
    public function webhookSignatureDitolakSprint2(): void
    {
        $this->cekStatusWebhookSprint2(403);
        $this->cekPembayaranSprint2('pending_payment', 'pending');
    }

    #[When('webhook Midtrans melaporkan nominal berbeda')]
    public function webhookNominalBerbedaSprint2(): void
    {
        $wrongAmount = (
            (int) $this->sprint2Payment->amount + 1000
        ) . '.00';

        $this->kirimWebhookSprint2(
            $this->payloadWebhookSprint2(
                'settlement',
                ['gross_amount' => $wrongAmount]
            )
        );
    }

    #[Then('webhook ditolak karena nominal tidak cocok')]
    public function webhookNominalDitolakSprint2(): void
    {
        $this->cekStatusWebhookSprint2(422);
        $this->cekPembayaranSprint2('pending_payment', 'pending');
    }

    #[When('webhook Midtrans melaporkan QRIS kedaluwarsa')]
    public function webhookQrisKedaluwarsaSprint2(): void
    {
        $this->kirimWebhookSprint2(
            $this->payloadWebhookSprint2('expire')
        );
    }

    #[Then('attempt QRIS kedaluwarsa dan pembayaran belum lunas')]
    public function attemptQrisExpiredSprint2(): void
    {
        $this->cekStatusWebhookSprint2(200);
        $this->cekPembayaranSprint2('pending_payment', 'pending');

        $this->test->assertDatabaseHasRecord(
            'ticket_payment_gateway_attempts',
            [
                'id' => $this->sprint2Attempt->id,
                'status' => 'expired',
            ]
        );
    }

    #[When('webhook settlement Midtrans yang sama dikirim dua kali')]
    public function webhookSettlementDuplikatSprint2(): void
    {
        $payload = $this->payloadWebhookSprint2('settlement');

        $this->kirimWebhookSprint2($payload);
        $this->cekStatusWebhookSprint2(200);

        $this->sprint2FirstPaidAt = $this->sprint2Payment
            ->fresh()
            ->getRawOriginal('paid_at');

        $this->kirimWebhookSprint2($payload);
    }

    #[Then('pembayaran tetap lunas tanpa percobaan QRIS tambahan')]
    public function qrisDuplikatAmanSprint2(): void
    {
        $this->cekStatusWebhookSprint2(200);
        $this->cekPembayaranSprint2('paid', 'paid');

        $latestPaidAt = $this->sprint2Payment
            ->fresh()
            ->getRawOriginal('paid_at');

        if ($this->sprint2FirstPaidAt !== $latestPaidAt) {
            throw new \RuntimeException(
                'Webhook duplikat mengubah waktu pelunasan.'
            );
        }

        $count = TicketPaymentGatewayAttempt::query()
            ->where('ticket_payment_id', $this->sprint2Payment->id)
            ->count();

        if ($count !== 1) {
            throw new \RuntimeException(
                "Jumlah percobaan QRIS menjadi {$count}."
            );
        }
    }

    #[When('saya membuka E-Tiket pesanan')]
    public function membukaETiketSprint2(): void
    {
        $this->test->getPage(
            '/wisatawan/pembelian-tiket/'
            . $this->sprint2Order->code
            . '/pembayaran/e-tiket'
        );
    }

    #[Then('saya diarahkan kembali ke halaman pembayaran')]
    public function eTiketBelumBisaDiaksesSprint2(): void
    {
        $this->test->assertResponseRedirectedTo(
            route('ticket-payment.show', $this->sprint2Order->code)
        );
    }

    #[Given('pembayaran Cash pesanan saya telah dikonfirmasi petugas')]
    public function cashSudahDikonfirmasiUntukETiket(): void
    {
        $this->memilihCashSprint2();
        $this->loginPetugasSprint2();
        $this->konfirmasiCashSprint2();

        // Kembali login sebagai pemilik pesanan.
        $this->test->loginAsUser($this->user);

        if ($this->sprint2Order->fresh()->status !== 'paid') {
            throw new \RuntimeException(
                'Pesanan belum berstatus lunas.'
            );
        }
    }

    /**
     * Memanggil listener secara langsung untuk menguji
     * proses penerbitan tiket dalam transaksi BDD.
     *
     * Ini bukan simulasi commit asli.
     */
    private function prosesListenerETiketSprint2(): void
    {
        $order = $this->sprint2Order->fresh();
        $payment = $this->sprint2Payment->fresh();

        if (
            $order->status !== 'paid'
            || $payment->status !== 'paid'
            || $payment->paid_at === null
        ) {
            throw new \RuntimeException(
                'Penerbitan E-Tiket membutuhkan pembayaran lunas.'
            );
        }

        $event = new TicketPaymentConfirmed(
            (string) $order->code,
            (string) $payment->code,
            (string) $payment->method,
            (string) $payment->amount,
            $payment->paid_at->toIso8601String()
        );

        app(IssueTicketsAfterPayment::class)->handle($event);
    }

    #[When('listener penerbitan memproses event pembayaran lunas')]
    public function listenerMenerbitkanETiketSprint2(): void
    {
        $this->prosesListenerETiketSprint2();
    }

    #[When('listener penerbitan memproses event pembayaran lunas dua kali')]
    public function listenerDiprosesDuaKaliSprint2(): void
    {
        $this->prosesListenerETiketSprint2();

        $this->sprint2KodeTiketSebelumPengulangan =
            IssuedTicket::query()
                ->where('ticket_order_id', $this->sprint2Order->id)
                ->orderBy('ticket_order_visitor_id')
                ->pluck('ticket_code', 'ticket_order_visitor_id')
                ->all();

        $this->prosesListenerETiketSprint2();
    }

    #[Then('satu E-Tiket aktif diterbitkan untuk setiap pengunjung')]
    public function satuETiketPerPengunjungSprint2(): void
    {
        $order = $this->sprint2Order->fresh()
            ->load('items.visitors');

        $visitorIds = [];

        foreach ($order->items as $item) {
            foreach ($item->visitors as $visitor) {
                $visitorIds[] = (int) $visitor->id;
            }
        }

        if (count($visitorIds) === 0) {
            throw new \RuntimeException(
                'Data pengunjung pesanan tidak ditemukan.'
            );
        }

        $tickets = IssuedTicket::query()
            ->where('ticket_order_id', $order->id)
            ->get();

        if ($tickets->count() !== count($visitorIds)) {
            throw new \RuntimeException(
                'Jumlah E-Tiket tidak sesuai dengan jumlah pengunjung. '
                . 'Pengunjung: ' . count($visitorIds)
                . ', E-Tiket: ' . $tickets->count()
            );
        }

        $issuedVisitorIds = [];

        foreach ($tickets as $ticket) {
            $issuedVisitorIds[] = (int) $ticket->ticket_order_visitor_id;

            if ($ticket->status !== 'active') {
                throw new \RuntimeException(
                    'E-Tiket baru seharusnya berstatus active.'
                );
            }

            if (
                !is_string($ticket->qr_token)
                || strlen($ticket->qr_token) !== 64
            ) {
                throw new \RuntimeException(
                    'Token QR E-Tiket tidak valid.'
                );
            }

            if (!\Illuminate\Support\Str::isUuid($ticket->ticket_code)) {
                throw new \RuntimeException(
                    'Kode E-Tiket bukan UUID yang valid.'
                );
            }

            if ($ticket->used_at !== null) {
                throw new \RuntimeException(
                    'E-Tiket baru belum boleh memiliki waktu penggunaan.'
                );
            }
        }

        sort($visitorIds);
        sort($issuedVisitorIds);

        if ($visitorIds !== $issuedVisitorIds) {
            throw new \RuntimeException(
                'E-Tiket tidak terhubung ke pengunjung yang tepat.'
            );
        }

        if (
            $tickets->pluck('qr_token')->unique()->count()
            !== $tickets->count()
        ) {
            throw new \RuntimeException(
                'Terdapat token QR duplikat.'
            );
        }
    }

    #[Then('kode E-Tiket tidak berubah setelah pemrosesan ulang')]
    public function kodeETiketTidakBerubahSprint2(): void
    {
        $kodeSetelahPengulangan = IssuedTicket::query()
            ->where('ticket_order_id', $this->sprint2Order->id)
            ->orderBy('ticket_order_visitor_id')
            ->pluck('ticket_code', 'ticket_order_visitor_id')
            ->all();

        if (
            $kodeSetelahPengulangan
            !== $this->sprint2KodeTiketSebelumPengulangan
        ) {
            throw new \RuntimeException(
                'Pemrosesan ulang mengubah atau menggandakan E-Tiket.'
            );
        }
    }

    #[Then('halaman E-Tiket menampilkan identitas dan kode tiket')]
    public function halamanETiketMenampilkanDataSprint2(): void
    {
        $response = $this->test->bddLastResponse();
        $html = $response->getContent();

        $ticket = IssuedTicket::query()
            ->where('ticket_order_id', $this->sprint2Order->id)
            ->with('visitor')
            ->firstOrFail();

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                'HTTP E-Tiket: ' . $response->getStatusCode()
            );
        }

        if (!str_contains($html, 'E-Tiket Saya')) {
            throw new \RuntimeException(
                'Judul E-Tiket Saya tidak ditemukan. Kemungkinan UI berubah.'
            );
        }

        if (!str_contains($html, $ticket->ticket_code)) {
            throw new \RuntimeException(
                'Kode tiket tidak ditemukan pada halaman E-Tiket.'
            );
        }

        if (!str_contains($html, $ticket->visitor->name)) {
            throw new \RuntimeException(
                'Nama pengunjung tidak ditemukan pada halaman E-Tiket.'
            );
        }
    }

    private function tiketUntukValidasiSprint2(): IssuedTicket
    {
        return IssuedTicket::query()
            ->where('ticket_order_id', $this->sprint2Order->id)
            ->firstOrFail();
    }

    #[Given('tanggal kunjungan pesanan disetel ke hari ini')]
    public function tanggalKunjunganHariIniSprint2(): void
    {
        $this->sprint2Order->update([
            'visit_date' => now('Asia/Jakarta')->toDateString(),
        ]);
    }

    #[Given('saya masuk sebagai petugas validasi tiket')]
    public function loginPetugasValidasiSprint2(): void
    {
        if ($this->sprint2Staff === null) {
            throw new \RuntimeException(
                'Petugas BDD belum dibuat.'
            );
        }

        $this->test->loginAsUser($this->sprint2Staff);
    }

    private function verifikasiTokenSprint2(string $token): void
    {
        $response = $this->test->postJsonPage(
            '/petugas/validasi-tiket/verify',
            ['qr_token' => $token]
        );

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                'Verifikasi gagal. HTTP '
                . $response->getStatusCode()
            );
        }

        $result = $response->viewData('result');

        if (
            !is_array($result)
            || !array_key_exists('valid', $result)
        ) {
            throw new \RuntimeException(
                'Hasil validasi tidak tersedia pada view.'
            );
        }

        $this->sprint2ValidationResult = $result;
    }

    private function cekHasilValidasiSprint2(
        bool $valid,
        string $message
    ): void {
        if (
            ($this->sprint2ValidationResult['valid'] ?? null) !== $valid
            || ($this->sprint2ValidationResult['message'] ?? null) !== $message
        ) {
            throw new \RuntimeException(
                'Hasil validasi tidak sesuai: '
                . json_encode($this->sprint2ValidationResult)
            );
        }
    }

    #[When('petugas memverifikasi token QR E-Tiket')]
    public function verifikasiQrAktifSprint2(): void
    {
        $this->verifikasiTokenSprint2(
            $this->tiketUntukValidasiSprint2()->qr_token
        );
    }

    #[Then('hasil verifikasi menyatakan tiket valid')]
    public function hasilQrValidSprint2(): void
    {
        $this->cekHasilValidasiSprint2(
            true,
            'E-Tiket valid dan siap untuk check-in.'
        );

        if (
            $this->tiketUntukValidasiSprint2()->status !== 'active'
        ) {
            throw new \RuntimeException(
                'Verifikasi tidak boleh mengubah status tiket.'
            );
        }
    }

    #[When('petugas memverifikasi token QR tidak dikenal')]
    public function verifikasiQrTidakDikenalSprint2(): void
    {
        $this->verifikasiTokenSprint2(str_repeat('Z', 64));
    }

    #[Then('hasil verifikasi menolak token tidak ditemukan')]
    public function hasilQrTidakDitemukanSprint2(): void
    {
        $this->cekHasilValidasiSprint2(
            false,
            'E-Tiket tidak ditemukan.'
        );
    }

    #[Given('status pembayaran tiket diubah menjadi menunggu untuk pengujian')]
    public function pembayaranBelumLunasValidasiSprint2(): void
    {
        // Ambil status terbaru dari database.
        $this->sprint2Order->refresh();
        $this->sprint2Payment->refresh();

        // Ubah status khusus untuk skenario negatif BDD.
        $this->sprint2Order->update([
            'status' => 'pending_payment',
        ]);

        $this->sprint2Payment->update([
            'status' => 'pending',
        ]);
    }

    #[Then('hasil verifikasi menolak pembayaran belum lunas')]
    public function hasilPembayaranBelumLunasSprint2(): void
    {
        $this->cekHasilValidasiSprint2(
            false,
            'Pembayaran tiket belum lunas.'
        );
    }

    #[Given('tanggal kunjungan tiket diubah menjadi hari lain')]
    public function tanggalBerbedaSprint2(): void
    {
        $this->sprint2Order->update([
            'visit_date' => now('Asia/Jakarta')
                ->addDays(2)
                ->toDateString(),
        ]);
    }

    #[Then('hasil verifikasi menolak tanggal tidak sesuai')]
    public function hasilTanggalBerbedaSprint2(): void
    {
        $this->cekHasilValidasiSprint2(
            false,
            'Tanggal kunjungan tidak sesuai dengan hari ini.'
        );
    }

    private function checkInTokenSprint2(): void
    {
        $response = $this->test->postJsonPage(
            '/petugas/validasi-tiket/check-in',
            [
                'qr_token' => $this->tiketUntukValidasiSprint2()
                    ->qr_token,
            ]
        );

        $this->sprint2CheckInHttpStatus =
            $response->getStatusCode();

        // Controller mengembalikan redirect setelah check-in.
        if ($this->sprint2CheckInHttpStatus !== 302) {
            throw new \RuntimeException(
                'Check-in seharusnya redirect HTTP 302, tetapi HTTP '
                . $this->sprint2CheckInHttpStatus
            );
        }
    }

    #[When('petugas melakukan check-in E-Tiket')]
    public function checkInPertamaSprint2(): void
    {
        $this->checkInTokenSprint2();

        $this->sprint2FirstUsedAt =
            $this->tiketUntukValidasiSprint2()
                ->getRawOriginal('used_at');
    }

    #[Then('E-Tiket berstatus used dan waktu penggunaan tercatat')]
    public function checkInBerhasilSprint2(): void
    {
        $ticket = $this->tiketUntukValidasiSprint2();

        if ($ticket->status !== 'used') {
            throw new \RuntimeException(
                'Status tiket belum berubah menjadi used.'
            );
        }

        if ($ticket->used_at === null) {
            throw new \RuntimeException(
                'Waktu penggunaan tiket belum tercatat.'
            );
        }
    }

    #[When('petugas mengulangi check-in pada E-Tiket yang sama')]
    public function checkInKeduaSprint2(): void
    {
        $this->checkInTokenSprint2();
    }

    #[Then('check-in kedua ditolak dan tiket tetap digunakan satu kali')]
    public function checkInKeduaDitolakSprint2(): void
    {
        $ticket = $this->tiketUntukValidasiSprint2();

        if ($ticket->status !== 'used') {
            throw new \RuntimeException(
                'Status tiket berubah setelah check-in kedua.'
            );
        }

        if (
            $ticket->getRawOriginal('used_at')
            !== $this->sprint2FirstUsedAt
        ) {
            throw new \RuntimeException(
                'Check-in kedua mengubah waktu penggunaan tiket.'
            );
        }

        // Ambil pesan error sesuai struktur session Behat.
        $message = data_get(
            session()->get('errors'),
            'default.messages.qr_token.0'
        );

        if ($message !== 'E-Tiket sudah digunakan atau tidak aktif.') {
            throw new \RuntimeException(
                'Pesan penolakan check-in kedua tidak sesuai: '
                . var_export($message, true)
            );
        }
    }

    #[When('saya membuka halaman riwayat pesanan tiket')]
    public function membukaRiwayatPesananSprint2(): void
    {
        $this->test->getPage(
            '/wisatawan/pembelian-tiket/pesanan'
        );

        $this->test->assertResponseOk();
    }

    private function daftarRiwayatSprint2():
        \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $this->test->checkViewIs(
            'wisatawan.ticket-purchase.orders'
        );

        $orders = $this->test
            ->bddLastResponse()
            ->viewData('orders');

        if (
            !($orders instanceof
                \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        ) {
            throw new \RuntimeException(
                'Data pagination riwayat tidak tersedia.'
            );
        }

        return $orders;
    }

    private function pesananDalamRiwayatSprint2():
        \App\Models\TicketOrder
    {
        $order = $this->daftarRiwayatSprint2()
            ->getCollection()
            ->firstWhere('code', $this->sprint2Order->code);

        if (!$order) {
            throw new \RuntimeException(
                'Pesanan tidak ditemukan dalam riwayat.'
            );
        }

        return $order;
    }

    #[Then('daftar riwayat tiket masih kosong')]
    public function riwayatTiketKosongSprint2(): void
    {
        $orders = $this->daftarRiwayatSprint2();

        if ($orders->total() !== 0) {
            throw new \RuntimeException(
                'Akun baru seharusnya belum memiliki riwayat tiket.'
            );
        }
    }

    #[Then('riwayat mencantumkan pesanan saya yang menunggu pembayaran')]
    public function riwayatPendingSprint2(): void
    {
        $order = $this->pesananDalamRiwayatSprint2();

        if (
            (int) $order->user_id !== (int) $this->user->id
            || $order->status !== 'pending_payment'
        ) {
            throw new \RuntimeException(
                'Pemilik atau status riwayat pesanan tidak sesuai.'
            );
        }
    }

    #[Then('riwayat mencantumkan pesanan saya yang sudah lunas')]
    public function riwayatLunasSprint2(): void
    {
        $order = $this->pesananDalamRiwayatSprint2();

        if (
            $order->status !== 'paid'
            || $order->payment?->status !== 'paid'
        ) {
            throw new \RuntimeException(
                'Riwayat tidak mencerminkan pembayaran Lunas.'
            );
        }
    }

    #[When('saya membuka detail pesanan tiket saya')]
    public function membukaDetailPesananSayaSprint2(): void
    {
        $this->test->getPage(
            '/wisatawan/pembelian-tiket/'
            . $this->sprint2Order->code
        );
    }

    #[Then('detail transaksi sesuai dengan pesanan saya')]
    public function detailPesananSesuaiSprint2(): void
    {
        $this->test->assertResponseOk();
        $this->test->checkViewIs(
            'wisatawan.ticket-purchase.show'
        );

        $order = $this->test
            ->bddLastResponse()
            ->viewData('order');

        if (
            !$order
            || $order->code !== $this->sprint2Order->code
            || (int) $order->user_id !== (int) $this->user->id
            || (string) $order->total
                !== (string) $this->sprint2Order->total
        ) {
            throw new \RuntimeException(
                'Detail transaksi tidak sesuai dengan pesanan.'
            );
        }
    }

    #[Given('saya beralih ke akun wisatawan lain')]
    public function beralihAkunWisatawanLainSprint2(): void
    {
        $otherUser = $this->test->makeUser([
            'name' => 'Wisatawan Lain BDD',
            'identity_number' => 'BEHATOTHER123',
            'phone' => '081234567897',
            'email' => 'wisatawan.lain.bdd@example.com',
            'password' => 'Password123!',
            'visitor_type' => 'domestik',
            'role' => 'wisatawan',
        ]);

        // Pesanan tetap milik akun pertama.
        // Hanya pengguna yang sedang login yang diganti.
        $this->test->loginAsUser($otherUser);
    }

    #[Then('pesanan wisatawan pertama tidak muncul dalam riwayat')]
    public function riwayatAkunLainTerisolasiSprint2(): void
    {
        $orders = $this->daftarRiwayatSprint2();

        $leaked = $orders->getCollection()->contains(
            fn ($order) =>
                $order->code === $this->sprint2Order->code
        );

        if ($leaked) {
            throw new \RuntimeException(
                'Riwayat membocorkan pesanan milik akun lain.'
            );
        }

        if ($orders->total() !== 0) {
            throw new \RuntimeException(
                'Akun wisatawan kedua seharusnya belum memiliki pesanan.'
            );
        }
    }

    #[When('saya membuka detail pesanan wisatawan pertama')]
    public function membukaDetailAkunLainSprint2(): void
    {
        $this->test->getPage(
            '/wisatawan/pembelian-tiket/'
            . $this->sprint2Order->code
        );
    }

    #[Then('akses detail pesanan tersebut ditolak')]
    public function detailAkunLainDitolakSprint2(): void
    {
        $this->test->assertResponseNotFound();
    }

    #[When('saya membuka halaman pembelian tiket loket')]
    public function membukaHalamanLoketSprint2(): void
    {
        $this->test->getPage('/petugas/loket');
    }

    #[Then('halaman pembelian tiket loket berhasil ditampilkan')]
    public function halamanLoketBerhasilSprint2(): void
    {
        $this->test->assertResponseOk();

        $this->test->checkViewIs('petugas.loket.index');

        $response = $this->test->bddLastResponse();

        $tickets = $response->viewData('tickets');
        $checkoutToken = $response->viewData('checkoutToken');

        if ($tickets === null) {
            throw new \RuntimeException(
                'Daftar tiket loket tidak tersedia.'
            );
        }

        if (
            !is_string($checkoutToken)
            || !\Illuminate\Support\Str::isUuid($checkoutToken)
        ) {
            throw new \RuntimeException(
                'Checkout token loket tidak valid.'
            );
        }
    }

    #[Then('akses halaman loket ditolak')]
    public function aksesLoketDitolakSprint2(): void
    {
        $this->test->assertResponseHasStatus(403);
    }

    #[Given('petugas menyiapkan data pembelian loket yang valid')]
    public function menyiapkanDataLoketSprint2(): void
    {
        // Ambil identitas wisatawan yang sudah dibuat
        // pada langkah login BDD sebelumnya.
        $wisatawan = $this->user->fresh();

        $this->sprint2LoketData = [
            'checkout_token' => (string) \Illuminate\Support\Str::uuid(),
            'visit_date' => now('Asia/Jakarta')
                ->addDay()
                ->toDateString(),
            'visitor_type' => 'domestik',
            'payment_method' => 'cash',
            'booker_is_visitor' => false,
            'booker' => [
                'name' => $wisatawan->name,
                'identity_number' => $wisatawan->identity_number,
                'phone' => $wisatawan->phone,
                'email' => $wisatawan->email,
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
        ];
    }

    #[When('petugas meminta preview harga tiket loket')]
    public function previewHargaLoketSprint2(): void
    {
        $this->sprint2LoketResponse = $this->test->postJsonPage(
            '/petugas/loket/preview',
            $this->sprint2LoketData
        );
    }

    #[Then('preview loket berhasil menampilkan rincian harga')]
    public function previewLoketBerhasilSprint2(): void
    {
        $response = $this->sprint2LoketResponse;

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                'Preview loket gagal. HTTP '
                . $response->getStatusCode()
                . ': ' . substr($response->getContent(), 0, 500)
            );
        }

        $this->test->checkViewIs('petugas.loket.index');

        $quote = $response->viewData('quote');

        if ($quote === null) {
            throw new \RuntimeException(
                'Rincian harga tidak tersedia setelah preview.'
            );
        }
    }

    #[When('petugas melakukan checkout tiket loket')]
    public function checkoutLoketSprint2(): void
    {
        $this->sprint2LoketResponse = $this->test->postJsonPage(
            '/petugas/loket/checkout',
            $this->sprint2LoketData
        );

        $status = $this->sprint2LoketResponse->getStatusCode();

        if ($status !== 302) {
            throw new \RuntimeException(
                'Checkout loket seharusnya redirect HTTP 302, tetapi HTTP '
                . $status . ': '
                . substr($this->sprint2LoketResponse->getContent(), 0, 700)
            );
        }

        // Cari transaksi menggunakan token checkout yang unik.
        $this->sprint2Order = TicketOrder::query()
            ->where('user_id', $this->user->id)
            ->where(
                'checkout_token',
                $this->sprint2LoketData['checkout_token']
            )
            ->firstOrFail();

        $this->sprint2Payment = TicketPayment::query()
            ->where('ticket_order_id', $this->sprint2Order->id)
            ->firstOrFail();
    }

    #[Then('pesanan loket tersimpan dengan pembayaran Cash pending')]
    public function checkoutCashPendingLoketSprint2(): void
    {
        $this->test->assertDatabaseHasRecord('ticket_orders', [
            'id' => $this->sprint2Order->id,
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
        ]);

        $this->test->assertDatabaseHasRecord('ticket_payments', [
            'id' => $this->sprint2Payment->id,
            'ticket_order_id' => $this->sprint2Order->id,
            'method' => 'cash',
            'status' => 'pending',
        ]);

        // Harga referensi master:5 pada fixture pembelian BDD.
        $orderTotal = (string) $this->sprint2Order->fresh()->total;
        $paymentAmount = (string) $this->sprint2Payment->fresh()->amount;

        if (
            (int) $orderTotal <= 0
            || $orderTotal !== $paymentAmount
        ) {
            throw new \RuntimeException(
                'Nominal pesanan dan pembayaran Cash tidak sesuai. '
                . 'Total: ' . $orderTotal
                . ', Pembayaran: ' . $paymentAmount
            );
        }

        $expectedUrl = route(
            'ticket-payment.staff.show',
            $this->sprint2Payment->id
        );

        $actualUrl = $this->sprint2LoketResponse
            ->headers
            ->get('Location');

        if ($actualUrl !== $expectedUrl) {
            throw new \RuntimeException(
                'Checkout tidak diarahkan ke detail pembayaran petugas.'
            );
        }
    }

    #[When('petugas mengonfirmasi pembayaran Cash loket')]
    public function konfirmasiCashLoketSprint2(): void
    {
        // Menggunakan endpoint konfirmasi Cash
        // yang sudah berhasil diuji pada modul pembayaran.
        $this->konfirmasiCashSprint2();
    }

    #[Then('transaksi loket dan pembayaran berstatus lunas')]
    public function transaksiLoketLunasSprint2(): void
    {
        $this->test->assertDatabaseHasRecord('ticket_orders', [
            'id' => $this->sprint2Order->id,
            'status' => 'paid',
        ]);

        $this->test->assertDatabaseHasRecord('ticket_payments', [
            'id' => $this->sprint2Payment->id,
            'method' => 'cash',
            'status' => 'paid',
            'confirmed_by' => $this->sprint2Staff->id,
        ]);

        if ($this->sprint2Payment->fresh()->paid_at === null) {
            throw new \RuntimeException(
                'Waktu pembayaran Cash belum tercatat.'
            );
        }
    }

    #[When('petugas checkout menggunakan identitas wisatawan tidak cocok')]
    public function checkoutIdentitasSalahLoketSprint2(): void
    {
        $data = $this->sprint2LoketData;

        // Identitas sengaja tidak cocok dengan akun terdaftar.
        $data['booker']['identity_number'] = 'BDD-IDENTITAS-SALAH';

        $this->sprint2LoketResponse = $this->test->postJsonPage(
            '/petugas/loket/checkout',
            $data
        );
    }

    #[Then('checkout loket ditolak tanpa membuat pesanan')]
    public function checkoutIdentitasDitolakLoketSprint2(): void
    {
        if ($this->sprint2LoketResponse->getStatusCode() !== 302) {
            throw new \RuntimeException(
                'Identitas tidak cocok seharusnya redirect HTTP 302.'
            );
        }

        $messages = data_get(
            session()->get('errors'),
            'default.messages',
            []
        );

        $message = $messages['booker.email'][0] ?? null;

        if (
            !is_string($message)
            || !str_contains($message, 'Akun Wisatawan tidak ditemukan')
        ) {
            throw new \RuntimeException(
                'Pesan penolakan identitas tidak sesuai: '
                . var_export($message, true)
            );
        }

        $created = TicketOrder::query()
            ->where(
                'checkout_token',
                $this->sprint2LoketData['checkout_token']
            )
            ->exists();

        if ($created) {
            throw new \RuntimeException(
                'Pesanan tetap tercipta meskipun identitas tidak cocok.'
            );
        }
    }

    #[When('petugas checkout dengan jumlah pengunjung tidak sesuai')]
    public function checkoutPengunjungSalahLoketSprint2(): void
    {
        $data = $this->sprint2LoketData;

        // Jumlah dewasa 2, tetapi daftar pengunjung hanya 1.
        $data['items'][0]['adult_quantity'] = 2;

        $this->sprint2LoketResponse = $this->test->postJsonPage(
            '/petugas/loket/checkout',
            $data
        );
    }

    #[Then('validasi checkout loket menolak jumlah pengunjung')]
    public function pengunjungDitolakLoketSprint2(): void
    {
        $response = $this->sprint2LoketResponse;

        if ($response->getStatusCode() !== 422) {
            throw new \RuntimeException(
                'Jumlah pengunjung tidak sesuai seharusnya HTTP 422, '
                . 'tetapi HTTP ' . $response->getStatusCode()
            );
        }

        $errors = $response->json('errors') ?? [];

        if (!array_key_exists('items.0.visitors', $errors)) {
            throw new \RuntimeException(
                'Validasi jumlah pengunjung tidak ditemukan: '
                . json_encode($errors)
            );
        }

        if (
            TicketOrder::query()
                ->where(
                    'checkout_token',
                    $this->sprint2LoketData['checkout_token']
                )
                ->exists()
        ) {
            throw new \RuntimeException(
                'Pesanan tidak valid tetap tersimpan.'
            );
        }
    }

}
