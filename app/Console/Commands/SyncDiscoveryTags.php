<?php

namespace App\Console\Commands;

use App\Models\Series;
use App\Models\Tag;
use App\Models\Artist;
use App\Models\Duo;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SyncDiscoveryTags extends Command
{
    protected $signature = 'catalog:sync-discovery-tags';

    protected $description = 'Assigns baseline discovery tags to every catalog title from its existing metadata.';

    public function handle(): int
    {
        $catalog = $this->tagCatalog();
        $tags = collect($catalog)->mapWithKeys(function (array $tag): array {
            $model = Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                Arr::except($tag, ['slug']) + ['slug' => $tag['slug']]
            );

            return [$tag['slug'] => $model->id];
        });

        $count = 0;

        Series::query()->with('genre')->orderBy('id')->each(function (Series $series) use ($tags, &$count): void {
            $slugs = $this->slugsFor($series);
            $tagIds = $tags->only($slugs)->values()->all();

            if ($tagIds !== []) {
                $series->tags()->syncWithoutDetaching($tagIds);
                $count++;
            }
        });

        $this->syncVerifiedDuos();

        $this->info("Etiquetas base sincronizadas para {$count} títulos.");

        return self::SUCCESS;
    }

    /** @return array<int, array{name: string, slug: string, category: string, is_active: bool}> */
    private function tagCatalog(): array
    {
        return [
            ['name' => 'Girls Love', 'slug' => 'girls-love', 'category' => 'identity', 'is_active' => true],
            ['name' => 'Yuri', 'slug' => 'yuri', 'category' => 'identity', 'is_active' => true],
            ['name' => 'Anime', 'slug' => 'anime', 'category' => 'format', 'is_active' => true],
            ['name' => 'Película', 'slug' => 'pelicula', 'category' => 'format', 'is_active' => true],
            ['name' => 'Subtítulos en español', 'slug' => 'subtitulos-en-espanol', 'category' => 'availability', 'is_active' => true],
            ['name' => 'Romance', 'slug' => 'romance', 'category' => 'genre', 'is_active' => true],
            ['name' => 'Comedia', 'slug' => 'comedia', 'category' => 'genre', 'is_active' => true],
            ['name' => 'En emisión', 'slug' => 'en-emision', 'category' => 'status', 'is_active' => true],
            ['name' => 'Completada', 'slug' => 'completada', 'category' => 'status', 'is_active' => true],
            ['name' => 'Próximamente', 'slug' => 'proximamente', 'category' => 'status', 'is_active' => true],
            ['name' => 'Tailandia', 'slug' => 'tailandia', 'category' => 'country', 'is_active' => true],
            ['name' => 'Japón', 'slug' => 'japon', 'category' => 'country', 'is_active' => true],
            ['name' => 'Taiwán', 'slug' => 'taiwan', 'category' => 'country', 'is_active' => true],
            ['name' => 'Singapur', 'slug' => 'singapur', 'category' => 'country', 'is_active' => true],
            ['name' => 'Universidad', 'slug' => 'universidad', 'category' => 'setting', 'is_active' => true],
            ['name' => 'Escuela', 'slug' => 'escuela', 'category' => 'setting', 'is_active' => true],
            ['name' => 'Oficina', 'slug' => 'oficina', 'category' => 'setting', 'is_active' => true],
            ['name' => 'Fantasía', 'slug' => 'fantasia', 'category' => 'theme', 'is_active' => true],
            ['name' => 'Drama', 'slug' => 'drama', 'category' => 'theme', 'is_active' => true],
            ['name' => 'Amistad a romance', 'slug' => 'amistad-a-romance', 'category' => 'theme', 'is_active' => true],
            ['name' => 'Enemigas a amantes', 'slug' => 'enemigas-a-amantes', 'category' => 'theme', 'is_active' => true],
        ];
    }

    /** @return array<int, string> */
    private function slugsFor(Series $series): array
    {
        $slugs = ['subtitulos-en-espanol'];
        $text = Str::lower($series->title.' '.$series->description);

        $slugs[] = $series->catalog_section === 'anime' ? 'yuri' : 'girls-love';

        if ($series->catalog_section === 'anime') {
            $slugs[] = 'anime';
        }

        if ($series->content_type === 'movie') {
            $slugs[] = 'pelicula';
        }

        $slugs[] = match ($series->status) {
            'ongoing' => 'en-emision',
            'upcoming' => 'proximamente',
            default => 'completada',
        };

        if ($series->genre?->slug && in_array($series->genre->slug, ['romance', 'comedia'], true)) {
            $slugs[] = $series->genre->slug;
        }

        $country = match (Str::lower((string) $series->country_of_origin)) {
            'tailandia', 'thailand' => 'tailandia',
            'japon', 'japón', 'japan' => 'japon',
            'taiwan', 'taiwán' => 'taiwan',
            'singapur', 'singapore' => 'singapur',
            default => null,
        };

        if ($country) {
            $slugs[] = $country;
        }

        foreach ([
            'universidad' => ['universidad', 'university', 'college', 'campus'],
            'escuela' => ['escuela', 'school', 'academia', 'estudiante'],
            'oficina' => ['oficina', 'office', 'trabajo', 'empresa'],
            'fantasia' => ['fantas', 'magia', 'villana', 'diosa', 'mágic'],
            'drama' => ['drama', 'secreto', 'sombra', 'traged'],
            'amistad-a-romance' => ['mejor amiga', 'amistad', 'best friend'],
            'enemigas-a-amantes' => ['enemiga', 'rival', 'enemies'],
        ] as $slug => $needles) {
            if (Str::contains($text, $needles)) {
                $slugs[] = $slug;
            }
        }

        return array_values(array_unique($slugs));
    }

    private function syncVerifiedDuos(): void
    {
        $this->syncDuo(
            ['hometown-romance', 'harmony-secret'],
            'lmsy',
            'LMSY',
            'Lookmhee & Sonya',
            [
                ['slug' => 'lookmhee-punyapat', 'name' => 'Lookmhee Punyapat', 'aliases' => 'Punyapat Wangpongsathaporn, Lookmhee'],
                ['slug' => 'sonya-saranphat', 'name' => 'Sonya Saranphat', 'aliases' => 'Saranphat Pedersen, Sonya'],
            ]
        );

        $this->syncDuo(
            ['apple-my-love-temporada-1', 'apple-my-love-temporada-2'],
            'ormfolk',
            'OrmFolk',
            'Ormsin & Folk',
            [
                ['slug' => 'ormsin-supitcha-limsommut', 'name' => 'Ormsin Supitcha Limsommut', 'aliases' => 'Supitcha Limsommut, Ormsin, Orm'],
                ['slug' => 'folk-sutima-kokiatwanit', 'name' => 'Folk Sutima Kokiatwanit', 'aliases' => 'Sutima Kokiatwanit, Folk'],
            ]
        );

        $this->syncDuo(
            ['uranus-2324'],
            'freenbecky',
            'FreenBecky',
            'Freen & Becky',
            [
                ['slug' => 'freen-sarocha-chankimha', 'name' => 'Freen Sarocha Chankimha', 'aliases' => 'Freen Sarocha, Freen'],
                ['slug' => 'becky-armstrong', 'name' => 'Becky Armstrong', 'aliases' => 'Rebecca Patricia Armstrong, Becky'],
            ]
        );

        $this->syncDuo(
            ['my-safe-zone'],
            'lenamiu',
            'LenaMiu',
            'Lena & Miu',
            [
                ['slug' => 'lena-lalina-schuett', 'name' => 'Lena Lalina Schuett', 'aliases' => 'Lorena Schuett, Lena'],
                ['slug' => 'miu-natsha-taechamongkalapiwat', 'name' => 'Miu Natsha Taechamongkalapiwat', 'aliases' => 'Natsha Taechamongkalapiwat, Miu'],
            ]
        );

        $this->syncDuo(
            ['love-on-hire'],
            'ginjay',
            'GinJay',
            'Ginny & Jayna',
            [
                ['slug' => 'ginny-natnicha-pratipnatsiri', 'name' => 'Ginny Natnicha Pratipnatsiri', 'aliases' => 'Natnicha Pratipnatsiri, Ginny'],
                ['slug' => 'jayna-angelina-stevens', 'name' => 'Jayna Angelina Stevens', 'aliases' => 'Angelina Stevens, Jayna'],
            ]
        );

        $this->syncDuo(
            ['love-beyond-dreams'],
            'mieaya',
            'MieAya',
            'Miephat & Aya',
            [
                ['slug' => 'miephat-phattaranan-padpai', 'name' => 'Miephat Phattaranan Padpai', 'aliases' => 'Mie Phattaranan, Miephat'],
                ['slug' => 'aya-orapan-phongmaykin', 'name' => 'Aya Orapan Phongmaykin', 'aliases' => 'Aya Orapan, Aya'],
            ]
        );

        $this->syncDuo(
            ['broken-of-love'],
            'fayeatom',
            'FayeAtom',
            'Faye & Atom',
            [
                ['slug' => 'faye-peraya-malisorn', 'name' => 'Faye Peraya Malisorn', 'aliases' => 'Faye Peraya, Faye'],
                ['slug' => 'atom-pariya-piyapanopas', 'name' => 'Atom Pariya Piyapanopas', 'aliases' => 'Atom Pariya, Atom'],
            ]
        );

        $this->syncDuo(
            ['whale-store-xoxo'],
            'milklove',
            'MilkLove',
            'Milk & Love',
            [
                ['slug' => 'milk-pansa-vosbein', 'name' => 'Milk Pansa Vosbein', 'aliases' => 'Pansa Vosbein, Milk'],
                ['slug' => 'love-pattranite-limpatiyakorn', 'name' => 'Love Pattranite Limpatiyakorn', 'aliases' => 'Pattranite Limpatiyakorn, Love'],
            ]
        );
    }

    /** @param array<int, string> $seriesSlugs
     *  @param array<int, array{slug: string, name: string, aliases: string}> $artists
     */
    private function syncDuo(array $seriesSlugs, string $duoSlug, string $tagName, string $members, array $artists): void
    {
        $series = Series::query()->whereIn('slug', $seriesSlugs)->get();

        if ($series->isEmpty()) {
            return;
        }

        $artistIds = collect($artists)->map(function (array $artist): int {
            return Artist::query()->updateOrCreate(
                ['slug' => $artist['slug']],
                $artist + ['country_of_origin' => 'Tailandia', 'is_active' => true]
            )->id;
        })->all();

        $duo = Duo::query()->updateOrCreate(
            ['slug' => $duoSlug],
            [
                'name' => "{$tagName} · {$members}",
                'description' => "Dupla de {$members}.",
                'is_active' => true,
            ]
        );
        $tag = Tag::query()->updateOrCreate(
            ['slug' => $duoSlug],
            ['name' => $tagName, 'category' => 'duo', 'is_active' => true]
        );

        $series->each(function (Series $item) use ($artistIds, $duo, $tag): void {
            $item->artists()->syncWithoutDetaching($artistIds);
            $item->duos()->syncWithoutDetaching([$duo->id]);
            $item->tags()->syncWithoutDetaching([$tag->id]);
        });
    }
}
