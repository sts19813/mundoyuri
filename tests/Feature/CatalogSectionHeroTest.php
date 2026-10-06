<?php

namespace Tests\Feature;

use App\Models\CatalogSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_admin_can_update_a_catalog_section_cover_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $section = CatalogSection::query()->where('slug', 'series-gl')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.catalog-sections.update', $section), [
                'slug' => $section->slug,
                'name' => $section->name,
                'label' => $section->label,
                'hero_eyebrow' => $section->hero_eyebrow,
                'hero_title' => 'Compartiendo el yuri con elegancia',
                'hero_description' => $section->hero_description,
                'hero_image_url' => 'https://cdn.example.com/mundo-yuri/gl-cover.jpg',
                'hero_video_url' => $section->hero_video_url,
                'hero_primary_label' => $section->hero_primary_label,
                'hero_secondary_label' => $section->hero_secondary_label,
                'sort_order' => $section->sort_order,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.catalog-sections.index'));

        $this->assertDatabaseHas('catalog_sections', [
            'id' => $section->id,
            'hero_image_url' => 'https://cdn.example.com/mundo-yuri/gl-cover.jpg',
        ]);
    }
}
