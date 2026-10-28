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

class UpsertMariaSamaGaMiteruFourthSeasonSeeder extends Seeder
{
    private const SERIES_SLUG = 'maria-sama-ga-miteru-4th-season';

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
                    'title' => 'Maria-sama ga Miteru: 4th Season',
                    'alternate_titles' => [
                        'Maria Watches Over Us 4th Season',
                        'Maria-sama ga Miteru 4th Season',
                        'マリア様がみてる 4thシーズン',
                        'MariMite 4th Season',
                        'La Virgen María nos observa: Cuarta temporada',
                    ],
                    'content_type' => 'series',
                    'catalog_section' => 'anime',
                    'status' => 'completed',
                    'description' => 'El Consejo Yamayuri de la Academia Femenina Lillian prepara una obra con la Academia Hanadera y la llegada del festival escolar llena el campus de expectativas. Mientras Yumi Fukuzawa intenta cumplir sus nuevas responsabilidades, Sachiko Ogasawara le encarga una decisión decisiva: elegir a su petite sœur. Entre amistades, tradiciones y cambios, la cuarta temporada acompaña a Yumi en un nuevo capítulo de su vínculo con Sachiko.',
                    'seo_title' => 'Maria-sama ga Miteru: 4th Season · anime yuri en español',
                    'seo_description' => 'Mira Maria-sama ga Miteru: 4th Season, el cierre del clásico anime yuri de la Academia Lillian, con Yumi y Sachiko.',
                    'country_of_origin' => 'Japón',
                    'release_year' => 2009,
                    'total_seasons' => 1,
                    'total_episodes' => 13,
                    'duration_minutes' => 25,
                    'banner_image' => 'series/banners/maria-sama-ga-miteru-4th-season.webp',
                    'cover_image' => 'series/covers/maria-sama-ga-miteru-4th-season.webp',
                    'is_featured' => false,
                    'moderation_status' => 'approved',
                    'moderation_notes' => 'Ficha editorial aprobada. Cuarta temporada independiente; HLS alojado en video.mundoyuri.com.',
                    'published_at' => CarbonImmutable::create(2009, 1, 3, 12, 0, 0, 'UTC'),
                ]
            );

            $tagIds = collect([
                ['name' => 'Anime', 'slug' => 'anime', 'category' => 'format'],
                ['name' => 'Clásico', 'slug' => 'clasico', 'category' => 'theme'],
                ['name' => 'Completada', 'slug' => 'completada', 'category' => 'status'],
                ['name' => 'Drama', 'slug' => 'drama', 'category' => 'theme'],
                ['name' => 'Escuela', 'slug' => 'escuela', 'category' => 'setting'],
                ['name' => 'Japón', 'slug' => 'japon', 'category' => 'country'],
                ['name' => 'Romance', 'slug' => 'romance', 'category' => 'genre'],
                ['name' => 'Sœurs', 'slug' => 'soeurs', 'category' => 'theme'],
                ['name' => 'Subtítulos en español', 'slug' => 'subtitulos-en-espanol', 'category' => 'availability'],
                ['name' => 'Yuri', 'slug' => 'yuri', 'category' => 'identity'],
            ])->map(fn (array $tag): int => Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                $tag + ['is_active' => true]
            )->id)->all();

            $series->tags()->sync($tagIds);

            $firstAirDate = CarbonImmutable::create(2009, 1, 3, 12, 0, 0, 'UTC');

            foreach (range(1, 13) as $number) {
                $publishedAt = $firstAirDate->addWeeks($number - 1);

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
                        'slug' => self::SERIES_SLUG."-s1e{$number}",
                        'release_date' => $publishedAt->toDateString(),
                        'duration_minutes' => 25,
                        'description' => "Maria-sama ga Miteru: 4th Season · episodio {$number}.",
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
                        'video_url' => "https://video.mundoyuri.com/marimite4/Cap%20{$number}.HLS/master.m3u8",
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('Maria-sama ga Miteru: 4th Season: ficha y 13 episodios HLS sincronizados.');
    }
}
