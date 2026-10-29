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

class UpsertTwentyThreePointFiveSeeder extends Seeder
{
    private const SERIES_SLUG = '23-5';

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
                    'title' => '23.5',
                    'alternate_titles' => [
                        '23.5 The Series',
                        '23.5 องศาที่โลกเอียง',
                        '23.5 When the Earth Is Spinning Around',
                        '23.5 grados: el mundo se inclina',
                    ],
                    'content_type' => 'series',
                    'catalog_section' => 'series-gl',
                    'status' => 'completed',
                    'description' => 'Ongsa llega a una nueva escuela y se enamora de Sun, una compañera brillante y popular. Demasiado tímida para hablarle en persona, le escribe por Instagram bajo el nombre de Earth. Cuando Sun cree que su misteriosa amiga es un chico, Ongsa debe decidir entre proteger esa conexión o revelar quién es realmente. 23.5 es una comedia romántica escolar sobre la primera vez que el mundo parece inclinarse hacia alguien.',
                    'seo_title' => '23.5: serie GL tailandesa completa en español',
                    'seo_description' => 'Mira 23.5, la serie GL tailandesa completa de MilkLove. Ongsa se acerca a Sun con una identidad secreta en una tierna comedia romántica escolar.',
                    'country_of_origin' => 'Tailandia',
                    'release_year' => 2024,
                    'total_seasons' => 1,
                    'total_episodes' => 12,
                    'duration_minutes' => 45,
                    'banner_image' => 'series/banners/23-5.webp',
                    'cover_image' => 'series/covers/23-5.webp',
                    'is_featured' => false,
                    'moderation_status' => 'approved',
                    'moderation_notes' => 'Ficha editorial aprobada. Los episodios disponibles se reproducen por HLS desde video.mundoyuri.com.',
                    'published_at' => CarbonImmutable::create(2024, 3, 8, 12, 0, 0, 'UTC'),
                ]
            );

            $tagIds = collect([
                ['name' => 'Girls Love', 'slug' => 'girls-love', 'category' => 'identity'],
                ['name' => 'Serie', 'slug' => 'serie', 'category' => 'format'],
                ['name' => 'Completada', 'slug' => 'completada', 'category' => 'status'],
                ['name' => 'Tailandia', 'slug' => 'tailandia', 'category' => 'country'],
                ['name' => 'Romance', 'slug' => 'romance', 'category' => 'genre'],
                ['name' => 'Comedia romántica', 'slug' => 'comedia-romantica', 'category' => 'tone'],
                ['name' => 'Escuela', 'slug' => 'escuela', 'category' => 'setting'],
                ['name' => 'Primer amor', 'slug' => 'primer-amor', 'category' => 'trope'],
                ['name' => 'Identidad secreta', 'slug' => 'identidad-secreta', 'category' => 'trope'],
                ['name' => 'Subtítulos en español', 'slug' => 'subtitulos-en-espanol', 'category' => 'availability'],
                ['name' => 'MilkLove', 'slug' => 'milklove', 'category' => 'duo'],
            ])->map(fn (array $tag): int => Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                $tag + ['is_active' => true]
            )->id)->all();
            $series->tags()->sync($tagIds);

            $milk = Artist::query()->updateOrCreate(
                ['slug' => 'milk-pansa-vosbein'],
                [
                    'name' => 'Milk Pansa Vosbein',
                    'aliases' => 'Pansa Vosbein, Milk, พรรณษา วอสเบียน',
                    'country_of_origin' => 'Tailandia',
                    'biography' => 'Actriz tailandesa. Interpreta a Ongsa en 23.5.',
                    'is_active' => true,
                ]
            );
            $love = Artist::query()->updateOrCreate(
                ['slug' => 'love-pattranite-limpatiyakorn'],
                [
                    'name' => 'Love Pattranite Limpatiyakorn',
                    'aliases' => 'Pattranite Limpatiyakorn, Love, ภัทรานิษฐ์ ลิ้มปติยากร',
                    'country_of_origin' => 'Tailandia',
                    'biography' => 'Actriz tailandesa. Interpreta a Sun en 23.5.',
                    'is_active' => true,
                ]
            );
            $series->artists()->sync([
                $milk->id => ['role' => 'Protagonista', 'character_name' => 'Ongsa', 'sort_order' => 1],
                $love->id => ['role' => 'Protagonista', 'character_name' => 'Sun', 'sort_order' => 2],
            ]);

            $duo = Duo::query()->updateOrCreate(
                ['slug' => 'milklove'],
                [
                    'name' => 'MilkLove · Milk & Love',
                    'aliases' => 'MilkLove, Milk Love, Milk & Love',
                    'description' => 'Dupla protagonista de 23.5 como Ongsa y Sun.',
                    'is_active' => true,
                ]
            );
            $series->duos()->sync([$duo->id => ['sort_order' => 1]]);

            $firstAirDate = CarbonImmutable::create(2024, 3, 8, 12, 0, 0, 'UTC');
            // Los HLS 6 al 10 se crearán como pendientes hasta que el origen
            // responda correctamente, evitando exponer reproductores rotos.
            $availableEpisodes = [1, 2, 3, 4, 5, 11, 12];

            foreach (range(1, 12) as $number) {
                $publishedAt = $firstAirDate->addWeeks($number - 1);
                $isAvailable = in_array($number, $availableEpisodes, true);

                $episode = Episode::query()->updateOrCreate(
                    ['series_id' => $series->id, 'season_number' => 1, 'episode_number' => $number],
                    [
                        'created_by' => $admin->id,
                        'approved_by' => $admin->id,
                        'title' => "Episodio {$number}",
                        'slug' => self::SERIES_SLUG."-s1e{$number}",
                        'release_date' => $publishedAt->toDateString(),
                        'duration_minutes' => 45,
                        'description' => "23.5 · episodio {$number}.",
                        'moderation_status' => $isAvailable ? 'approved' : 'pending',
                        'moderation_notes' => $isAvailable
                            ? 'HLS configurado en video.mundoyuri.com.'
                            : 'Pendiente: el HLS aún no está disponible.',
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
                        'video_url' => 'https://video.mundoyuri.com/23.5/23.5.S01e'.str_pad((string) $number, 2, '0', STR_PAD_LEFT).'.HLS/master.m3u8',
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('23.5: ficha, dupla y episodios HLS sincronizados.');
    }
}
