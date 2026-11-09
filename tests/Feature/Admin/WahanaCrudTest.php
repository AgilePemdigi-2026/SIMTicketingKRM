<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Wahana;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WahanaCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->role = 'admin';
        $user->save();

        return $user;
    }

    public function test_guest_cannot_access_wahana_pages(): void
    {
        $response = $this->get(route('admin.wahana.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_wahana_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.wahana.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_wahana_index_and_create_page(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.wahana.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.wahana.create'))
            ->assertOk();
    }

    public function test_admin_can_create_wahana(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.wahana.store'), [
            'nama_wahana' => 'Roller Coaster Test',
            'durasi' => 5,
            'jam_buka' => '09:00',
            'jam_tutup' => '17:00',
            'kapasitas' => 20,
            'status_aktif' => 1,
            'deskripsi' => 'Wahana untuk pengujian.',
        ]);

        $response->assertRedirect(route('admin.wahana.index'));

        $this->assertDatabaseHas('wahana', [
            'nama_wahana' => 'Roller Coaster Test',
            'durasi' => 5,
            'kapasitas' => 20,
            'status_aktif' => 1,
        ]);
    }

    public function test_wahana_creation_requires_valid_data(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.wahana.store'), [
                'nama_wahana' => '',
                'durasi' => 0,
                'jam_buka' => 'invalid',
                'jam_tutup' => 'invalid',
                'kapasitas' => 0,
                'status_aktif' => 3,
            ])
            ->assertSessionHasErrors([
                'nama_wahana',
                'durasi',
                'jam_buka',
                'jam_tutup',
                'kapasitas',
                'status_aktif',
            ]);
    }

    public function test_admin_can_update_wahana(): void
    {
        $admin = $this->admin();

        $wahana = Wahana::create([
            'nama_wahana' => 'Wahana Lama',
            'durasi' => 5,
            'jam_buka' => '09:00',
            'jam_tutup' => '17:00',
            'kapasitas' => 20,
            'status_aktif' => 1,
            'deskripsi' => 'Deskripsi lama',
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.wahana.update', $wahana->id_wahana),
            [
                'nama_wahana' => 'Wahana Baru',
                'durasi' => 10,
                'jam_buka' => '10:00',
                'jam_tutup' => '18:00',
                'kapasitas' => 30,
                'status_aktif' => 0,
                'deskripsi' => 'Deskripsi baru',
            ]
        );

        $response->assertRedirect(route('admin.wahana.index'));

        $this->assertDatabaseHas('wahana', [
            'id_wahana' => $wahana->id_wahana,
            'nama_wahana' => 'Wahana Baru',
            'durasi' => 10,
            'kapasitas' => 30,
            'status_aktif' => 0,
        ]);
    }

    public function test_admin_can_delete_wahana(): void
    {
        $admin = $this->admin();

        $wahana = Wahana::create([
            'nama_wahana' => 'Wahana Hapus',
            'durasi' => 5,
            'jam_buka' => '09:00',
            'jam_tutup' => '17:00',
            'kapasitas' => 20,
            'status_aktif' => 1,
        ]);

        $response = $this->actingAs($admin)->delete(
            route('admin.wahana.destroy', $wahana->id_wahana)
        );

        $response->assertRedirect(route('admin.wahana.index'));

        $this->assertDatabaseMissing('wahana', [
            'id_wahana' => $wahana->id_wahana,
        ]);
    }
}