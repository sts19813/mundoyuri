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

class UpsertAoiHanaSeeder extends Seeder
{
    private const SERIES_SLUG = 'aoi-hana';

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
                    'title' => 'Aoi Hana',
                    'alternate_titles' => [
                        'Sweet Blue Flowers',
                        'Blue Flower',
                        '青い花',
                        'Flores azules',
                    ],
                    'content_type' => 'series',
                    'catalog_section' => 'anime',
                    'status' => 'completed',
                    'description' => 'Diez años después de separarse, Fumi Manjōme vuelve a encontrarse con Akira Okudaira, su amiga de la infancia, al comenzar la preparatoria en Kamakura. Mientras recuperan la confianza que compartían de niñas, ambas atraviesan las dudas, amistades y primeros amores que acompañan esa etapa. Aoi Hana es un yuri íntimo y sensible sobre descubrirse, querer y aprender a ser una misma.',
                    'seo_title' => 'Aoi Hana (Sweet Blue Flowers) · anime yuri completo en español',
                    'seo_description' => 'Mira Aoi Hana (Sweet Blue Flowers), anime yuri de 11 episodios sobre Fumi y Akira, amistad, escuela y primeros amores.',
                    'country_of_origin' => 'Japón',
                    'release_year' => 2009,
                    'total_seasons' => 1,
                    'total_episodes' => 11,
                    'duration_minutes' => 22,
                    'banner_image' => 'series/banners/aoi-hana.webp',
                    'cover_image' => 'series/covers/aoi-hana.webp',
                    'is_featured' => false,
                    'moderation_status' => 'approved',
                    'moderation_notes' => 'Ficha editorial aprobada. Los 11 episodios HLS fueron verificados en video.mundoyuri.com.',
                    'published_at' => CarbonImmutable::create(2009, 7, 2, 12, 0, 0, 'UTC'),
                ]
            );

            $tagIds = collect([
                ['name' => 'Anime', 'slug' => 'anime', 'category' => 'format'],
                ['name' => 'Completada', 'slug' => 'completada', 'category' => 'status'],
                ['name' => 'Drama', 'slug' => 'drama', 'category' => 'tone'],
                ['name' => 'Escuela', 'slug' => 'escuela', 'category' => 'setting'],
                ['name' => 'Girls Love', 'slug' => 'girls-love', 'category' => 'identity'],
                ['name' => 'Japón', 'slug' => 'japon', 'category' => 'country'],
                ['name' => 'Juventud', 'slug' => 'juventud', 'category' => 'theme'],
                ['name' => 'Romance', 'slug' => 'romance', 'category' => 'genre'],
                ['name' => 'Slice of Life', 'slug' => 'slice-of-life', 'category' => 'tone'],
                ['name' => 'Subtítulos en español', 'slug' => 'subtitulos-en-espanol', 'category' => 'availability'],
                ['name' => 'Yuri', 'slug' => 'yuri', 'category' => 'identity'],
            ])->map(fn (array $tag): int => Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                $tag + ['is_active' => true]
            )->id)->all();

            $series->tags()->sync($tagIds);

            $episodeTitles = [
                'Flower Tale',
                'Spring Storm',
                'Rise and Shine',
                'Youth Is a Lovely Thing',
                'Wuthering Heights (1)',
                'Wuthering Heights (2)',
                'When the Leaves Were New',
                'Love Is Blind',
                'A Midsummer Night’s Dream',
                'The Happy Prince',
                'Fireworks in Winter',
            ];

            $firstAirDate = CarbonImmutable::create(2009, 7, 2, 12, 0, 0, 'UTC');

            foreach ($episodeTitles as $index => $title) {
                $number = $index + 1;
                $publishedAt = $firstAirDate->addWeeks($index);

                $episode = Episode::query()->updateOrCreate(
                    ['series_id' => $series->id, 'season_number' => 1, 'episode_number' => $number],
                    [
                        'created_by' => $admin->id,
                        'approved_by' => $admin->id,
                        'title' => $title,
                        'slug' => self::SERIES_SLUG."-s1e{$number}",
                        'release_date' => $publishedAt->toDateString(),
                        'duration_minutes' => 22,
                        'description' => "Aoi Hana · episodio {$number}: {$title}.",
                        'moderation_status' => 'approved',
                        'moderation_notes' => 'HLS verificado y configurado en video.mundoyuri.com.',
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
                        'video_url' => "https://video.mundoyuri.com/anime/aoi%20hana/Cap%20{$number}.HLS/master.m3u8",
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('Aoi Hana: ficha, SEO, etiquetas y 11 episodios HLS sincronizados.');
    }
}
