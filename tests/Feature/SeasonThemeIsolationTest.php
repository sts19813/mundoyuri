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
            $response = $this->get($url)->assertOk()
                ->assertDontSee('halloween-', false)
                ->assertDontSee('season-themes.css', false)
                ->assertDontSee('season-palette.css', false)
                ->assertDontSee('data-season-palette', false)
                ->assertDontSee('assets/img/seasonal/', false)
                ->assertDontSee('data-season-theme', false)
                ->assertSee('assets/img/logos/mundo-yuri-logo.svg', false);

            foreach (array_keys(SeasonThemeSetting::THEMES) as $theme) {
                $response->assertDontSee('assets/img/logos/mundo-yuri-'.$theme.'.svg', false);
            }
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
            ->assertSee('data-season-palette="halloween"', false)
            ->assertSeeInOrder(['assets/css/style.css', 'assets/css/halloween-theme.css', 'assets/css/season-palette.css'], false)
            ->assertSee('halloween-theme.js', false)
            ->assertSee('halloween-hero-mobile.webp', false)
            ->assertSee('assets/img/logos/mundo-yuri-halloween.svg', false)
            ->assertDontSee('<iframe id="heroYoutubeVideo"', false);

        $this->actingAs($admin)->withSession(['_token' => 'season-theme-test'])->put(route('admin.settings.season-theme.update'), [
            '_token' => 'season-theme-test', 'enabled' => '0', 'theme' => 'halloween',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->get('/')->assertOk()
            ->assertDontSee('halloween-', false)
            ->assertDontSee('season-palette.css', false)
            ->assertDontSee('data-season-palette', false)
            ->assertSee('wallpaper-login.webp', false)
            ->assertSee('assets/img/logos/mundo-yuri-logo.svg', false)
            ->assertDontSee('assets/img/logos/mundo-yuri-halloween.svg', false)
            ->assertSee('data-desktop-src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false);

        $this->actingAs($admin)->put(route('admin.settings.season-theme.update'), [
            '_token' => 'season-theme-test', 'enabled' => '1', 'theme' => 'halloween',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->get('/')->assertOk()
            ->assertSee('halloween-theme.css', false)
            ->assertSee('halloween-theme.js', false)
            ->assertSee('assets/img/logos/mundo-yuri-halloween.svg', false)
            ->assertSee('halloween-hero-desktop.webp', false);
    }

    public function test_other_themes_do_not_load_halloween_scripts_or_assets(): void
    {
        foreach (['christmas', 'spring', 'summer', 'autumn'] as $theme) {
            SeasonThemeSetting::query()->updateOrCreate(['id' => 1], ['enabled' => true, 'theme' => $theme]);

            $response = $this->get('/')->assertOk()
                ->assertSee('season-themes.css', false)
                ->assertSee('season-palette.css', false)
                ->assertSee('data-season-palette="'.$theme.'"', false)
                ->assertSee('data-season-theme="'.$theme.'"', false)
                ->assertDontSee('halloween-', false)
                ->assertDontSee('assets/img/seasonal/', false)
                ->assertSee('assets/img/logos/mundo-yuri-'.$theme.'.svg', false);

            $this->assertSame(2, substr_count($response->getContent(), 'assets/img/logos/mundo-yuri-'.$theme.'.svg'));
            foreach (array_diff(array_keys(SeasonThemeSetting::THEMES), [$theme]) as $otherTheme) {
                $response->assertDontSee('assets/img/logos/mundo-yuri-'.$otherTheme.'.svg', false);
            }
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

    public function test_seasonal_logos_preserve_the_original_vector_wordmark(): void
    {
        $original = new \DOMDocument();
        $this->assertTrue($original->load(public_path('assets/img/logos/mundo-yuri-logo.svg')));
        $originalPaths = (new \DOMXPath($original))->query('//*[local-name()="path"][@transform]');
        $signature = fn ($paths) => collect(iterator_to_array($paths))->map(
            fn ($path) => [$path->getAttribute('d'), $path->getAttribute('transform')]
        )->all();
        $expected = $signature($originalPaths);
        $this->assertCount(9, $expected);
        $colors = [
            'halloween' => '#F0B368', 'christmas' => '#A8CFB2', 'spring' => '#EBA9D1',
            'summer' => '#F5C56B', 'autumn' => '#DCA16C',
        ];

        foreach (array_keys(SeasonThemeSetting::THEMES) as $theme) {
            $logo = new \DOMDocument();
            $this->assertTrue($logo->load(public_path('assets/img/logos/mundo-yuri-'.$theme.'.svg')));
            $xpath = new \DOMXPath($logo);
            $group = $xpath->query('//*[@id="'.$theme.'-wordmark"]')->item(0);
            $this->assertInstanceOf(\DOMElement::class, $group);
            $this->assertSame($colors[$theme], $group->getAttribute('fill'));
            $this->assertSame($expected, $signature($xpath->query('./*[local-name()="path"]', $group)));
            $this->assertSame(0, $xpath->query('.//*[local-name()="text" or local-name()="image"]', $group)->length);
            $this->assertSame(0, $xpath->query('//*[local-name()="script"]')->length);
            $this->assertStringContainsString('prefers-reduced-motion: reduce', $logo->saveXML());
        }
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
