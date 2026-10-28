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

class UpsertPlutoSeeder extends Seeder
{
    private const SERIES_SLUG = 'pluto';

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
                    'title' => 'Pluto',
                    'alternate_titles' => [
                        'Pluto The Series',
                        'Tales, Stars, Love',
                        'นิทาน ดวงดาว ความรัก',
                        'Pluto: Cuentos, estrellas, amor',
                    ],
                    'content_type' => 'series',
                    'catalog_section' => 'series-gl',
                    'status' => 'completed',
                    'description' => 'Tras el accidente que deja a su hermana gemela en coma, Ai-oon recibe una petición imposible: hacerse pasar por ella para terminar la relación con May. Pero May no puede verla y, mientras la mentira se prolonga, Ai-oon descubre una cercanía que cambia el rumbo de ambas. Entre secretos familiares, identidad y pérdidas, Pluto cuenta una historia de amor que aprende a encontrarse incluso en la oscuridad.',
                    'seo_title' => 'Pluto: serie GL tailandesa completa en español',
                    'seo_description' => 'Mira Pluto, la serie GL tailandesa de NamtanFilm. Ai-oon suplanta a su gemela y se acerca a May en una historia de romance, secretos y destino.',
                    'country_of_origin' => 'Tailandia',
                    'release_year' => 2024,
                    'total_seasons' => 1,
                    'total_episodes' => 12,
                    'duration_minutes' => 50,
                    'banner_image' => 'series/banners/pluto.webp',
                    'cover_image' => 'series/covers/pluto.webp',
                    'is_featured' => false,
                    'moderation_status' => 'approved',
                    'moderation_notes' => 'Ficha editorial aprobada. Los 12 episodios se reproducen por HLS desde video.mundoyuri.com.',
                    'published_at' => CarbonImmutable::create(2024, 10, 19, 12, 0, 0, 'UTC'),
                ]
            );

            $tagIds = collect([
                ['name' => 'Girls Love', 'slug' => 'girls-love', 'category' => 'identity'],
                ['name' => 'Serie', 'slug' => 'serie', 'category' => 'format'],
                ['name' => 'Completada', 'slug' => 'completada', 'category' => 'status'],
                ['name' => 'Tailandia', 'slug' => 'tailandia', 'category' => 'country'],
                ['name' => 'Romance', 'slug' => 'romance', 'category' => 'genre'],
                ['name' => 'Drama', 'slug' => 'drama', 'category' => 'tone'],
                ['name' => 'Misterio', 'slug' => 'misterio', 'category' => 'tone'],
                ['name' => 'Amor secreto', 'slug' => 'amor-secreto', 'category' => 'trope'],
                ['name' => 'Subtítulos en español', 'slug' => 'subtitulos-en-espanol', 'category' => 'availability'],
                ['name' => 'NamtanFilm', 'slug' => 'namtanfilm', 'category' => 'duo'],
            ])->map(fn (array $tag): int => Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                $tag + ['is_active' => true]
            )->id)->all();

            $series->tags()->sync($tagIds);

            $artists = [
                Artist::query()->updateOrCreate(
                    ['slug' => 'namtan-tipnaree-weerawatnodom'],
                    [
                        'name' => 'Namtan Tipnaree Weerawatnodom',
                        'aliases' => 'Tipnaree Weerawatnodom, Namtan, น้ำตาล ทิพนารี',
                        'country_of_origin' => 'Tailandia',
                        'biography' => 'Actriz tailandesa. Interpreta a Ai-oon y Ob-oom en Pluto.',
                        'is_active' => true,
                    ]
                ),
                Artist::query()->updateOrCreate(
                    ['slug' => 'film-rachanun-mahawan'],
                    [
                        'name' => 'Film Rachanun Mahawan',
                        'aliases' => 'Rachanun Mahawan, Film, ฟิล์ม รชานันท์',
                        'country_of_origin' => 'Tailandia',
                        'biography' => 'Actriz tailandesa. Interpreta a May Metawee en Pluto.',
                        'is_active' => true,
                    ]
                ),
            ];

            $series->artists()->sync([
                $artists[0]->id => ['role' => 'Protagonista', 'character_name' => 'Ai-oon / Ob-oom', 'sort_order' => 1],
                $artists[1]->id => ['role' => 'Protagonista', 'character_name' => 'May Metawee', 'sort_order' => 2],
            ]);

            $duo = Duo::query()->updateOrCreate(
                ['slug' => 'namtanfilm'],
                [
                    'name' => 'NamtanFilm · Namtan & Film',
                    'aliases' => 'NamtanFilm, Namtan Film, Namtan & Film',
                    'description' => 'Dupla protagonista de Pluto como Ai-oon y May.',
                    'is_active' => true,
                ]
            );
            $series->duos()->sync([$duo->id => ['sort_order' => 1]]);

            $firstAirDate = CarbonImmutable::create(2024, 10, 19, 12, 0, 0, 'UTC');

            foreach (range(1, 12) as $number) {
                $publishedAt = $firstAirDate->addWeeks($number - 1);
                $isAvailable = $number !== 12;
                $episode = Episode::query()->updateOrCreate(
                    ['series_id' => $series->id, 'season_number' => 1, 'episode_number' => $number],
                    [
                        'created_by' => $admin->id,
                        'approved_by' => $admin->id,
                        'title' => "Episodio {$number}",
                        'slug' => self::SERIES_SLUG."-s1e{$number}",
                        'release_date' => $publishedAt->toDateString(),
                        'duration_minutes' => 50,
                        'description' => "Pluto · episodio {$number}.",
                        // The supplied master playlist for episode 12 is not yet
                        // available. Keep its metadata ready, but do not expose a
                        // broken player to visitors until the HLS returns 200.
                        'moderation_status' => $isAvailable ? 'approved' : 'pending',
                        'moderation_notes' => $isAvailable
                            ? 'HLS configurado en video.mundoyuri.com.'
                            : 'Pendiente: el HLS del episodio 12 aún no está disponible.',
                        'published_at' => $isAvailable ? $publishedAt : null,
                        'notify_subscribers' => false,
                    ]
                );

                EpisodeSource::query()->updateOrCreate(
                    ['episode_id' => $episode->id, 'sort_order' => 1],
                    [
                        'provider' => 'cloudflare_hls',
                        'source_type' => 'full',
                        'label' => 'Principal',
                        'video_url' => "https://video.mundoyuri.com/Pluto/Pluto.S01e".str_pad((string) $number, 2, '0', STR_PAD_LEFT).'.Iq.HLS/master.m3u8',
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('Pluto: ficha, dupla y 12 episodios HLS sincronizados.');
    }
}
