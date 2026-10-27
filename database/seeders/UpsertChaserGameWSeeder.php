<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Duo;
use App\Models\Episode;
use App\Models\EpisodeSource;
use App\Models\Genre;
use App\Models\Series;
use App\Models\Tag;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpsertChaserGameWSeeder extends Seeder
{
    private const SERIES_SLUG = 'chaser-game-w';

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
                    'title' => 'Chaser Game W',
                    'alternate_titles' => [
                        'Chaser Game W: My Evil Boss is My Ex-Girlfriend',
                        'Chaser Game W: My Power-Harassing Boss Is My Ex-Girlfriend',
                        'チェイサーゲームW パワハラ上司は私の元カノ',
                        'Chaser Game W: Mi jefa abusiva es mi exnovia',
                    ],
                    'content_type' => 'series',
                    'catalog_section' => 'series-gl',
                    'status' => 'completed',
                    'description' => 'En la desarrolladora Dynamic Dream, Itsuki Harumoto debe liderar la adaptación de un gran webtoon chino. Su contraparte del cliente resulta ser Fuyu Hayashi, la exnovia que dejó atrás en la universidad. Entre la presión del trabajo, los secretos y una relación que nunca se cerró, ambas tendrán que decidir si pueden enfrentar el dolor para volver a elegirse.',
                    'seo_title' => 'Chaser Game W: serie GL japonesa en español',
                    'seo_description' => 'Mira Chaser Game W, serie GL japonesa de romance y oficina. Itsuki se reencuentra con Fuyu, su exnovia, en un proyecto de videojuegos.',
                    'country_of_origin' => 'Japón',
                    'release_year' => 2024,
                    'total_seasons' => 1,
                    'total_episodes' => 8,
                    'duration_minutes' => 25,
                    'banner_image' => 'https://video-tvtokyo.imgix.net/chasergamew/_MASTER/image/chasergamew_pg_05.jpg',
                    'cover_image' => 'https://image.tmdb.org/t/p/w500/450LkifqTnYQW4IZVNl26Vyzsha.jpg',
                    'is_featured' => false,
                    'moderation_status' => 'approved',
                    'moderation_notes' => 'Ficha editorial aprobada. Episodios HLS alojados en video.mundoyuri.com.',
                    'published_at' => CarbonImmutable::create(2024, 1, 8, 12, 0, 0, 'UTC'),
                ]
            );

            $tagIds = collect([
                ['name' => 'Girls Love', 'slug' => 'girls-love', 'category' => 'identity'],
                ['name' => 'Completada', 'slug' => 'completada', 'category' => 'status'],
                ['name' => 'Japón', 'slug' => 'japon', 'category' => 'country'],
                ['name' => 'Romance', 'slug' => 'romance', 'category' => 'genre'],
                ['name' => 'Drama', 'slug' => 'drama', 'category' => 'theme'],
                ['name' => 'Oficina', 'slug' => 'oficina', 'category' => 'setting'],
                ['name' => 'Exparejas', 'slug' => 'exparejas', 'category' => 'theme'],
                ['name' => 'Subtítulos en español', 'slug' => 'subtitulos-en-espanol', 'category' => 'availability'],
                ['name' => 'YuukaYurika', 'slug' => 'yuuka-yurika', 'category' => 'duo'],
            ])->map(fn (array $tag): int => Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                $tag + ['is_active' => true]
            )->id)->all();

            $series->tags()->sync($tagIds);

            $artists = [
                Artist::query()->updateOrCreate(
                    ['slug' => 'yuuka-sugai'],
                    [
                        'name' => 'Yuuka Sugai',
                        'aliases' => 'Yūka Sugai, Sugai Yuuka, 菅井友香',
                        'country_of_origin' => 'Japón',
                        'biography' => 'Actriz japonesa. Interpreta a Itsuki Harumoto en Chaser Game W.',
                        'is_active' => true,
                    ]
                ),
                Artist::query()->updateOrCreate(
                    ['slug' => 'yurika-nakamura'],
                    [
                        'name' => 'Yurika Nakamura',
                        'aliases' => 'Nakamura Yurika, 中村ゆりか',
                        'country_of_origin' => 'Japón',
                        'biography' => 'Actriz japonesa. Interpreta a Fuyu Hayashi en Chaser Game W.',
                        'is_active' => true,
                    ]
                ),
            ];

            $series->artists()->sync([
                $artists[0]->id => ['role' => 'Protagonista', 'character_name' => 'Itsuki Harumoto', 'sort_order' => 1],
                $artists[1]->id => ['role' => 'Protagonista', 'character_name' => 'Fuyu Hayashi', 'sort_order' => 2],
            ]);

            $duo = Duo::query()->updateOrCreate(
                ['slug' => 'yuuka-yurika'],
                [
                    'name' => 'YuukaYurika · Yuuka Sugai & Yurika Nakamura',
                    'description' => 'Dupla protagonista de Chaser Game W como Itsuki y Fuyu.',
                    'is_active' => true,
                ]
            );
            $series->duos()->sync([$duo->id => ['sort_order' => 1]]);

            $firstAirDate = CarbonImmutable::create(2024, 1, 8, 12, 0, 0, 'UTC');

            foreach (range(1, 8) as $number) {
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
                        'duration_minutes' => 25,
                        'description' => "Chaser Game W · episodio {$number}.",
                        'moderation_status' => 'approved',
                        'moderation_notes' => 'HLS configurado en video.mundoyuri.com.',
                        'published_at' => $firstAirDate->addWeeks($number - 1),
                        'notify_subscribers' => false,
                    ]
                );

                EpisodeSource::query()->updateOrCreate(
                    ['episode_id' => $episode->id, 'sort_order' => 1],
                    [
                        'provider' => 'cloudflare_hls',
                        'source_type' => 'full',
                        'label' => 'Principal',
                        'video_url' => "https://video.mundoyuri.com/chaser%20game/Cap%20{$number}.HLS/master.m3u8",
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('Chaser Game W: ficha, dupla y 8 episodios HLS sincronizados.');
    }
}
