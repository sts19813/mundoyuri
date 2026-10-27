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

class UpsertMariaSamaGaMiteruSeeder extends Seeder
{
    private const SERIES_SLUG = 'maria-sama-ga-miteru';

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
                    'title' => 'Maria-sama ga Miteru',
                    'alternate_titles' => [
                        'Maria Watches Over Us',
                        'MariMite',
                        'マリア様がみてる',
                        'La Virgen María nos observa',
                    ],
                    'content_type' => 'series',
                    'catalog_section' => 'anime',
                    'status' => 'completed',
                    'description' => 'Yumi Fukuzawa estudia en la prestigiosa Academia Femenina Lillian, donde las alumnas mayores guían a las menores mediante el vínculo de sœur. Cuando Sachiko Ogasawara, la admirada Rosa en Bouton, se fija en Yumi, ambas comienzan una relación que transformará su lugar en el Consejo Yamayuri y el modo en que entienden la amistad, la confianza y el amor.',
                    'seo_title' => 'Maria-sama ga Miteru · anime yuri en español',
                    'seo_description' => 'Mira Maria-sama ga Miteru, el clásico anime yuri ambientado en la Academia Lillian. Sigue el comienzo del vínculo entre Yumi y Sachiko.',
                    'country_of_origin' => 'Japón',
                    'release_year' => 2004,
                    'total_seasons' => 1,
                    'total_episodes' => 13,
                    'duration_minutes' => 23,
                    'banner_image' => 'series/banners/maria-sama-ga-miteru.webp',
                    'cover_image' => 'series/covers/maria-sama-ga-miteru.webp',
                    'is_featured' => false,
                    'moderation_status' => 'approved',
                    'moderation_notes' => 'Ficha editorial aprobada. Primera temporada independiente; episodios HLS alojados en video.mundoyuri.com.',
                    'published_at' => CarbonImmutable::create(2004, 1, 7, 12, 0, 0, 'UTC'),
                ]
            );

            $tagIds = collect([
                ['name' => 'Anime', 'slug' => 'anime', 'category' => 'format'],
                ['name' => 'Completada', 'slug' => 'completada', 'category' => 'status'],
                ['name' => 'Clásico', 'slug' => 'clasico', 'category' => 'theme'],
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

            $firstAirDate = CarbonImmutable::create(2004, 1, 7, 12, 0, 0, 'UTC');

            foreach (range(1, 13) as $number) {
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
                        'description' => "Maria-sama ga Miteru · episodio {$number}.",
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
                        'video_url' => "https://video.mundoyuri.com/anime/marimite1/Cap%20{$number}.HLS/master.m3u8",
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('Maria-sama ga Miteru: ficha y 13 episodios HLS sincronizados.');
    }
}
