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

class UpsertYesOrNoFilmsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = User::query()->where('role', 'admin')->orderBy('id')->firstOrFail();
            $romance = Genre::query()->where('slug', 'romance')->firstOrFail();

            $aom = Artist::query()->updateOrCreate(
                ['slug' => 'aom-sushar-manaying'],
                [
                    'name' => 'Aom Sushar Manaying',
                    'aliases' => 'Sushar Manaying, Sucharat Manaying, Aom, ออม สุชาร์',
                    'country_of_origin' => 'Tailandia',
                    'biography' => 'Actriz tailandesa. Interpreta a Pie en Yes or No y su secuela.',
                    'is_active' => true,
                ]
            );
            $tina = Artist::query()->updateOrCreate(
                ['slug' => 'tina-suppanad-jittaleela'],
                [
                    'name' => 'Tina Suppanad Jittaleela',
                    'aliases' => 'Suppanad Jittaleela, Supanart Jittaleela, Tina, ติ๊นา ศุภนาฎ',
                    'country_of_origin' => 'Tailandia',
                    'biography' => 'Actriz y cantante tailandesa. Interpreta a Kim en Yes or No y su secuela.',
                    'is_active' => true,
                ]
            );
            $duo = Duo::query()->updateOrCreate(
                ['slug' => 'aom-tina'],
                [
                    'name' => 'AomTina · Aom & Tina',
                    'aliases' => 'AomTina, Aom Tina, Aom & Tina, TinaAom',
                    'description' => 'Dupla protagonista de Yes or No como Pie y Kim.',
                    'is_active' => true,
                ]
            );

            $films = [
                [
                    'slug' => 'yes-or-no',
                    'title' => 'Yes or No',
                    'alternate_titles' => ['Yes or No? So I Love You', 'Yes or No: I Love You', 'อยากรัก ก็รักเลย', 'Yak Rak Ko Rak Loei'],
                    'description' => 'Pie, una estudiante universitaria de carácter reservado, debe compartir dormitorio con Kim, una chica segura de sí misma que se expresa con un estilo tom. Lo que empieza con incomodidad y malentendidos se transforma en una amistad intensa que obliga a ambas a preguntarse si están dispuestas a elegir el amor pese a las expectativas de quienes las rodean.',
                    'seo_title' => 'Yes or No: película GL tailandesa en español',
                    'seo_description' => 'Mira Yes or No, clásica película GL tailandesa de AomTina. Pie y Kim descubren que su convivencia universitaria puede convertirse en amor.',
                    'release_date' => CarbonImmutable::create(2010, 12, 16, 12, 0, 0, 'UTC'),
                    'duration' => 107,
                    'cover' => 'series/covers/yes-or-no.webp',
                    'banner' => 'series/banners/yes-or-no.webp',
                    'hls' => 'https://video.mundoyuri.com/peliculas/Yes%20or%20No.HLS/master.m3u8',
                    'tags' => [
                        ['name' => 'Universidad', 'slug' => 'universidad', 'category' => 'setting'],
                        ['name' => 'Primer amor', 'slug' => 'primer-amor', 'category' => 'trope'],
                        ['name' => 'Amistad a romance', 'slug' => 'amistad-a-romance', 'category' => 'trope'],
                    ],
                ],
                [
                    'slug' => 'yes-or-no-2-come-back-to-me',
                    'title' => 'Yes or No 2: Come Back to Me',
                    'alternate_titles' => ['Yes or No 2', 'Yes or No? Come Back 2 Me', 'รักไม่รัก อย่ากั๊กเลย', 'Rak Mai Rak Ya Kak Loei'],
                    'description' => 'Tres años después, Pie y Kim siguen juntas, pero sus prácticas profesionales las mandan a extremos opuestos de Tailandia. La distancia, nuevas amistades y los planes de futuro ponen a prueba una relación que ambas creían segura. Yes or No 2 continúa su historia con una pregunta más difícil: cómo volver a elegirse cuando crecer las lleva por caminos distintos.',
                    'seo_title' => 'Yes or No 2: Come Back to Me · película GL en español',
                    'seo_description' => 'Mira Yes or No 2: Come Back to Me, la secuela GL tailandesa de AomTina. Pie y Kim enfrentan distancia, trabajo y nuevas decisiones.',
                    'release_date' => CarbonImmutable::create(2012, 8, 16, 12, 0, 0, 'UTC'),
                    'duration' => 108,
                    'cover' => 'series/covers/yes-or-no-2-come-back-to-me.webp',
                    'banner' => 'series/banners/yes-or-no-2-come-back-to-me.webp',
                    'hls' => 'https://video.mundoyuri.com/peliculas/Yes%20or%20No%202.HLS/master.m3u8',
                    'tags' => [
                        ['name' => 'Exparejas', 'slug' => 'exparejas', 'category' => 'trope'],
                        ['name' => 'Relación a distancia', 'slug' => 'relacion-a-distancia', 'category' => 'trope'],
                        ['name' => 'Vida adulta', 'slug' => 'vida-adulta', 'category' => 'setting'],
                    ],
                ],
            ];

            foreach ($films as $film) {
                $series = Series::query()->updateOrCreate(
                    ['slug' => $film['slug']],
                    [
                        'genre_id' => $romance->id,
                        'created_by' => $admin->id,
                        'approved_by' => $admin->id,
                        'title' => $film['title'],
                        'alternate_titles' => $film['alternate_titles'],
                        'content_type' => 'movie',
                        // Inicio mezcla el catálogo principal `series-gl`, donde ya
                        // viven las películas GL como Uranus y Girls Like Girls.
                        'catalog_section' => 'series-gl',
                        'status' => 'completed',
                        'description' => $film['description'],
                        'seo_title' => $film['seo_title'],
                        'seo_description' => $film['seo_description'],
                        'country_of_origin' => 'Tailandia',
                        'release_year' => $film['release_date']->year,
                        'total_seasons' => 1,
                        'total_episodes' => 1,
                        'duration_minutes' => $film['duration'],
                        'banner_image' => $film['banner'],
                        'cover_image' => $film['cover'],
                        'is_featured' => false,
                        'moderation_status' => 'approved',
                        'moderation_notes' => 'Ficha editorial aprobada. Reproductor HLS pendiente de disponibilidad del archivo.',
                        'published_at' => $film['release_date'],
                    ]
                );

                $tagIds = collect(array_merge([
                    ['name' => 'Girls Love', 'slug' => 'girls-love', 'category' => 'identity'],
                    ['name' => 'Película', 'slug' => 'pelicula', 'category' => 'format'],
                    ['name' => 'Completada', 'slug' => 'completada', 'category' => 'status'],
                    ['name' => 'Tailandia', 'slug' => 'tailandia', 'category' => 'country'],
                    ['name' => 'Romance', 'slug' => 'romance', 'category' => 'genre'],
                    ['name' => 'Comedia romántica', 'slug' => 'comedia-romantica', 'category' => 'tone'],
                    ['name' => 'Subtítulos en español', 'slug' => 'subtitulos-en-espanol', 'category' => 'availability'],
                    ['name' => 'AomTina', 'slug' => 'aom-tina', 'category' => 'duo'],
                ], $film['tags']))->map(fn (array $tag): int => Tag::query()->updateOrCreate(
                    ['slug' => $tag['slug']],
                    $tag + ['is_active' => true]
                )->id)->all();
                $series->tags()->sync($tagIds);
                $series->artists()->sync([
                    $aom->id => ['role' => 'Protagonista', 'character_name' => 'Pie', 'sort_order' => 1],
                    $tina->id => ['role' => 'Protagonista', 'character_name' => 'Kim', 'sort_order' => 2],
                ]);
                $series->duos()->sync([$duo->id => ['sort_order' => 1]]);

                $episode = Episode::query()->updateOrCreate(
                    ['series_id' => $series->id, 'season_number' => 1, 'episode_number' => 1],
                    [
                        'created_by' => $admin->id,
                        'approved_by' => $admin->id,
                        'title' => 'Película',
                        'slug' => $film['slug'].'-pelicula',
                        'release_date' => $film['release_date']->toDateString(),
                        'duration_minutes' => $film['duration'],
                        'description' => $film['description'],
                        'moderation_status' => 'pending',
                        'moderation_notes' => 'Pendiente: el HLS todavía no responde correctamente.',
                        'published_at' => null,
                        'notify_subscribers' => false,
                    ]
                );
                EpisodeSource::query()->updateOrCreate(
                    ['episode_id' => $episode->id, 'sort_order' => 1],
                    [
                        'provider' => 'cloudflare_hls',
                        'source_type' => 'full',
                        'label' => 'Principal',
                        'video_url' => $film['hls'],
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('Yes or No: dos fichas de películas y sus HLS sincronizados.');
    }
}
