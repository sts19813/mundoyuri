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

        $managedTagIds = Tag::query()
            ->whereIn('category', ['identity', 'format', 'availability', 'genre', 'status', 'country'])
            ->pluck('id');

        Series::query()->with('genre')->orderBy('id')->each(function (Series $series) use ($tags, $managedTagIds, &$count): void {
            $slugs = $this->slugsFor($series);
            $tagIds = $tags->only($slugs)->values()->all();

            if ($tagIds !== []) {
                // These categories mirror intrinsic metadata. Remove only stale
                // automated values; editorial tags, artists and duos are preserved.
                $series->tags()->detach($managedTagIds->diff($tagIds)->all());
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
            ['name' => 'Serie', 'slug' => 'serie', 'category' => 'format', 'is_active' => true],
            ['name' => 'Película', 'slug' => 'pelicula', 'category' => 'format', 'is_active' => true],
            ['name' => 'OVA', 'slug' => 'ova', 'category' => 'format', 'is_active' => true],
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
            ['name' => 'Estados Unidos', 'slug' => 'estados-unidos', 'category' => 'country', 'is_active' => true],
            ['name' => 'Corea del Sur', 'slug' => 'corea-del-sur', 'category' => 'country', 'is_active' => true],
            ['name' => 'Filipinas', 'slug' => 'filipinas', 'category' => 'country', 'is_active' => true],
            ['name' => 'China', 'slug' => 'china', 'category' => 'country', 'is_active' => true],
            ['name' => 'Universidad', 'slug' => 'universidad', 'category' => 'setting', 'is_active' => true],
            ['name' => 'Escuela', 'slug' => 'escuela', 'category' => 'setting', 'is_active' => true],
            ['name' => 'Oficina', 'slug' => 'oficina', 'category' => 'setting', 'is_active' => true],
            ['name' => 'Histórico', 'slug' => 'historico', 'category' => 'setting', 'is_active' => true],
            ['name' => 'Música', 'slug' => 'musica', 'category' => 'setting', 'is_active' => true],
            ['name' => 'Deportes', 'slug' => 'deportes', 'category' => 'setting', 'is_active' => true],
            ['name' => 'Comedia romántica', 'slug' => 'comedia-romantica', 'category' => 'tone', 'is_active' => true],
            ['name' => 'Drama', 'slug' => 'drama', 'category' => 'tone', 'is_active' => true],
            ['name' => 'Misterio', 'slug' => 'misterio', 'category' => 'tone', 'is_active' => true],
            ['name' => 'Fantasía', 'slug' => 'fantasia', 'category' => 'tone', 'is_active' => true],
            ['name' => 'Ciencia ficción', 'slug' => 'ciencia-ficcion', 'category' => 'tone', 'is_active' => true],
            ['name' => 'Amistad a romance', 'slug' => 'amistad-a-romance', 'category' => 'trope', 'is_active' => true],
            ['name' => 'Enemigas a amantes', 'slug' => 'enemigas-a-amantes', 'category' => 'trope', 'is_active' => true],
            ['name' => 'Exparejas', 'slug' => 'exparejas', 'category' => 'trope', 'is_active' => true],
            ['name' => 'Amor secreto', 'slug' => 'amor-secreto', 'category' => 'trope', 'is_active' => true],
            ['name' => 'Relación falsa', 'slug' => 'relacion-falsa', 'category' => 'trope', 'is_active' => true],
            ['name' => 'Diferencia de edad', 'slug' => 'diferencia-de-edad', 'category' => 'trope', 'is_active' => true],
            ['name' => 'Subtexto yuri', 'slug' => 'subtexto-yuri', 'category' => 'identity', 'is_active' => true],
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
        } elseif ($series->content_type === 'series') {
            $slugs[] = 'serie';
        }

        if ($series->content_type === 'movie') {
            $slugs[] = 'pelicula';
        }

        if (Str::contains($text, ['ova', 'original video animation'])) {
            $slugs[] = 'ova';
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
            'estados unidos', 'united states', 'usa' => 'estados-unidos',
            'corea del sur', 'south korea', 'korea' => 'corea-del-sur',
            'filipinas', 'philippines' => 'filipinas',
            'china' => 'china',
            default => null,
        };

        if ($country) {
            $slugs[] = $country;
        }

        foreach ([
            'universidad' => ['universidad', 'university', 'college', 'campus'],
            'escuela' => ['escuela', 'school', 'academia', 'estudiante'],
            'oficina' => ['oficina', 'office', 'trabajo', 'empresa'],
            'historico' => ['históric', 'historical', 'historia alternativa'],
            'musica' => ['música', 'musica', 'music', 'banda', 'cantante'],
            'deportes' => ['deporte', 'sport', 'fútbol', 'futbol', 'golf', 'lucha'],
            'comedia-romantica' => ['comedia romántica', 'romantic comedy', 'romcom'],
            'fantasia' => ['fantas', 'magia', 'villana', 'diosa', 'mágic'],
            'drama' => ['drama', 'secreto', 'sombra', 'traged'],
            'misterio' => ['misterio', 'mystery', 'investiga', 'asesin'],
            'ciencia-ficcion' => ['ciencia ficción', 'science fiction', 'futuro', 'espacio'],
            'amistad-a-romance' => ['mejor amiga', 'amistad', 'best friend'],
            'enemigas-a-amantes' => ['enemiga', 'rival', 'enemies'],
            'exparejas' => ['expareja', 'ex pareja', 'ex-novia', 'exnovia'],
            'amor-secreto' => ['amor secreto', 'relación secreta', 'relacion secreta'],
            'relacion-falsa' => ['relación falsa', 'relacion falsa', 'fingir ser', 'fake relationship'],
            'diferencia-de-edad' => ['diferencia de edad', 'age gap'],
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
            ],
            ['LMSY', 'LookmheeSonya', 'Lookmhee & Sonya', 'Lookmhee Sonya']
        );

        $this->syncDuo(
            ['apple-my-love-temporada-1', 'apple-my-love-temporada-2'],
            'ormfolk',
            'OrmFolk',
            'Ormsin & Folk',
            [
                ['slug' => 'ormsin-supitcha-limsommut', 'name' => 'Ormsin Supitcha Limsommut', 'aliases' => 'Supitcha Limsommut, Ormsin, Orm'],
                ['slug' => 'folk-sutima-kokiatwanit', 'name' => 'Folk Sutima Kokiatwanit', 'aliases' => 'Sutima Kokiatwanit, Folk'],
            ],
            ['OrmFolk', 'Orm Folk', 'OrmsinFolk']
        );

        $this->syncDuo(
            ['uranus-2324'],
            'freenbecky',
            'FreenBecky',
            'Freen & Becky',
            [
                ['slug' => 'freen-sarocha-chankimha', 'name' => 'Freen Sarocha Chankimha', 'aliases' => 'Freen Sarocha, Freen'],
                ['slug' => 'becky-armstrong', 'name' => 'Becky Armstrong', 'aliases' => 'Rebecca Patricia Armstrong, Becky'],
            ],
            ['FreenBecky', 'Freen Becky', 'Freen & Becky']
        );

        $this->syncDuo(
            ['my-safe-zone'],
            'lenamiu',
            'LenaMiu',
            'Lena & Miu',
            [
                ['slug' => 'lena-lalina-schuett', 'name' => 'Lena Lalina Schuett', 'aliases' => 'Lorena Schuett, Lena'],
                ['slug' => 'miu-natsha-taechamongkalapiwat', 'name' => 'Miu Natsha Taechamongkalapiwat', 'aliases' => 'Natsha Taechamongkalapiwat, Miu'],
            ],
            ['LenaMiu', 'Lena Miu', 'Lena & Miu']
        );

        $this->syncDuo(
            ['love-on-hire'],
            'ginjay',
            'GinJay',
            'Ginny & Jayna',
            [
                ['slug' => 'ginny-natnicha-pratipnatsiri', 'name' => 'Ginny Natnicha Pratipnatsiri', 'aliases' => 'Natnicha Pratipnatsiri, Ginny'],
                ['slug' => 'jayna-angelina-stevens', 'name' => 'Jayna Angelina Stevens', 'aliases' => 'Angelina Stevens, Jayna'],
            ],
            ['GinJay', 'JaynaGinny', 'Ginny Jayna']
        );

        $this->syncDuo(
            ['love-beyond-dreams'],
            'mieaya',
            'MieAya',
            'Miephat & Aya',
            [
                ['slug' => 'miephat-phattaranan-padpai', 'name' => 'Miephat Phattaranan Padpai', 'aliases' => 'Mie Phattaranan, Miephat'],
                ['slug' => 'aya-orapan-phongmaykin', 'name' => 'Aya Orapan Phongmaykin', 'aliases' => 'Aya Orapan, Aya'],
            ],
            ['MieAya', 'Mie Aya', 'Miephat Aya']
        );

        $this->syncDuo(
            ['broken-of-love'],
            'fayeatom',
            'FayeAtom',
            'Faye & Atom',
            [
                ['slug' => 'faye-peraya-malisorn', 'name' => 'Faye Peraya Malisorn', 'aliases' => 'Faye Peraya, Faye'],
                ['slug' => 'atom-pariya-piyapanopas', 'name' => 'Atom Pariya Piyapanopas', 'aliases' => 'Atom Pariya, Atom'],
            ],
            ['FayeAtom', 'Faye Atom', 'Faye & Atom']
        );

        $this->syncDuo(
            ['whale-store-xoxo'],
            'milklove',
            'MilkLove',
            'Milk & Love',
            [
                ['slug' => 'milk-pansa-vosbein', 'name' => 'Milk Pansa Vosbein', 'aliases' => 'Pansa Vosbein, Milk'],
                ['slug' => 'love-pattranite-limpatiyakorn', 'name' => 'Love Pattranite Limpatiyakorn', 'aliases' => 'Pattranite Limpatiyakorn, Love'],
            ],
            ['MilkLove', 'Milk Love', 'Milk & Love']
        );

        $this->syncDuo(['blank-the-series-temporada-1', 'blank-the-series-temporada-2'], 'fayeyoko', 'FayeYoko', 'Faye & Yoko', [
            ['slug' => 'faye-peraya-malisorn', 'name' => 'Faye Peraya Malisorn', 'aliases' => 'Faye Peraya, Faye'],
            ['slug' => 'yoko-apasra-lertprasert', 'name' => 'Yoko Apasra Lertprasert', 'aliases' => 'Yoko Apasra, Yoko'],
        ], ['FayeYoko', 'Faye Yoko', 'Faye & Yoko']);

        $this->syncDuo(['plslove'], 'lenamiu', 'LenaMiu', 'Lena & Miu', [
            ['slug' => 'lena-lalina-schuett', 'name' => 'Lena Lalina Schuett', 'aliases' => 'Lorena Schuett, Lena'],
            ['slug' => 'miu-natsha-taechamongkalapiwat', 'name' => 'Miu Natsha Taechamongkalapiwat', 'aliases' => 'Natsha Taechamongkalapiwat, Miu'],
        ], ['LenaMiu', 'Lena Miu', 'Lena & Miu']);

        $this->syncDuo(['el-aire-los-cuatro-elementos'], 'freenbecky', 'FreenBecky', 'Freen & Becky', [
            ['slug' => 'freen-sarocha-chankimha', 'name' => 'Freen Sarocha Chankimha', 'aliases' => 'Freen Sarocha, Freen'],
            ['slug' => 'becky-armstrong', 'name' => 'Becky Armstrong', 'aliases' => 'Rebecca Patricia Armstrong, Becky'],
        ], ['FreenBecky', 'Freen Becky', 'Freen & Becky']);

        $this->syncDuo(['el-agua-los-cuatro-elementos'], 'englot', 'EngLot', 'Engfa & Charlotte', [
            ['slug' => 'engfa-waraha', 'name' => 'Engfa Waraha', 'aliases' => 'Engfa, MGI Engfa'],
            ['slug' => 'charlotte-austin', 'name' => 'Charlotte Austin', 'aliases' => 'Charlotte, Charlotte Austin'],
        ], ['EngLot', 'EngfaCharlotte', 'Engfa & Charlotte']);

        $this->syncDuo(['la-tierra-los-cuatro-elementos'], 'applemim', 'AppleMim', 'Apple & Mim', [
            ['slug' => 'apple-lapisara-intarasut', 'name' => 'Apple Lapisara', 'aliases' => 'Lapisara Intarasut, Apple'],
            ['slug' => 'mim-panthita-jencharoentham', 'name' => 'Mim Panthita', 'aliases' => 'Panthita Jencharoentham, Mim'],
        ], ['AppleMim', 'Apple Mim', 'Apple & Mim']);

        $this->syncDuo(['enemies-with-benefits'], 'janjingjing', 'JanJingJing', 'Jan & JingJing', [
            ['slug' => 'jan-ployshompoo-supasap', 'name' => 'Jan Ployshompoo', 'aliases' => 'Ployshompoo Supasap, Jan'],
            ['slug' => 'jingjing-yu', 'name' => 'JingJing Yu', 'aliases' => 'Jing Jing Yu, JingJing'],
        ], ['JanJingJing', 'Jan JingJing', 'Jan & JingJing']);

        $this->syncDuo(['fairway-of-love'], 'ingcartoon', 'IngCartoon', 'Ing & Cartoon', [
            ['slug' => 'ing-thanatcha-chaiwut', 'name' => 'Ing Thanatcha', 'aliases' => 'Thanatcha Chaiwut, Ing'],
            ['slug' => 'cartoon-kittaya-pattarasirilert', 'name' => 'Cartoon Kittaya', 'aliases' => 'Kittaya Pattarasirilert, Cartoon'],
        ], ['IngCartoon', 'Ing Cartoon', 'Ing & Cartoon']);
        $this->syncDuo(['fairway-of-love'], 'minagibam', 'MinagiBam', 'Minagi & Bam', [
            ['slug' => 'minagi-ramaneeya', 'name' => 'Minagi Ramaneeya', 'aliases' => 'Minagi'],
            ['slug' => 'bam-rinrada', 'name' => 'Bam Rinrada', 'aliases' => 'Bam'],
        ], ['MinagiBam', 'Minagi Bam', 'Minagi & Bam']);

        $this->syncDuo(['fulfill'], 'oombam', 'OomBam', 'Oom & Bam', [
            ['slug' => 'oom-eisaya-hosuwan', 'name' => 'Oom Eisaya', 'aliases' => 'Eisaya Hosuwan, Oom'],
            ['slug' => 'bam-saralee-pratummaneerat', 'name' => 'Bam Saralee', 'aliases' => 'Saralee Pratummaneerat, Bam'],
        ], ['OomBam', 'Oom Bam', 'Oom & Bam']);

        $this->syncDuo(['in-love-forever-the-series', 'only-you'], 'lingorm', 'LingOrm', 'Lingling & Orm', [
            ['slug' => 'lingling-kwong', 'name' => 'Lingling Kwong', 'aliases' => 'Lingling Sirilak Kwong, Ling'],
            ['slug' => 'orm-kornnaphat-settharattanapong', 'name' => 'Orm Kornnaphat', 'aliases' => 'Kornnaphat Settharattanapong, Orm'],
        ], ['LingOrm', 'Ling Orm', 'LinglingOrm', 'Lingling & Orm']);

        $this->syncDuo(['juliet-juliet'], 'mingmingnepjune', 'MingmingNepjune', 'Mingming & Nepjune', [
            ['slug' => 'mingming-kanyakorn', 'name' => 'Mingming Kanyakorn', 'aliases' => 'Mingming'],
            ['slug' => 'nepjune-nutkitta', 'name' => 'Nepjune Nutkitta', 'aliases' => 'Nepjune'],
        ], ['MingmingNepjune', 'Mingming Nepjune', 'Mingming & Nepjune']);

        $this->syncDuo(['khom-khlang'], 'bintpuinoon', 'BintPuinoon', 'Bint & Puinoon', [
            ['slug' => 'bint-sireethorn-leearamwat', 'name' => 'Bint Sireethorn', 'aliases' => 'Sireethorn Leearamwat, Bint'],
            ['slug' => 'puinoon-warangsiri-tanajarusworaphat', 'name' => 'Puinoon Warangsiri', 'aliases' => 'Warangsiri Tanajarusworaphat, Puinoon'],
        ], ['BintPuinoon', 'Bint Puinoon', 'Bint & Puinoon']);

        $this->syncDuo(['moonshadow'], 'emibonnie', 'EmiBonnie', 'Emi & Bonnie', [
            ['slug' => 'emi-thasorn-klinnium', 'name' => 'Emi Thasorn', 'aliases' => 'Thasorn Klinnium, Emi'],
            ['slug' => 'bonnie-pattraphus-borattasuwan', 'name' => 'Bonnie Pattraphus', 'aliases' => 'Pattraphus Borattasuwan, Bonnie'],
        ], ['EmiBonnie', 'Emi Bonnie', 'Emi & Bonnie']);

        $this->syncDuo(['somewhere-somehow'], 'faymay', 'FayMay', 'Fay & May', [
            ['slug' => 'fay-kanyaphat-na-nakhon', 'name' => 'Fay Kanyaphat', 'aliases' => 'Kanyaphat Na Nakhon, Fay'],
            ['slug' => 'may-yada-wacharamethakul', 'name' => 'May Yada', 'aliases' => 'Yada Wacharamethakul, May'],
        ], ['FayMay', 'Fay May', 'Fay & May']);

        $this->syncDuo(['third-person'], 'garnmimie', 'GarnMimie', 'Garn & Mimie', [
            ['slug' => 'garn-nuttacha-ratchayangkanont', 'name' => 'Garn Nuttacha', 'aliases' => 'Nuttacha Ratchayangkanont, Garn'],
            ['slug' => 'mimie-bhapat-ahchariyasripong', 'name' => 'Mimie Bhapat', 'aliases' => 'Bhapat Ahchariyasripong, Mimie'],
        ], ['GarnMimie', 'Garn Mimie', 'Garn & Mimie']);

        $this->syncDuo(['built-in-love'], 'pimjipineare', 'PimjiPineare', 'Pimji & Pineare', [
            ['slug' => 'pim-pimjira-jaroenlak', 'name' => 'Pim Pimjira Jaroenlak', 'aliases' => 'Pimji, Pim'],
            ['slug' => 'pineare-pannin-charnmanoon', 'name' => 'Pineare Pannin Charnmanoon', 'aliases' => 'Pineare'],
        ], ['PimjiPineare', 'Pimji Pineare', 'Pimji & Pineare']);

        $this->syncDuo(['clairebell'], 'mablepangjie', 'MablePangjie', 'Mable & Pangjie', [
            ['slug' => 'mable-siriwalee', 'name' => 'Mable Siriwalee', 'aliases' => 'Mable'],
            ['slug' => 'pangjie-paphavarin', 'name' => 'Pangjie Paphavarin', 'aliases' => 'Pangjie'],
        ], ['MablePangjie', 'Mable Pangjie', 'Mable & Pangjie']);

        $this->syncDuo(['chasing-love'], 'nilenamwan', 'NileNamwan', 'Nile & Namwan', [
            ['slug' => 'nile-chanidapa', 'name' => 'Nile Chanidapa', 'aliases' => 'Nile'],
            ['slug' => 'namwan-natchaya', 'name' => 'Namwan Natchaya', 'aliases' => 'Namwan'],
        ], ['NileNamwan', 'Nile Namwan', 'Nile & Namwan']);

        $this->syncDuo(['shadow-of-love'], 'fahbell', 'FahBell', 'Praifah & Bebell', [
            ['slug' => 'praifah-penpat-kairapee', 'name' => 'Praifah Penpat', 'aliases' => 'Penpat Kairapee, Fah'],
            ['slug' => 'bebell-patthanarat-ketkaew', 'name' => 'Bebell Patthanarat', 'aliases' => 'Patthanarat Ketkaew, Bell'],
        ], ['FahBell', 'Fah Bell', 'Praifah Bebell']);
    }

    /** @param array<int, string> $seriesSlugs
     *  @param array<int, array{slug: string, name: string, aliases: string}> $artists
     */
    private function syncDuo(array $seriesSlugs, string $duoSlug, string $tagName, string $members, array $artists, array $aliases = []): void
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
                'aliases' => implode(', ', array_values(array_unique([...$aliases, $tagName, $members]))),
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
