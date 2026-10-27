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

class UpsertMariaSamaGaMiteruHaruSeeder extends Seeder
{
    private const SERIES_SLUG = 'maria-sama-ga-miteru-haru';

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
                    'title' => 'Maria-sama ga Miteru: Haru',
                    'alternate_titles' => [
                        'Maria Watches Over Us: Printemps',
                        'Maria-sama ga Miteru ~Haru~',
                        'マリア様がみてる～春～',
                        'MariMite: Haru',
                        'La Virgen María nos observa: Primavera',
                    ],
                    'content_type' => 'series',
                    'catalog_section' => 'anime',
                    'status' => 'completed',
                    'description' => 'La primavera llega a la Academia Femenina Lillian y el Consejo Yamayuri se prepara para despedir a sus alumnas de último año. Yumi Fukuzawa y Sachiko Ogasawara viven nuevas decisiones, mientras las Rosas Blancas buscan a su próxima sœur. Entre cambios, afectos y promesas de hermandad, esta temporada profundiza los vínculos que sostienen a las estudiantes.',
                    'seo_title' => 'Maria-sama ga Miteru: Haru · anime yuri en español',
                    'seo_description' => 'Mira Maria-sama ga Miteru: Haru, segunda temporada del clásico anime yuri. La primavera transforma los vínculos de la Academia Lillian.',
                    'country_of_origin' => 'Japón',
                    'release_year' => 2004,
                    'total_seasons' => 1,
                    'total_episodes' => 13,
                    'duration_minutes' => 24,
                    'banner_image' => 'https://image-cf.kddi-video.com/144/1445c471848d0a6b6742e5335a4a0ec1/fit-background-transparent/1747267700/1080x608.png',
                    'cover_image' => 'https://yurilibrary.info/sakuhin/7magyou/images7magyou/marimite_anime2.jpg',
                    'is_featured' => false,
                    'moderation_status' => 'approved',
                    'moderation_notes' => 'Ficha editorial aprobada. Segunda temporada independiente; episodios HLS alojados en video.mundoyuri.com.',
                    'published_at' => CarbonImmutable::create(2004, 7, 4, 12, 0, 0, 'UTC'),
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
                ['name' => 'Subtítulos en español', 'slug' => 'subtitulos-en-espanol', 'category' => 'availability'],
                ['name' => 'Yuri', 'slug' => 'yuri', 'category' => 'identity'],
            ])->map(fn (array $tag): int => Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                $tag + ['is_active' => true]
            )->id)->all();

            $series->tags()->sync($tagIds);

            $firstAirDate = CarbonImmutable::create(2004, 7, 4, 12, 0, 0, 'UTC');

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
                        'duration_minutes' => 24,
                        'description' => "Maria-sama ga Miteru: Haru · episodio {$number}.",
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
                        'video_url' => "https://video.mundoyuri.com/anime/marimite2/Cap%20{$number}.HLS/master.m3u8",
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('Maria-sama ga Miteru: Haru: ficha y 13 episodios HLS sincronizados.');
    }
}
