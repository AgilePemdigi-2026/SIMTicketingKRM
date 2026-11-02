<?php

namespace Tests\Feature;

use App\Models\Konten;
use App\Support\FigmaDemoContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\PostgresTicketTestCase;

class FigmaContentTest extends PostgresTicketTestCase
{
    public function test_local_empty_sections_have_gallery_and_one_labelled_demo_article_without_database_writes(): void
    {
        $user = $this->account();
        $this->actingAs($user);
        $this->app->instance('env', 'local');
        config(['figma_demo.enabled' => true]);
        $this->get('/beranda')->assertOk()
            ->assertViewHas('galeri', fn ($images) => count($images) === 8 && $images[0] === 'images/figma/jembatan.jpg' && $images[1] === 'images/figma/lanskap.jpg')
            ->assertViewHas('berita', fn ($articles) => count($articles) === 1)
            ->assertSee('Demo Proyek')->assertSee('images/figma/jembatan.jpg');
        $this->get('/destinasi')->assertOk()->assertViewHas('galeri', fn ($images) => count($images) === 8);
        $this->get('/berita')->assertOk()->assertViewHas('berita', fn ($articles) => $articles->total() === 1 && $articles->count() === 1);
        $this->get('/berita?page=2')->assertOk()->assertViewHas('berita', fn ($articles) => $articles->count() === 0);
        $this->get('/berita?kategori=event')->assertOk()->assertDontSee(FigmaDemoContent::article()['judul']);
        $this->get('/berita?q=tidak-ada-hasil')->assertOk()->assertDontSee(FigmaDemoContent::article()['judul']);
        $this->get('/berita/'.FigmaDemoContent::ARTICLE_SLUG)->assertOk()->assertSee('bukan publikasi resmi pengelola')->assertSee('images/figma/lanskap.jpg');
        $this->assertDatabaseCount('konten', 0);
        $this->assertDatabaseCount('info_destinasi', 0);
    }

    public function test_published_admin_content_and_existing_images_take_priority_over_demo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('gallery/existing.png', 'fixture');
        Storage::disk('public')->put('wahana/existing.png', 'fixture');
        Storage::disk('public')->put('facilities/existing.png', 'fixture');
        DB::table('wahana')->where('id_wahana', 2)->update(['foto' => 'wahana/existing.png']);
        DB::table('fasilitas')->where('id_fasilitas', 1)->update(['foto_fasilitas' => 'facilities/existing.png']);
        $user = $this->account();
        foreach (['galeri', 'berita'] as $kind) {
            Konten::create(['id_pengguna' => $user->id, 'jenis_konten' => $kind, 'judul' => 'Konten Admin '.$kind,
                'slug' => $kind === 'berita' ? 'konten-admin' : null, 'kategori' => 'Info', 'isi' => 'Konten asli',
                'gambar' => 'gallery/existing.png', 'status_publikasi' => 'published', 'waktu_publikasi' => now()->subMinute()]);
        }
        $this->actingAs($user);
        $this->app->instance('env', 'local');
        config(['figma_demo.enabled' => true]);
        $this->get('/beranda')->assertOk()->assertSee('storage/gallery/existing.png')
            ->assertSee('storage/wahana/existing.png')->assertSee('storage/facilities/existing.png')
            ->assertViewHas('galeri', fn ($images) => count($images) === 1)
            ->assertDontSee(FigmaDemoContent::article()['judul']);
        $this->get('/berita')->assertOk()->assertSee('Konten Admin berita')->assertDontSee('Demo Proyek');
        $this->get('/berita?q=tidak-ada-hasil')->assertOk()->assertDontSee(FigmaDemoContent::article()['judul']);
        $this->get('/berita/'.FigmaDemoContent::ARTICLE_SLUG)->assertNotFound();
        $this->get('/wahana/2')->assertOk()->assertSee('storage/wahana/existing.png');
        $this->assertSame('wahana/existing.png', DB::table('wahana')->where('id_wahana', 2)->value('foto'));
    }

    public function test_demo_is_absent_in_production_and_when_disabled_locally(): void
    {
        $this->actingAs($this->account());
        config(['figma_demo.enabled' => true]);
        $this->app->instance('env', 'production');
        $this->get('/beranda')->assertOk()->assertViewHas('galeri', fn ($images) => $images === [])
            ->assertDontSee(FigmaDemoContent::article()['judul']);
        $this->get('/berita/'.FigmaDemoContent::ARTICLE_SLUG)->assertNotFound();
        $this->app->instance('env', 'local');
        config(['figma_demo.enabled' => false]);
        $this->get('/berita/'.FigmaDemoContent::ARTICLE_SLUG)->assertNotFound();
        $this->get('/destinasi')->assertViewHas('galeri', fn ($images) => $images === []);
    }

    public function test_missing_uploaded_image_uses_illustration_without_overwriting_master_reference(): void
    {
        Storage::fake('public');
        $this->actingAs($this->account());
        $this->get('/beranda')->assertOk()->assertSee('images/figma/jembatan.jpg');
        $this->get('/wahana/2')->assertOk()->assertSee('images/wahana/boardwalk-mangrove.jpg');
        $this->assertSame('wahana/YU7JmnHoMb9csXJ6fNPILSIkgwGHiYqWtVqbV25h.png', DB::table('wahana')->where('id_wahana', 2)->value('foto'));
    }
}
