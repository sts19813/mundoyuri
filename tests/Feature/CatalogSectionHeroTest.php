<?php

namespace Tests\Feature;

use App\Models\CatalogSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogSectionHeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_gl_home_uses_the_configured_cover_image_and_message(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Compartiendo el yuri con elegancia')
            ->assertSee('class="hero-cover-image"', false)
            ->assertSee('src="/assets/img/wallpaper-login.jpg"', false);
    }

    public function test_legacy_section_urls_use_the_same_portal_home_configuration(): void
    {
        $this->get(route('catalog.sections.show', 'anime'))
            ->assertOk()
            ->assertSee('Compartiendo el yuri con elegancia')
            ->assertSee('class="hero-cover-media"', false);
    }

    public function test_admin_can_upload_a_catalog_section_cover_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $section = CatalogSection::query()->where('slug', 'series-gl')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.catalog-sections.update', $section), [
                'hero_title' => 'Compartiendo el yuri con elegancia',
                'hero_desktop_image' => UploadedFile::fake()->image('portada-desktop.jpg', 1600, 900),
                'hero_mobile_image' => UploadedFile::fake()->image('portada-movil.jpg', 720, 960),
                'hero_primary_enabled' => 1,
                'hero_secondary_enabled' => 1,
            ])
            ->assertRedirect(route('admin.catalog-sections.edit', $section));

        $section->refresh();

        Storage::disk('public')->assertExists($section->hero_desktop_image);
        Storage::disk('public')->assertExists($section->hero_mobile_image);
    }
}
