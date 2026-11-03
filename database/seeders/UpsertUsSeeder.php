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

class UpsertUsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = User::query()->where('role', 'admin')->orderBy('id')->firstOrFail();
            $romance = Genre::query()->where('slug', 'romance')->firstOrFail();
            $firstAirDate = CarbonImmutable::create(2025, 1, 18, 12, 0, 0, 'UTC');

            $series = Series::query()->firstOrCreate(['slug' => 'us'], [
                'genre_id' => $romance->id,
                'created_by' => $admin->id,
                'approved_by' => $admin->id,
                'title' => 'Us',
                'alternate_titles' => ['Us The Series', 'Us รักของเรา', 'รักของเรา', 'Our Love'],
                'content_type' => 'series',
                'catalog_section' => 'series-gl',
                'status' => 'completed',
                'description' => 'Dokrak trabaja en una cafetería dentro de una galería de arte, donde conoce a Pam, una estudiante de odontología que visita el lugar con frecuencia. Sus sentimientos crecen en silencio, pero todo se complica cuando su hermano Kawi también se enamora de Pam y le pide ayuda para conquistarla. Us es una historia tailandesa de amor, familia y sentimientos difíciles de confesar, basada en la novela de Chao Planoy.',
                'seo_title' => 'Us: serie GL tailandesa de Emi y Bonnie en español',
                'seo_description' => 'Descubre Us รักของเรา, la serie GL tailandesa de Emi y Bonnie. Dokrak y Pam se encuentran entre el amor secreto y los lazos familiares.',
                'country_of_origin' => 'Tailandia',
                'release_year' => 2025,
                'total_seasons' => 1,
                'total_episodes' => 12,
                'banner_image' => 'series/banners/us.webp',
                'cover_image' => 'series/covers/us.webp',
                'is_featured' => false,
                'moderation_status' => 'approved',
                'moderation_notes' => 'Serie de GMMTV. Los episodios 6, 7 y 12 quedan pendientes hasta que sus HLS estén disponibles.',
                'published_at' => $firstAirDate,
            ]);

            $tags = [
                ['Girls Love', 'girls-love', 'identity'], ['Serie', 'serie', 'format'],
                ['Completada', 'completada', 'status'], ['Tailandia', 'tailandia', 'country'],
                ['Romance', 'romance', 'genre'], ['Drama', 'drama', 'tone'],
                ['Amor secreto', 'amor-secreto', 'trope'], ['Triángulo amoroso', 'triangulo-amoroso', 'trope'],
                ['Basada en novela', 'basada-en-novela', 'source'], ['EmiBonnie', 'emibonnie', 'duo'],
            ];
            foreach ($tags as [$name, $slug, $category]) {
                $tag = Tag::query()->firstOrCreate(['slug' => $slug], ['name' => $name, 'category' => $category, 'is_active' => true]);
                $series->tags()->syncWithoutDetaching([$tag->id]);
            }

            $emi = Artist::query()->firstOrCreate(['slug' => 'emi-thasorn-klinnium'], [
                'name' => 'Emi Thasorn', 'aliases' => 'Thasorn Klinnium, Emi', 'country_of_origin' => 'Tailandia', 'is_active' => true,
            ]);
            $bonnie = Artist::query()->firstOrCreate(['slug' => 'bonnie-pattraphus-borattasuwan'], [
                'name' => 'Bonnie Pattraphus', 'aliases' => 'Pattraphus Borattasuwan, Bonnie', 'country_of_origin' => 'Tailandia', 'is_active' => true,
            ]);
            $series->artists()->syncWithoutDetaching([
                $bonnie->id => ['role' => 'Protagonista', 'character_name' => 'Dokrak', 'sort_order' => 1],
                $emi->id => ['role' => 'Protagonista', 'character_name' => 'Pam', 'sort_order' => 2],
            ]);
            $duo = Duo::query()->firstOrCreate(['slug' => 'emibonnie'], [
                'name' => 'EmiBonnie · Emi & Bonnie', 'aliases' => 'EmiBonnie, Emi Bonnie, Emi & Bonnie', 'description' => 'Dupla de Emi y Bonnie.', 'is_active' => true,
            ]);
            $series->duos()->syncWithoutDetaching([$duo->id => ['sort_order' => 1]]);

            foreach (range(1, 12) as $number) {
                $airDate = $firstAirDate->addWeeks($number - 1);
                $available = ! in_array($number, [6, 7, 12], true);
                $episode = Episode::query()->firstOrCreate(
                    ['series_id' => $series->id, 'season_number' => 1, 'episode_number' => $number],
                    [
                        'created_by' => $admin->id,
                        'approved_by' => $available ? $admin->id : null,
                        'title' => "Episodio {$number}",
                        'slug' => "us-s1e{$number}",
                        'release_date' => $airDate->toDateString(),
                        'description' => "Us · episodio {$number}.",
                        'moderation_status' => $available ? 'approved' : 'pending',
                        'moderation_notes' => $available ? 'HLS verificado en video.mundoyuri.com.' : 'Pendiente: el HLS devuelve 404.',
                        'published_at' => $available ? $airDate : null,
                        'notify_subscribers' => false,
                    ]
                );
                EpisodeSource::query()->firstOrCreate(
                    ['episode_id' => $episode->id, 'sort_order' => 1],
                    [
                        'provider' => 'cloudflare_hls', 'source_type' => 'full', 'label' => 'Principal',
                        'video_url' => "https://video.mundoyuri.com/us/US%20{$number}.HLS/master.m3u8",
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('Us: ficha y episodios preparados. Los HLS no disponibles permanecen pendientes.');
    }
}
