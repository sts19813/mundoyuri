<?php

namespace Tests\Feature;

use App\Models\CatalogSection;
use App\Models\SeasonThemeSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeasonThemeIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['env'] = 'local';
        // Exercise the public cache without leaving test data in the local file cache.
        config(['cache.stores.file' => ['driver' => 'array']]);
    }

    public function test_disabled_theme_sends_no_seasonal_resources_on_public_pages(): void
    {
        SeasonThemeSetting::query()->create(['id' => 1, 'enabled' => false, 'theme' => 'halloween']);

        foreach (['/', '/series', '/comunidad'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('halloween-', false)
                ->assertDontSee('season-themes.css', false)
                ->assertDontSee('assets/img/seasonal/', false)
                ->assertDontSee('data-season-theme', false);
        }
    }

    public function test_admin_toggle_invalidates_cache_and_restores_the_existing_hero(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        CatalogSection::query()->where('slug', 'series-gl')->firstOrFail()->update([
            'hero_desktop_image' => '/assets/img/wallpaper-login.webp',
            'hero_mobile_image' => '/assets/img/wallpaper-login.webp',
            'hero_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'hero_video_on_mobile' => false,
        ]);
        SeasonThemeSetting::query()->create(['id' => 1, 'enabled' => true, 'theme' => 'halloween']);

        $this->get('/')->assertOk()
            ->assertSee('halloween-theme.css', false)
            ->assertSee('halloween-theme.js', false)
            ->assertSee('halloween-hero-mobile.webp', false)
            ->assertDontSee('<iframe id="heroYoutubeVideo"', false);

        $this->actingAs($admin)->withSession(['_token' => 'season-theme-test'])->put(route('admin.settings.season-theme.update'), [
            '_token' => 'season-theme-test', 'enabled' => '0', 'theme' => 'halloween',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->get('/')->assertOk()
            ->assertDontSee('halloween-', false)
            ->assertSee('wallpaper-login.webp', false)
            ->assertSee('data-desktop-src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false);

        $this->actingAs($admin)->put(route('admin.settings.season-theme.update'), [
            '_token' => 'season-theme-test', 'enabled' => '1', 'theme' => 'halloween',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->get('/')->assertOk()
            ->assertSee('halloween-theme.css', false)
            ->assertSee('halloween-theme.js', false)
            ->assertSee('halloween-hero-desktop.webp', false);
    }

    public function test_other_themes_do_not_load_halloween_scripts_or_assets(): void
    {
        foreach (['christmas', 'spring', 'summer', 'autumn'] as $theme) {
            SeasonThemeSetting::query()->updateOrCreate(['id' => 1], ['enabled' => true, 'theme' => $theme]);

            $this->get('/')->assertOk()
                ->assertSee('season-themes.css', false)
                ->assertSee('data-season-theme="'.$theme.'"', false)
                ->assertDontSee('halloween-', false)
                ->assertDontSee('assets/img/seasonal/', false);
        }
    }

    public function test_public_settings_are_read_from_cache_after_the_first_lookup(): void
    {
        SeasonThemeSetting::query()->create(['id' => 1, 'enabled' => false, 'theme' => 'halloween']);
        $this->assertNull(SeasonThemeSetting::activeTheme());

        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->assertNull(SeasonThemeSetting::activeTheme());
        $this->assertSame([], DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_production_loads_the_enabled_seasonal_theme(): void
    {
        SeasonThemeSetting::query()->create(['id' => 1, 'enabled' => true, 'theme' => 'halloween']);
        $this->app['env'] = 'production';

        $this->assertSame('halloween', SeasonThemeSetting::activeTheme());

        $this->get('/')->assertOk()
            ->assertSee('halloween-theme.css', false)
            ->assertSee('halloween-theme.js', false)
            ->assertSee('data-season-theme="halloween"', false);
    }
}
