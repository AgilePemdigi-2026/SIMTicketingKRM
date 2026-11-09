<?php

namespace Tests\Feature\Admin;

use App\Models\DaftarTiket;
use App\Models\User;
use App\Models\Wahana;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TiketCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->role = 'admin';
        $user->save();

        return $user;
    }

    private function dataTiket(array $overrides = []): array
    {
        return array_merge([
            'nama_tiket' => 'Tiket Test',
            'kategori_tiket' => 'Tiket Masuk',
            'kuota' => 100,
            'kategori_wisatawan' => 'Domestik (WNI)',
            'harga_dewasa' => 50000,
            'harga_anak' => 25000,
            'status_aktif' => 1,
            'ketentuan' => 'Tiket untuk pengujian.',
        ], $overrides);
    }

    private function wahana(): Wahana
    {
        return Wahana::create([
            'nama_wahana' => 'Wahana Test',
            'durasi' => 5,
            'jam_buka' => '09:00',
            'jam_tutup' => '17:00',
            'kapasitas' => 20,
            'status_aktif' => 1,
        ]);
    }

    public function test_guest_cannot_access_ticket_pages(): void
    {
        $this->get(route('admin.daftar-tiket.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_ticket_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.daftar-tiket.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_ticket_index_and_create_page(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.daftar-tiket.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.daftar-tiket.create'))
            ->assertOk();
    }

    public function test_admin_can_create_ticket_without_wahana(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)
            ->post(route('admin.daftar-tiket.store'), $this->dataTiket());

        $response->assertRedirect(route('admin.daftar-tiket.index'));

        $this->assertDatabaseHas('daftar_tiket', [
            'nama_tiket' => 'Tiket Test',
            'kategori_tiket' => 'Tiket Masuk',
            'id_wahana' => null,
        ]);
    }

    public function test_admin_can_create_ticket_with_wahana_relation(): void
    {
        $admin = $this->admin();
        $wahana = $this->wahana();

        $response = $this->actingAs($admin)
            ->post(route('admin.daftar-tiket.store'), $this->dataTiket([
                'kategori_tiket' => 'Tiket Wahana',
                'wahana_ids' => [$wahana->id_wahana],
            ]));

        $response->assertRedirect(route('admin.daftar-tiket.index'));

        $tiket = DaftarTiket::where('nama_tiket', 'Tiket Test')->firstOrFail();

        $this->assertDatabaseHas('daftar_tiket', [
            'id_tiket' => $tiket->id_tiket,
            'id_wahana' => $wahana->id_wahana,
        ]);

        $this->assertDatabaseHas('tiket_wahana', [
            'id_tiket' => $tiket->id_tiket,
            'id_wahana' => $wahana->id_wahana,
        ]);
    }

    public function test_ticket_creation_rejects_invalid_data(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.daftar-tiket.store'), [
                'nama_tiket' => '',
                'kategori_tiket' => 'Kategori Salah',
                'kuota' => -1,
                'kategori_wisatawan' => 'Jenis Salah',
                'harga_dewasa' => -100,
                'harga_anak' => -50,
                'status_aktif' => 3,
            ])
            ->assertSessionHasErrors([
                'nama_tiket',
                'kategori_tiket',
                'kuota',
                'kategori_wisatawan',
                'harga_dewasa',
                'harga_anak',
                'status_aktif',
            ]);
    }

    public function test_ticket_rejects_nonexistent_wahana_id(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.daftar-tiket.store'), $this->dataTiket([
                'wahana_ids' => [999999],
            ]))
            ->assertSessionHasErrors(['wahana_ids.0']);
    }

    public function test_admin_can_update_ticket_and_wahana_relation(): void
    {
        $admin = $this->admin();
        $wahana = $this->wahana();

        $tiket = DaftarTiket::create($this->dataTiket());

        $response = $this->actingAs($admin)
            ->put(route('admin.daftar-tiket.update', $tiket->id_tiket), $this->dataTiket([
                'nama_tiket' => 'Tiket Updated',
                'kategori_tiket' => 'Tiket Wahana',
                'harga_dewasa' => 75000,
                'wahana_ids' => [$wahana->id_wahana],
            ]));

        $response->assertRedirect(route('admin.daftar-tiket.index'));

        $this->assertDatabaseHas('daftar_tiket', [
            'id_tiket' => $tiket->id_tiket,
            'nama_tiket' => 'Tiket Updated',
            'harga_dewasa' => 75000,
            'id_wahana' => $wahana->id_wahana,
        ]);

        $this->assertDatabaseHas('tiket_wahana', [
            'id_tiket' => $tiket->id_tiket,
            'id_wahana' => $wahana->id_wahana,
        ]);
    }

    public function test_admin_can_delete_ticket(): void
    {
        $admin = $this->admin();
        $tiket = DaftarTiket::create($this->dataTiket());

        $response = $this->actingAs($admin)
            ->delete(route('admin.daftar-tiket.destroy', $tiket->id_tiket));

        $response->assertRedirect(route('admin.daftar-tiket.index'));

        $this->assertDatabaseMissing('daftar_tiket', [
            'id_tiket' => $tiket->id_tiket,
        ]);
    }
}