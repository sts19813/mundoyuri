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

class UpsertTheLoyalPinSeeder extends Seeder
{
    private const SERIES_SLUG = 'the-loyal-pin';

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
                    'title' => 'The Loyal Pin',
                    'alternate_titles' => ['The Loyal Pin: ปิ่นภักดิ์', 'Pin Phak', 'ปิ่นภักดิ์', 'La horquilla leal'],
                    'content_type' => 'series',
                    'catalog_section' => 'series-gl',
                    'status' => 'completed',
                    'description' => 'La princesa Anilaphat y Lady Pilantita, conocida como Pin, crecieron juntas y forjaron un vínculo que desafía las reglas de la aristocracia tailandesa de los años cincuenta. Tras volver del extranjero, Anil se niega a aceptar que Pin esté comprometida con otro hombre y hace todo lo posible por proteger el amor que comparten. The Loyal Pin es un romance histórico GL protagonizado por Freen Sarocha y Becky Armstrong, basado en la novela de MonMaw.',
                    'seo_title' => 'The Loyal Pin | Serie GL histórica de Freen y Becky',
                    'seo_description' => 'Mira The Loyal Pin, la serie GL histórica tailandesa de Freen y Becky: el romance de la princesa Anil y Lady Pin en 16 episodios.',
                    'country_of_origin' => 'Tailandia',
                    'release_year' => 2024,
                    'total_seasons' => 1,
                    'total_episodes' => 16,
                    'duration_minutes' => 60,
                    'banner_image' => 'series/banners/the-loyal-pin.webp',
                    'cover_image' => 'series/covers/the-loyal-pin.webp',
                    'is_featured' => false,
                    'moderation_status' => 'approved',
                    'moderation_notes' => 'Serie emitida del 04/08/2024 al 17/11/2024. Los HLS 1–14 fueron verificados; 15 y 16 quedan pendientes porque el origen devuelve 404.',
                    'published_at' => CarbonImmutable::create(2024, 8, 4, 12, 0, 0, 'UTC'),
                ]
            );

            $tagIds = collect([
                ['name' => 'Girls Love', 'slug' => 'girls-love', 'category' => 'identity'],
                ['name' => 'Serie', 'slug' => 'serie', 'category' => 'format'],
                ['name' => 'Completada', 'slug' => 'completada', 'category' => 'status'],
                ['name' => 'Tailandia', 'slug' => 'tailandia', 'category' => 'country'],
                ['name' => 'Romance', 'slug' => 'romance', 'category' => 'genre'],
                ['name' => 'Drama histórico', 'slug' => 'drama-historico', 'category' => 'tone'],
                ['name' => 'Drama familiar', 'slug' => 'drama-familiar', 'category' => 'tone'],
                ['name' => 'Amor prohibido', 'slug' => 'amor-prohibido', 'category' => 'trope'],
                ['name' => 'Amigas de la infancia', 'slug' => 'amigas-de-la-infancia', 'category' => 'trope'],
                ['name' => 'Basada en novela', 'slug' => 'basada-en-novela', 'category' => 'source'],
                ['name' => 'Subtítulos en español', 'slug' => 'subtitulos-en-espanol', 'category' => 'availability'],
                ['name' => 'FreenBecky', 'slug' => 'freenbecky', 'category' => 'duo'],
            ])->map(fn (array $tag): int => Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                $tag + ['is_active' => true]
            )->id)->all();
            $series->tags()->sync($tagIds);

            $freen = Artist::query()->where('slug', 'freen-sarocha-chankimha')->firstOrFail();
            $becky = Artist::query()->where('slug', 'becky-armstrong')->firstOrFail();
            $duo = Duo::query()->where('slug', 'freenbecky')->firstOrFail();
            $series->artists()->sync([
                $freen->id => ['role' => 'Protagonista', 'character_name' => 'Lady Pilantita Kasidit (Pin)', 'sort_order' => 1],
                $becky->id => ['role' => 'Protagonista', 'character_name' => 'Princesa Anilaphat Sawettawarit (Anil)', 'sort_order' => 2],
            ]);
            $series->duos()->sync([$duo->id => ['sort_order' => 1]]);

            $firstAirDate = CarbonImmutable::create(2024, 8, 4, 12, 0, 0, 'UTC');
            foreach (range(1, 16) as $number) {
                $publishedAt = $firstAirDate->addWeeks($number - 1);
                $isAvailable = $number <= 14;
                $episode = Episode::query()->updateOrCreate(
                    ['series_id' => $series->id, 'season_number' => 1, 'episode_number' => $number],
                    [
                        'created_by' => $admin->id,
                        'approved_by' => $admin->id,
                        'title' => "Episodio {$number}",
                        'slug' => self::SERIES_SLUG."-s1e{$number}",
                        'release_date' => $publishedAt->toDateString(),
                        'duration_minutes' => 60,
                        'description' => "The Loyal Pin · episodio {$number} de la historia de Anil y Pin.",
                        'moderation_status' => $isAvailable ? 'approved' : 'pending',
                        'moderation_notes' => $isAvailable
                            ? 'HLS verificado en video.mundoyuri.com.'
                            : 'Pendiente: el HLS proporcionado aún devuelve 404.',
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
                        'video_url' => 'https://video.mundoyuri.com/The%20Loyal%20Pin/The.Loyal.Pin.S01e'.str_pad((string) $number, 2, '0', STR_PAD_LEFT).'.HLS/master.m3u8',
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('The Loyal Pin: ficha, dupla y 16 episodios sincronizados (14 HLS disponibles).');
    }
}
