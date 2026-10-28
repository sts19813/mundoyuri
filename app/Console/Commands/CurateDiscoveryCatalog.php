<?php

namespace App\Console\Commands;

use App\Models\Artist;
use App\Models\Series;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CurateDiscoveryCatalog extends Command
{
    protected $signature = 'catalog:curate-discovery';

    protected $description = 'Curates catalog metadata, discovery tags, artists and verified duos.';

    public function handle(): int
    {
        DB::transaction(function (): void {
            $this->curateMetadata();
            Artisan::call('catalog:sync-discovery-tags');
            $this->syncEditorialTags();
            $this->syncAdditionalCast();
        });

        $this->info('Catálogo curado: '.$this->catalogSummary());

        return self::SUCCESS;
    }

    private function curateMetadata(): void
    {
        // All published anime in this catalogue are finished works. This also
        // corrects legacy entries that were accidentally imported as ongoing.
        Series::query()->where('catalog_section', 'anime')->update([
            'country_of_origin' => 'Japón',
            'status' => 'completed',
        ]);

        Series::query()
            ->where('catalog_section', 'series-gl')
            ->where(function ($query): void {
                $query->whereNull('country_of_origin')->orWhere('country_of_origin', '');
            })
            ->update(['country_of_origin' => 'Tailandia']);

        Series::query()->where('slug', 'girls-like-girls')->update(['country_of_origin' => 'Estados Unidos']);
        Series::query()->where('slug', 'candy')->update(['country_of_origin' => 'Singapur']);
        Series::query()->where('slug', 'fruit')->update(['country_of_origin' => 'Taiwán']);
        Series::query()->where('slug', 'how-do-we-relationship')->update(['country_of_origin' => 'Japón']);

        foreach ([
            'moonshadow' => 2026,
            'watashi-no-oshi-wa-akuyaku-reijou-me-enamore-de-la-villana' => 2023,
            'yagate-kimi-ni-naru-bloom-into-you' => 2018,
        ] as $slug => $year) {
            Series::query()->where('slug', $slug)->update(['release_year' => $year]);
        }
    }

    private function syncEditorialTags(): void
    {
        $tagIds = Tag::query()->pluck('id', 'slug');

        foreach ([
            'adachi-to-shimamura' => ['escuela', 'amistad-a-romance', 'drama'],
            'candy-boy' => ['escuela', 'comedia-romantica'],
            'citrus' => ['escuela', 'drama'],
            'izetta-the-last-witch' => ['historico', 'fantasia', 'ciencia-ficcion'],
            'kannazuki-no-miko' => ['escuela', 'drama', 'fantasia'],
            'kimi-ga-shinu-made-koi-o-shitai' => ['escuela', 'drama', 'fantasia'],
            'maria-sama-ga-miteru' => ['escuela', 'drama'],
            'maria-sama-ga-miteru-haru' => ['escuela', 'drama'],
            'maria-sama-ga-miteru-3rd-season' => ['escuela', 'drama'],
            'maria-sama-ga-miteru-4th-season' => ['escuela', 'drama'],
            'revolutionary-girl-utena' => ['escuela', 'fantasia', 'misterio'],
            'sakura-trick' => ['escuela', 'comedia-romantica'],
            'strawberry-panic' => ['escuela', 'drama'],
            'tai-ari-deshita' => ['escuela', 'drama'],
            'watashi-ga-koibito-ni-nareru-wake-nai-jan-muri-muri' => ['escuela', 'comedia-romantica'],
            'watashi-no-oshi-wa-akuyaku-reijou-me-enamore-de-la-villana' => ['escuela', 'fantasia', 'comedia-romantica'],
            'yagate-kimi-ni-naru-bloom-into-you' => ['escuela', 'drama'],
            'yami-to-boushi-to-hon-no-tabibito' => ['fantasia', 'misterio'],
            'yuri-kuma-arashi' => ['escuela', 'fantasia', 'misterio'],
            'enemies-with-benefits' => ['enemigas-a-amantes', 'oficina'],
            'harmony-secret' => ['enemigas-a-amantes', 'amor-secreto'],
            'hometown-romance' => ['amistad-a-romance'],
            'khom-khlang' => ['amor-secreto'],
            'my-safe-zone' => ['amistad-a-romance'],
            'plslove' => ['amistad-a-romance'],
            'reverse-with-me' => ['fantasia', 'drama'],
            'uranus-2324' => ['ciencia-ficcion', 'drama'],
            'how-do-we-relationship' => ['universidad', 'amistad-a-romance', 'drama'],
            'shades' => ['escuela', 'misterio', 'drama'],
            'shadow-of-love' => ['amor-secreto', 'drama'],
        ] as $seriesSlug => $slugs) {
            $ids = collect($slugs)->map(fn (string $slug) => $tagIds->get($slug))->filter()->all();

            if ($ids !== []) {
                Series::query()->where('slug', $seriesSlug)->first()?->tags()->syncWithoutDetaching($ids);
            }
        }
    }

    private function syncAdditionalCast(): void
    {
        foreach ([
            'adachi-to-shimamura' => [['sakura-adachi', 'Sakura Adachi', 'Akari Kitou', 'Sakura Adachi'], ['hougetsu-shimamura', 'Hougetsu Shimamura', 'Miku Itou', 'Hougetsu Shimamura']],
            'candy-boy' => [['kanade-sakurai', 'Kanade Sakurai', 'Hitomi Nabatame', 'Kanade Sakurai'], ['yukino-sakurai', 'Yukino Sakurai', 'Ryouka Yuzuki', 'Yukino Sakurai']],
            'citrus' => [['mei-aihara', 'Mei Aihara', 'Minami Tsuda', 'Mei Aihara'], ['yuzu-aihara', 'Yuzu Aihara', 'Ayana Taketatsu', 'Yuzu Aihara']],
            'izetta-the-last-witch' => [['izetta', 'Izetta', 'Himika Akaneya', 'Izetta'], ['ortfine-fine', 'Ortfiné Fredericka', 'Saori Hayami', 'Fine']],
            'kannazuki-no-miko' => [['chikane-himemiya', 'Chikane Himemiya', 'Ayako Kawasumi', 'Chikane Himemiya'], ['himeko-kurusu', 'Himeko Kurusu', 'Noriko Shitaya', 'Himeko Kurusu']],
            'kimi-ga-shinu-made-koi-o-shitai' => [['sheena-totsuki', 'Sheena Totsuki', 'Rie Takahashi', 'Sheena Totsuki'], ['mimi-kagari', 'Mimi Kagari', 'Rina Hidaka', 'Mimi Kagari']],
            'maria-sama-ga-miteru' => [['yumi-fukuzawa', 'Yumi Fukuzawa', 'Kana Ueda', 'Yumi Fukuzawa'], ['sachiko-ogasawara', 'Sachiko Ogasawara', 'Miki Itou', 'Sachiko Ogasawara']],
            'maria-sama-ga-miteru-haru' => [['yumi-fukuzawa', 'Yumi Fukuzawa', 'Kana Ueda', 'Yumi Fukuzawa'], ['sachiko-ogasawara', 'Sachiko Ogasawara', 'Miki Itou', 'Sachiko Ogasawara']],
            'maria-sama-ga-miteru-3rd-season' => [['yumi-fukuzawa', 'Yumi Fukuzawa', 'Kana Ueda', 'Yumi Fukuzawa'], ['sachiko-ogasawara', 'Sachiko Ogasawara', 'Miki Itou', 'Sachiko Ogasawara']],
            'maria-sama-ga-miteru-4th-season' => [['yumi-fukuzawa', 'Yumi Fukuzawa', 'Kana Ueda', 'Yumi Fukuzawa'], ['sachiko-ogasawara', 'Sachiko Ogasawara', 'Miki Itou', 'Sachiko Ogasawara']],
            'revolutionary-girl-utena' => [['utena-tenjou', 'Utena Tenjou', 'Tomoko Kawakami', 'Utena Tenjou'], ['anthy-himemiya', 'Anthy Himemiya', 'Yuriko Fuchizaki', 'Anthy Himemiya']],
            'sakura-trick' => [['haruka-takiyama', 'Haruka Takayama', 'Haruka Tomatsu', 'Haruka Takayama'], ['yuu-sonoda', 'Yuu Sonoda', 'Yuka Iguchi', 'Yuu Sonoda']],
            'strawberry-panic' => [['nagisa-aoi', 'Nagisa Aoi', 'Mai Nakahara', 'Nagisa Aoi'], ['shizuma-hanazono', 'Shizuma Hanazono', 'Hitomi Nabatame', 'Shizuma Hanazono']],
            'tai-ari-deshita' => [['aya-mitsuki', 'Aya Mitsuki', 'Ikumi Hasegawa', 'Aya Mitsuki'], ['mio-yorue', 'Mio Yorue', 'Kana Ichinose', 'Mio Yorue']],
            'watashi-ga-koibito-ni-nareru-wake-nai-jan-muri-muri' => [['renako-amori', 'Renako Amaori', 'Kanna Nakamura', 'Renako Amaori'], ['mai-oudaka', 'Mai Ouzuka', 'Saori Oonishi', 'Mai Ouzuka']],
            'watashi-no-oshi-wa-akuyaku-reijou-me-enamore-de-la-villana' => [['rae-taylor', 'Rae Taylor', 'Yuu Serizawa', 'Rae Taylor'], ['claire-francois', 'Claire François', 'Karin Nanami', 'Claire François']],
            'yagate-kimi-ni-naru-bloom-into-you' => [['yuu-koito', 'Yuu Koito', 'Yuuki Takada', 'Yuu Koito'], ['touko-nanami', 'Touko Nanami', 'Minako Kotobuki', 'Touko Nanami']],
            'yami-to-boushi-to-hon-no-tabibito' => [['hazuki-azuma', 'Hazuki Azuma', 'Mamiko Noto', 'Hazuki Azuma'], ['hatsumi-azuma', 'Hatsumi Azuma', 'Ai Shimizu', 'Hatsumi Azuma']],
            'yuri-kuma-arashi' => [['ginko-yurishiro', 'Ginko Yurishiro', 'Miho Arakawa', 'Ginko Yurishiro'], ['lulu-yurigasaki', 'Lulu Yurigasaki', 'Yoshiko Ikuta', 'Lulu Yurigasaki']],
        ] as $seriesSlug => $artists) {
            $this->syncCast($seriesSlug, $artists, 'Japón');
        }

        $this->syncCast('chaser-game-w', [
            ['chiaki-kuriyama', 'Chiaki Kuriyama', 'Chiaki Kuriyama', 'Itsuki Nakamura'],
            ['yuka-masuda', 'Yuka Masuda', 'Yuka Masuda', 'Fuyu Hayashi'],
        ], 'Japón');
        $this->syncCast('girls-like-girls', [
            ['maya-da-costa', 'Maya da Costa', 'Maya da Costa', 'Evie'],
            ['myra-molloy', 'Myra Molloy', 'Myra Molloy', 'June'],
        ], 'Estados Unidos');
        $this->syncCast('ai-girl', [
            ['myyu-khawisara-singplod', 'Myyu Khawisara Singplod', 'Myyu Khawisara Singplod', 'Gen'],
            ['chanya-amarit-duval', 'Chanya Amarit Duval', 'Chanya Amarit Duval', 'Ai'],
        ], 'Tailandia');
        $this->syncCast('denied-love', [
            ['enjoy-thidarut-pruethong', 'Enjoy Thidarut', 'Thidarut Pruethong', 'Khemjira'],
            ['june-teeratee-buddeehong', 'June Teeratee', 'Teeratee Buddeehong', 'Praenarin'],
        ], 'Tailandia');
        $this->syncCast('frozen-valentine', [
            ['natty-natthamon-jantraviphart', 'Natty Natthamon', 'Natthamon Jantraviphart', 'Charm'],
            ['yeepun-purichaya-saranark', 'Yeepun Purichaya', 'Purichaya Saranark', 'Pingrak'],
        ], 'Tailandia');
        $this->syncCast('mate', [
            ['grace-budsarin-wonglelamont', 'Grace Budsarin', 'Budsarin Wonglelamont', 'Kenlong'],
            ['oaey-ponchanok-theerawan', 'Oaey Ponchanok', 'Ponchanok Theerawan', 'Oengoei'],
        ], 'Tailandia');
        $this->syncCast('my-only-sunshine', [
            ['mercedes-siripath-sarakune', 'Mercedes Siripath', 'Siripath Sarakune', 'Sun / Atita'],
            ['atom-aphichaya-kamnoetsirikun', 'Atom Aphichaya', 'Aphichaya Kamnoetsirikun', 'Rafah / Flint'],
        ], 'Tailandia');
        $this->syncCast('reverse-with-me', [
            ['natt-nattasha', 'Natt Nattasha', 'Natt Nattasha', 'Jattawa'],
            ['noon', 'Noon', 'Noon', 'Karan'],
        ], 'Tailandia');
        $this->syncCast('how-do-we-relationship', [
            ['riho-sayashi', 'Riho Sayashi', 'Riho Sayashi', 'Saeko'],
            ['sakura-konishi', 'Sakura Konishi', 'Sakura Konishi', 'Miwa'],
        ], 'Japón');
        $this->syncCast('queendom', [
            ['arhoung-nattawadee-pipobpornchai', 'Arhoung Nattawadee', 'Nattawadee Pipobpornchai', 'Rey'],
            ['pam-tarradee-watancharoen', 'Pam Tarradee', 'Tarradee Watancharoen', 'Print'],
        ], 'Tailandia');
        $this->syncCast('shades', [
            ['mook-worranit-thawornwong', 'Mook Worranit', 'Worranit Thawornwong', 'Nalin'],
            ['shu-nunnicha-saehuang', 'Shu Nunnicha', 'Nunnicha Saehuang', 'Belle'],
        ], 'Tailandia');
        $this->syncCast('shadow-of-love', [
            ['praifah-penpat-kairapee', 'Praifah Penpat', 'Penpat Kairapee', 'Pimchat'],
            ['bebell-patthanarat-ketkaew', 'Bebell Patthanarat', 'Patthanarat Ketkaew', 'Ranya'],
        ], 'Tailandia');
    }

    /** @param array<int, array{0: string, 1: string, 2: string, 3: string}> $artists */
    private function syncCast(string $seriesSlug, array $artists, string $country): void
    {
        $series = Series::query()->where('slug', $seriesSlug)->first();

        if (! $series) {
            return;
        }

        foreach ($artists as $position => [$slug, $character, $performer, $characterName]) {
            $artist = Artist::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $performer,
                    'aliases' => $character,
                    'country_of_origin' => $country,
                    'is_active' => true,
                ]
            );

            $series->artists()->syncWithoutDetaching([
                $artist->id => ['role' => 'cast', 'character_name' => $characterName, 'sort_order' => $position + 1],
            ]);
        }
    }

    private function catalogSummary(): string
    {
        $titles = Series::query()->count();
        $withoutCountry = Series::query()->whereNull('country_of_origin')->orWhere('country_of_origin', '')->count();
        $withoutYear = Series::query()->whereNull('release_year')->count();
        $withoutArtists = Series::query()->doesntHave('artists')->count();

        return "{$titles} títulos; {$withoutCountry} sin país; {$withoutYear} sin año; {$withoutArtists} sin artistas.";
    }
}
