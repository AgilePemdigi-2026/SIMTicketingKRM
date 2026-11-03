<?php

namespace Tests\Feature\Admin;

use App\Models\Fasilitas;
use App\Models\SesiFasilitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FasilitasCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->role = 'admin';
        $user->save();

        return $user;
    }

    private function dataFasilitas(array $overrides = []): array
    {
        return array_merge([
            'nama_fasilitas' => 'Fasilitas Test',
            'kapasitas_fasilitas' => 10,
            'status_fasilitas' => 1,
            'ketentuan_fasilitas' => 'Ketentuan test',
            'deskripsi_fasilitas' => 'Deskripsi test',
            'sesi' => [
                [
                    'nama_sesi' => 'Sesi Pagi',
                    'jam_mulai' => '09:00',
                    'jam_selesai' => '10:00',
                    'durasi_menit' => 60,
                    'harga_sesi' => 25000,
                ],
            ],
        ], $overrides);
    }

    private function createFasilitas(array $attributes = []): Fasilitas
    {
        return Fasilitas::create(array_merge([
            'nama_fasilitas' => 'Fasilitas Test',
            'kapasitas_fasilitas' => 10,
            'status_fasilitas' => 1,
            'ketentuan_fasilitas' => 'Ketentuan test',
            'deskripsi_fasilitas' => 'Deskripsi test',
        ], $attributes));
    }

    private function createSesi(Fasilitas $fasilitas, array $attributes = []): SesiFasilitas
    {
        return $fasilitas->sesiFasilitas()->create(array_merge([
            'nama_sesi' => 'Sesi Pagi',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:00',
            'durasi_menit' => 60,
            'harga_sesi' => 25000,
            'status_aktif' => 1,
        ], $attributes));
    }

    public function test_guest_cannot_access_fasilitas_pages(): void
    {
        $this->get(route('admin.fasilitas.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_fasilitas_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.fasilitas.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_fasilitas_index_and_create_page(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.fasilitas.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.fasilitas.create'))
            ->assertOk();
    }

    public function test_admin_can_create_fasilitas_with_session(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)
            ->post(route('admin.fasilitas.store'), $this->dataFasilitas());

        $response->assertRedirect(route('admin.fasilitas.index'));

        $fasilitas = Fasilitas::where('nama_fasilitas', 'Fasilitas Test')
            ->firstOrFail();

        $this->assertDatabaseHas('fasilitas', [
            'id_fasilitas' => $fasilitas->id_fasilitas,
            'nama_fasilitas' => 'Fasilitas Test',
            'kapasitas_fasilitas' => 10,
        ]);

        $this->assertDatabaseHas('sesi_fasilitas', [
            'id_fasilitas' => $fasilitas->id_fasilitas,
            'nama_sesi' => 'Sesi Pagi',
            'durasi_menit' => 60,
            'status_aktif' => 1,
        ]);
    }

    public function test_fasilitas_creation_requires_valid_data_and_session(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.fasilitas.store'), [
                'nama_fasilitas' => '',
                'kapasitas_fasilitas' => 0,
                'status_fasilitas' => 3,
                'sesi' => [],
            ])
            ->assertSessionHasErrors([
                'nama_fasilitas',
                'kapasitas_fasilitas',
                'status_fasilitas',
                'sesi',
            ]);
    }

    public function test_session_duration_must_match_time_difference(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.fasilitas.store'), $this->dataFasilitas([
                'sesi' => [
                    [
                        'nama_sesi' => 'Sesi Tidak Valid',
                        'jam_mulai' => '09:00',
                        'jam_selesai' => '10:00',
                        'durasi_menit' => 30,
                        'harga_sesi' => 25000,
                    ],
                ],
            ]))
            ->assertSessionHasErrors(['sesi.0.durasi_menit']);
    }

    public function test_session_end_time_must_be_after_start_time(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.fasilitas.store'), $this->dataFasilitas([
                'sesi' => [
                    [
                        'nama_sesi' => 'Sesi Tidak Valid',
                        'jam_mulai' => '10:00',
                        'jam_selesai' => '09:00',
                        'durasi_menit' => 60,
                        'harga_sesi' => 25000,
                    ],
                ],
            ]))
            ->assertSessionHasErrors(['sesi.0.jam_selesai']);
    }

    public function test_admin_can_update_fasilitas_and_its_sessions(): void
    {
        $admin = $this->admin();
        $fasilitas = $this->createFasilitas();
        $sesi = $this->createSesi($fasilitas);

        $response = $this->actingAs($admin)
            ->put(
                route('admin.fasilitas.update', $fasilitas->id_fasilitas),
                $this->dataFasilitas([
                    'nama_fasilitas' => 'Fasilitas Updated',
                    'kapasitas_fasilitas' => 20,
                    'sesi' => [
                        [
                            'id_sesi' => $sesi->id_sesi,
                            'nama_sesi' => 'Sesi Updated',
                            'jam_mulai' => '10:00',
                            'jam_selesai' => '11:30',
                            'durasi_menit' => 90,
                            'harga_sesi' => 30000,
                        ],
                        [
                            'nama_sesi' => 'Sesi Baru',
                            'jam_mulai' => '13:00',
                            'jam_selesai' => '14:00',
                            'durasi_menit' => 60,
                            'harga_sesi' => 35000,
                        ],
                    ],
                ])
            );

        $response->assertRedirect(route('admin.fasilitas.index'));

        $this->assertDatabaseHas('fasilitas', [
            'id_fasilitas' => $fasilitas->id_fasilitas,
            'nama_fasilitas' => 'Fasilitas Updated',
            'kapasitas_fasilitas' => 20,
        ]);

        $this->assertDatabaseHas('sesi_fasilitas', [
            'id_sesi' => $sesi->id_sesi,
            'nama_sesi' => 'Sesi Updated',
            'durasi_menit' => 90,
        ]);

        $this->assertDatabaseHas('sesi_fasilitas', [
            'id_fasilitas' => $fasilitas->id_fasilitas,
            'nama_sesi' => 'Sesi Baru',
        ]);
    }

    public function test_admin_can_deactivate_fasilitas(): void
    {
        $admin = $this->admin();
        $fasilitas = $this->createFasilitas();

        $response = $this->actingAs($admin)
            ->patch(route('admin.fasilitas.toggle-status', $fasilitas->id_fasilitas));

        $response->assertRedirect(route('admin.fasilitas.index'));

        $this->assertDatabaseHas('fasilitas', [
            'id_fasilitas' => $fasilitas->id_fasilitas,
            'status_fasilitas' => 0,
        ]);
    }

    public function test_fasilitas_cannot_be_activated_without_active_session(): void
    {
        $admin = $this->admin();
        $fasilitas = $this->createFasilitas([
            'status_fasilitas' => 0,
        ]);

        $this->createSesi($fasilitas, [
            'status_aktif' => 0,
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.fasilitas.toggle-status', $fasilitas->id_fasilitas));

        $response->assertRedirect(route('admin.fasilitas.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('fasilitas', [
            'id_fasilitas' => $fasilitas->id_fasilitas,
            'status_fasilitas' => 0,
        ]);
    }

    public function test_admin_can_delete_fasilitas_and_its_sessions(): void
    {
        $admin = $this->admin();
        $fasilitas = $this->createFasilitas();
        $sesi = $this->createSesi($fasilitas);

        $response = $this->actingAs($admin)
            ->delete(route('admin.fasilitas.destroy', $fasilitas->id_fasilitas));

        $response->assertRedirect(route('admin.fasilitas.index'));

        $this->assertDatabaseMissing('fasilitas', [
            'id_fasilitas' => $fasilitas->id_fasilitas,
        ]);

        $this->assertDatabaseMissing('sesi_fasilitas', [
            'id_sesi' => $sesi->id_sesi,
        ]);
    }
}