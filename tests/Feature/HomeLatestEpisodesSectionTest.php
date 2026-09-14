<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeLatestEpisodesSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_latest_episodes_are_twelve_most_recent_across_anime_and_gl(): void
    {
        $genre = Genre::query()->create([
            'name' => 'Romance',
            'slug' => 'romance',
            'is_active' => true,
        ]);

        foreach (range(1, 13) as $number) {
            $seriesTitle = sprintf('Anime %02d', $number);
            $series = $this->createSeries($genre->id, $seriesTitle, "anime-{$number}", 'anime');

            Episode::query()->create([
                'series_id' => $series->id,
                'title' => "Episodio anime {$number}",
                'slug' => "episodio-anime-{$number}",
                'season_number' => 1,
                'episode_number' => $number,
                'moderation_status' => 'approved',
                'published_at' => now()->subMinutes($number),
            ]);
        }

        $oldGlSeries = $this->createSeries($genre->id, 'Serie GL antigua', 'serie-gl-antigua', 'series-gl');
        Episode::query()->create([
            'series_id' => $oldGlSeries->id,
            'title' => 'Episodio GL antiguo',
            'slug' => 'episodio-gl-antiguo',
            'season_number' => 1,
            'episode_number' => 1,
            'moderation_status' => 'approved',
            'published_at' => now()->subDay(),
        ]);

        $html = $this->get(route('home'))->assertOk()->getContent();
        $episodesSection = $this->extractLatestEpisodesSection($html);

        $this->assertSame(12, substr_count($episodesSection, 'class="episode-card"'));
        $this->assertStringContainsString('Anime 01', $episodesSection);
        $this->assertStringContainsString('Anime 12', $episodesSection);
        $this->assertStringNotContainsString('Anime 13', $episodesSection);
        $this->assertStringNotContainsString('Serie GL antigua', $episodesSection);
        $this->assertLessThan(
            strpos($episodesSection, 'Anime 12'),
            strpos($episodesSection, 'Anime 01')
        );
    }

    private function createSeries(int $genreId, string $title, string $slug, string $catalogSection): Series
    {
        return Series::query()->create([
            'genre_id' => $genreId,
            'title' => $title,
            'slug' => $slug,
            'content_type' => 'series',
            'catalog_section' => $catalogSection,
            'status' => 'ongoing',
            'description' => "Descripción de {$title}.",
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
    }

    private function extractLatestEpisodesSection(string $html): string
    {
        $start = strpos($html, '<h2 class="section-title">Últimos episodios</h2>');
        $this->assertNotFalse($start);

        $end = strpos($html, '<h2 class="section-title">Destacados</h2>', $start);
        $this->assertNotFalse($end);

        return substr($html, $start, $end - $start);
    }
}
