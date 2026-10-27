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

class UpsertMariaSamaGaMiteruThirdSeasonSeeder extends Seeder
{
    private const SERIES_SLUG = 'maria-sama-ga-miteru-3rd-season';

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
                    'title' => 'Maria-sama ga Miteru: 3rd Season',
                    'alternate_titles' => [
                        'Maria Watches Over Us 3rd Season OVA',
                        'Maria-sama ga Miteru 3rd Season OVA',
                        'マリア様がみてる 3rdシーズン',
                        'MariMite 3rd Season',
                    ],
                    'content_type' => 'series',
                    'catalog_section' => 'anime',
                    'status' => 'completed',
                    'description' => 'Durante las vacaciones de verano, Yumi Fukuzawa acompaña a Sachiko Ogasawara a la residencia de su familia. Lo que parecía una escapada tranquila se llena de tensiones cuando viejas conocidas de Sachiko cuestionan el lugar de Yumi a su lado. A lo largo de cinco episodios especiales, la Academia Lillian y sus vínculos de sœur atraviesan nuevas pruebas de afecto, lealtad y crecimiento.',
                    'seo_title' => 'Maria-sama ga Miteru: 3rd Season · OVA yuri en español',
                    'seo_description' => 'Mira Maria-sama ga Miteru: 3rd Season, la OVA yuri de cinco episodios sobre Yumi, Sachiko y la Academia Lillian.',
                    'country_of_origin' => 'Japón',
                    'release_year' => 2006,
                    'total_seasons' => 1,
                    'total_episodes' => 5,
                    'duration_minutes' => 51,
                    'banner_image' => 'series/banners/maria-sama-ga-miteru-3rd-season.webp',
                    'cover_image' => 'series/covers/maria-sama-ga-miteru-3rd-season.webp',
                    'is_featured' => false,
                    'moderation_status' => 'approved',
                    'moderation_notes' => 'Ficha editorial aprobada. Tercera temporada OVA independiente; episodios HLS alojados en video.mundoyuri.com.',
                    'published_at' => CarbonImmutable::create(2006, 11, 29, 12, 0, 0, 'UTC'),
                ]
            );

            $tagIds = collect([
                ['name' => 'Anime', 'slug' => 'anime', 'category' => 'format'],
                ['name' => 'Clásico', 'slug' => 'clasico', 'category' => 'theme'],
                ['name' => 'Completada', 'slug' => 'completada', 'category' => 'status'],
                ['name' => 'Drama', 'slug' => 'drama', 'category' => 'theme'],
                ['name' => 'Escuela', 'slug' => 'escuela', 'category' => 'setting'],
                ['name' => 'Japón', 'slug' => 'japon', 'category' => 'country'],
                ['name' => 'OVA', 'slug' => 'ova', 'category' => 'format'],
                ['name' => 'Romance', 'slug' => 'romance', 'category' => 'genre'],
                ['name' => 'Sœurs', 'slug' => 'soeurs', 'category' => 'theme'],
                ['name' => 'Subtítulos en español', 'slug' => 'subtitulos-en-espanol', 'category' => 'availability'],
                ['name' => 'Yuri', 'slug' => 'yuri', 'category' => 'identity'],
            ])->map(fn (array $tag): int => Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                $tag + ['is_active' => true]
            )->id)->all();

            $series->tags()->sync($tagIds);

            $releaseDates = [
                '2006-11-29',
                '2006-12-29',
                '2007-02-28',
                '2007-05-30',
                '2007-07-25',
            ];

            foreach ($releaseDates as $index => $releaseDate) {
                $number = $index + 1;
                $publishedAt = CarbonImmutable::parse($releaseDate, 'UTC')->setTime(12, 0);

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
                        'release_date' => $releaseDate,
                        'duration_minutes' => 51,
                        'description' => "Maria-sama ga Miteru: 3rd Season · episodio {$number}.",
                        'moderation_status' => 'approved',
                        'moderation_notes' => 'HLS configurado en video.mundoyuri.com.',
                        'published_at' => $publishedAt,
                        'notify_subscribers' => false,
                    ]
                );

                EpisodeSource::query()->updateOrCreate(
                    ['episode_id' => $episode->id, 'sort_order' => 1],
                    [
                        'provider' => 'cloudflare_hls',
                        'source_type' => 'full',
                        'label' => 'Principal',
                        'video_url' => "https://video.mundoyuri.com/anime/marimite3/Cap%20{$number}.HLS/master.m3u8",
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('Maria-sama ga Miteru: 3rd Season: ficha y 5 episodios HLS sincronizados.');
    }
}
