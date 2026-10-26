<?php

namespace Database\Seeders;

use App\Models\Episode;
use App\Models\EpisodeSource;
use App\Models\Genre;
use App\Models\Series;
use App\Models\Tag;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpsertRevolutionaryGirlUtenaSeeder extends Seeder
{
    private const SERIES_SLUG = 'revolutionary-girl-utena';

    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = User::query()->where('role', 'admin')->orderBy('id')->firstOrFail();
            $romance = Genre::query()->where('slug', 'romance')->firstOrFail();

            $series = Series::query()->updateOrCreate(
                ['slug' => self::SERIES_SLUG],
                [
                    'genre_id' => $romance->id,
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'title' => 'Revolutionary Girl Utena',
                    'alternate_titles' => [
                        'Shoujo Kakumei Utena',
                        '少女革命ウテナ',
                        'La chica revolucionaria Utena',
                        'Utena, la chica revolucionaria',
                    ],
                    'content_type' => 'series',
                    'catalog_section' => 'anime',
                    'status' => 'completed',
                    'description' => 'Tras perder a sus padres, Utena Tenjou conoce a un príncipe que la inspira a convertirse en uno. En la Academia Ohtori, su deseo de proteger a los demás la lleva a los duelos del Consejo Estudiantil y hasta Anthy Himemiya, la misteriosa Novia de la Rosa. Entre símbolos, secretos y revoluciones, Utena cuestiona el destino que la escuela ha trazado para ellas.',
                    'seo_title' => 'Revolutionary Girl Utena: anime yuri en español latino',
                    'seo_description' => 'Mira Revolutionary Girl Utena, anime yuri clásico en español latino. Utena protege a Anthy entre los duelos de la Academia Ohtori.',
                    'country_of_origin' => 'Japón',
                    'release_year' => 1997,
                    'total_seasons' => 1,
                    'total_episodes' => 39,
                    'duration_minutes' => 23,
                    'banner_image' => 'https://s4.anilist.co/file/anilistcdn/media/anime/banner/440-0Gzds5AlVDN6.jpg',
                    'cover_image' => 'https://s4.anilist.co/file/anilistcdn/media/anime/cover/medium/b440-r4l3N5WGgUOZ.jpg',
                    'is_featured' => false,
                    'moderation_status' => 'approved',
                    'moderation_notes' => 'Ficha editorial aprobada. Disponibles episodios 1 al 30 con audio latino.',
                    'published_at' => CarbonImmutable::create(1997, 4, 2, 12, 0, 0, 'UTC'),
                ]
            );

            $tagIds = collect([
                ['name' => 'Anime', 'slug' => 'anime', 'category' => 'format'],
                ['name' => 'Completada', 'slug' => 'completada', 'category' => 'status'],
                ['name' => 'Japón', 'slug' => 'japon', 'category' => 'country'],
                ['name' => 'Yuri', 'slug' => 'yuri', 'category' => 'identity'],
                ['name' => 'Romance', 'slug' => 'romance', 'category' => 'genre'],
                ['name' => 'Drama', 'slug' => 'drama', 'category' => 'theme'],
                ['name' => 'Fantasía', 'slug' => 'fantasia', 'category' => 'theme'],
                ['name' => 'Escuela', 'slug' => 'escuela', 'category' => 'setting'],
                ['name' => 'Audio latino', 'slug' => 'audio-latino', 'category' => 'availability'],
            ])->map(fn (array $tag): int => Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                $tag + ['is_active' => true]
            )->id)->all();

            $series->tags()->sync($tagIds);

            $firstAirDate = CarbonImmutable::create(1997, 4, 2, 12, 0, 0, 'UTC');

            foreach (range(1, 30) as $number) {
                $episode = Episode::query()->updateOrCreate(
                    [
                        'series_id' => $series->id,
                        'season_number' => 1,
                        'episode_number' => $number,
                    ],
                    [
                        'created_by' => $admin->id,
                        'approved_by' => $admin->id,
                        'title' => "Episodio {$number}",
                        'slug' => self::SERIES_SLUG.'-s1e'.$number,
                        'release_date' => $firstAirDate->addWeeks($number - 1)->toDateString(),
                        'duration_minutes' => 23,
                        'description' => "Revolutionary Girl Utena · episodio {$number}, con audio latino.",
                        'moderation_status' => 'approved',
                        'moderation_notes' => 'HLS validado en video.mundoyuri.com.',
                        'published_at' => $firstAirDate->addWeeks($number - 1),
                        'notify_subscribers' => false,
                    ]
                );

                $paddedNumber = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

                EpisodeSource::query()->updateOrCreate(
                    [
                        'episode_id' => $episode->id,
                        'sort_order' => 1,
                    ],
                    [
                        'provider' => 'cloudflare_hls',
                        'source_type' => 'full',
                        'label' => 'Principal · Audio latino',
                        'video_url' => "https://video.mundoyuri.com/anime/utena%20latino/UTENA%20CAPITULO%20{$paddedNumber}.HLS/master.m3u8",
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('Revolutionary Girl Utena: ficha y 30 episodios HLS sincronizados.');
    }
}
