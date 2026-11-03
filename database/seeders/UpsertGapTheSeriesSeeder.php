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

class UpsertGapTheSeriesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = User::query()->where('role', 'admin')->orderBy('id')->firstOrFail();
            $romance = Genre::query()->where('slug', 'romance')->firstOrFail();
            $series = Series::query()->firstOrCreate(['slug' => 'gap-the-series'], [
                'genre_id' => $romance->id,
                'created_by' => $admin->id,
                'approved_by' => $admin->id,
                'title' => 'GAP: The Series',
                'alternate_titles' => ['GAP', 'GAP The Series', 'Gap: The Pink Theory', 'ทฤษฎีสีชมพู', 'La teoría rosa'],
                'content_type' => 'series',
                'catalog_section' => 'series-gl',
                'status' => 'completed',
                'description' => 'Desde niña, Mon admira a Khun Sam, una mujer que la ayudó cuando más lo necesitaba. Al terminar sus estudios, decide trabajar en la empresa de Sam y descubre que su jefa es mucho más reservada de lo que imaginaba. La cercanía transforma la admiración en amor, pero las diferencias de edad y clase social, las expectativas familiares y las reglas de la empresa pondrán a prueba sus sentimientos. Protagonizada por Freen Sarocha y Becky Armstrong, GAP: The Series es una historia tailandesa basada en la novela de Chao Planoy.',
                'seo_title' => 'GAP: The Series | Serie GL de Freen y Becky',
                'seo_description' => 'Conoce GAP: The Series, la serie GL tailandesa de Freen y Becky. Descubre la historia de Sam y Mon, su reparto y sus 12 episodios.',
                'country_of_origin' => 'Tailandia',
                'release_year' => 2022,
                'total_seasons' => 1,
                'total_episodes' => 12,
                'banner_image' => 'series/banners/gap-the-series.webp',
                'cover_image' => 'series/covers/gap-the-series.webp',
                'is_featured' => false,
                'moderation_status' => 'approved',
                'moderation_notes' => 'Serie emitida entre el 19/11/2022 y el 11/02/2023. HLS verificados en video.mundoyuri.com.',
                'published_at' => CarbonImmutable::parse('2022-11-19 12:00:00', 'UTC'),
            ]);

            foreach ([
                ['Girls Love', 'girls-love', 'identity'], ['Serie', 'serie', 'format'],
                ['Completada', 'completada', 'status'], ['Tailandia', 'tailandia', 'country'],
                ['Romance', 'romance', 'genre'], ['Drama', 'drama', 'tone'],
                ['Comedia romántica', 'comedia-romantica', 'tone'], ['Romance de oficina', 'romance-de-oficina', 'trope'],
                ['Diferencia de clase', 'diferencia-de-clase', 'trope'], ['Basada en novela', 'basada-en-novela', 'source'],
                ['Subtítulos en español', 'subtitulos-en-espanol', 'availability'],
                ['FreenBecky', 'freenbecky', 'duo'],
            ] as [$name, $slug, $category]) {
                $tag = Tag::query()->firstOrCreate(['slug' => $slug], ['name' => $name, 'category' => $category, 'is_active' => true]);
                $series->tags()->syncWithoutDetaching([$tag->id]);
            }

            // Reuse established artist and duo records without overwriting their profiles.
            $freen = Artist::query()->where('slug', 'freen-sarocha-chankimha')->firstOrFail();
            $becky = Artist::query()->where('slug', 'becky-armstrong')->firstOrFail();
            $duo = Duo::query()->where('slug', 'freenbecky')->firstOrFail();
            $series->artists()->syncWithoutDetaching([
                $freen->id => ['role' => 'Protagonista', 'character_name' => 'Sam Samanan Anantrakul', 'sort_order' => 1],
                $becky->id => ['role' => 'Protagonista', 'character_name' => 'Mon Kornkamon Phetpailin', 'sort_order' => 2],
            ]);
            $series->duos()->syncWithoutDetaching([$duo->id => ['sort_order' => 1]]);

            $dates = ['2022-11-19', '2022-11-26', '2022-12-03', '2022-12-10', '2022-12-17', '2022-12-24', '2023-01-07', '2023-01-14', '2023-01-21', '2023-01-28', '2023-02-04', '2023-02-11'];
            $durations = [76, 68, 66, 54, 62, 55, 57, 68, 65, 48, 61, 74];
            foreach (range(1, 12) as $number) {
                $date = $dates[$number - 1];
                $episode = Episode::query()->firstOrCreate(
                    ['series_id' => $series->id, 'season_number' => 1, 'episode_number' => $number],
                    [
                        'created_by' => $admin->id, 'approved_by' => $admin->id,
                        'title' => "Episodio {$number}", 'slug' => "gap-the-series-s1e{$number}",
                        'release_date' => $date, 'duration_minutes' => $durations[$number - 1],
                        'description' => "GAP: The Series · episodio {$number} de la historia de Sam y Mon.",
                        'moderation_status' => 'approved', 'moderation_notes' => 'HLS verificado en video.mundoyuri.com.',
                        'published_at' => CarbonImmutable::parse($date.' 12:00:00', 'UTC'),
                        'notify_subscribers' => false,
                    ],
                );
                $folder = in_array($number, [4, 5, 6], true) ? 'CAP' : 'Cap';
                EpisodeSource::query()->firstOrCreate(['episode_id' => $episode->id, 'sort_order' => 1], [
                    'provider' => 'cloudflare_hls', 'source_type' => 'full', 'label' => 'Principal',
                    'video_url' => "https://video.mundoyuri.com/gap/{$folder}%20{$number}.HLS/master.m3u8",
                    'is_primary' => true,
                ]);
            }
        });

        $this->command?->info('GAP: ficha y 12 episodios sincronizados.');
    }
}
