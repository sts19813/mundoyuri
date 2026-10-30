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

class UpsertPoisonousLoveSeeder extends Seeder
{
    private const SERIES_SLUG = 'poisonous-love';

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
                    'title' => 'Poisonous Love',
                    'alternate_titles' => [
                        'Phit Rak',
                        'พิษรัก',
                        'Poisonous Love The Series',
                    ],
                    'content_type' => 'series',
                    'catalog_section' => 'series-gl',
                    'status' => 'completed',
                    'description' => 'La brillante cirujana cardíaca Premsinee está a punto de casarse cuando Pat, una actriz de fama y carácter explosivo, irrumpe en la boda para revelar el secreto del prometido. El desastre las obliga a acercarse, y ambas descubren que ya se habían cruzado en una noche inesperada. Entre heridas, deseo y secretos, Poisonous Love sigue la relación entre Prem y Pat mientras el amor se vuelve tan intenso como peligroso.',
                    'seo_title' => 'Poisonous Love: serie GL tailandesa completa en español',
                    'seo_description' => 'Mira Poisonous Love, la serie GL tailandesa de Ginny y Jayna. Prem y Pat se encuentran tras una boda arruinada en un romance de drama, secretos y pasión.',
                    'country_of_origin' => 'Tailandia',
                    'release_year' => 2025,
                    'total_seasons' => 1,
                    'total_episodes' => 11,
                    'duration_minutes' => 53,
                    'banner_image' => 'series/banners/poisonous-love.webp',
                    'cover_image' => 'series/covers/poisonous-love.webp',
                    'is_featured' => false,
                    'moderation_status' => 'approved',
                    'moderation_notes' => 'Ficha editorial aprobada. Los 11 episodios usan HLS en video.mundoyuri.com.',
                    'published_at' => CarbonImmutable::create(2025, 9, 20, 12, 0, 0, 'UTC'),
                ]
            );

            $tagIds = collect([
                ['name' => 'Girls Love', 'slug' => 'girls-love', 'category' => 'identity'],
                ['name' => 'Serie', 'slug' => 'serie', 'category' => 'format'],
                ['name' => 'Completada', 'slug' => 'completada', 'category' => 'status'],
                ['name' => 'Tailandia', 'slug' => 'tailandia', 'category' => 'country'],
                ['name' => 'Romance', 'slug' => 'romance', 'category' => 'genre'],
                ['name' => 'Drama', 'slug' => 'drama', 'category' => 'tone'],
                ['name' => 'Rivales a amantes', 'slug' => 'rivales-a-amantes', 'category' => 'trope'],
                ['name' => 'Medicina', 'slug' => 'medicina', 'category' => 'setting'],
                ['name' => 'Basada en novela', 'slug' => 'basada-en-novela', 'category' => 'source'],
                ['name' => 'Subtítulos en español', 'slug' => 'subtitulos-en-espanol', 'category' => 'availability'],
                ['name' => 'GinJay', 'slug' => 'ginjay', 'category' => 'duo'],
            ])->map(fn (array $tag): int => Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                $tag + ['is_active' => true]
            )->id)->all();
            $series->tags()->sync($tagIds);

            $ginny = Artist::query()->updateOrCreate(
                ['slug' => 'ginny-natnicha-pratipnatsiri'],
                [
                    'name' => 'Ginny Natnicha Pratipnatsiri',
                    'aliases' => 'Natnicha Pratipnatsiri, Ginny, จินนี่ ณัฐณิชา',
                    'country_of_origin' => 'Tailandia',
                    'biography' => 'Actriz tailandesa. Interpreta a la Dra. Premsinee en Poisonous Love.',
                    'is_active' => true,
                ]
            );
            $jayna = Artist::query()->updateOrCreate(
                ['slug' => 'jayna-angelina-stevens'],
                [
                    'name' => 'Jayna Angelina Stevens',
                    'aliases' => 'Angelina Stevens, Jayna, เจนน่า แองเจลิน่า',
                    'country_of_origin' => 'Tailandia',
                    'biography' => 'Actriz tailandesa. Interpreta a Pat en Poisonous Love.',
                    'is_active' => true,
                ]
            );
            $series->artists()->sync([
                $ginny->id => ['role' => 'Protagonista', 'character_name' => 'Dra. Premsinee (Prem)', 'sort_order' => 1],
                $jayna->id => ['role' => 'Protagonista', 'character_name' => 'Pat', 'sort_order' => 2],
            ]);

            $duo = Duo::query()->updateOrCreate(
                ['slug' => 'ginjay'],
                [
                    'name' => 'GinJay · Ginny & Jayna',
                    'aliases' => 'GinJay, Ginny Jayna, Jayna Ginny',
                    'description' => 'Dupla protagonista de Poisonous Love como Prem y Pat.',
                    'is_active' => true,
                ]
            );
            $series->duos()->sync([$duo->id => ['sort_order' => 1]]);

            $firstAirDate = CarbonImmutable::create(2025, 9, 20, 12, 0, 0, 'UTC');

            foreach (range(1, 11) as $number) {
                $publishedAt = $number === 11
                    ? CarbonImmutable::create(2026, 1, 8, 12, 0, 0, 'UTC')
                    : $firstAirDate->addWeeks($number - 1);
                $title = $number === 11 ? 'Especial' : "Episodio {$number}";

                $episode = Episode::query()->updateOrCreate(
                    ['series_id' => $series->id, 'season_number' => 1, 'episode_number' => $number],
                    [
                        'created_by' => $admin->id,
                        'approved_by' => $admin->id,
                        'title' => $title,
                        'slug' => self::SERIES_SLUG."-s1e{$number}",
                        'release_date' => $publishedAt->toDateString(),
                        'duration_minutes' => 53,
                        'description' => $number === 11
                            ? 'Poisonous Love · episodio especial.'
                            : "Poisonous Love · episodio {$number}.",
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
                        'video_url' => 'https://video.mundoyuri.com/Poisonous%20Love/Poisonous%20Love%20Ep'.$number.'.HLS/master.m3u8',
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('Poisonous Love: ficha, elenco y 11 episodios HLS sincronizados.');
    }
}
