<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('catalog.series.index'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => route('catalog.genres.index'), 'changefreq' => 'weekly', 'priority' => '0.7'],
            ['loc' => route('about'), 'changefreq' => 'monthly', 'priority' => '0.4'],
        ];

        if (Schema::hasTable('genres') && Schema::hasTable('series') && Schema::hasTable('episodes')) {
            Genre::query()->where('is_active', true)->each(function (Genre $genre) use (&$urls): void {
                $urls[] = ['loc' => route('catalog.genres.show', $genre->slug), 'lastmod' => $genre->updated_at?->toAtomString(), 'changefreq' => 'weekly', 'priority' => '0.7'];
            });

            Series::query()
                ->where('moderation_status', 'approved')
                ->whereNotNull('published_at')
                ->withMax([
                    'episodes as latest_episode_update' => fn ($query) => $query
                        ->where('moderation_status', 'approved')
                        ->whereNotNull('published_at'),
                ], 'updated_at')
                ->each(function (Series $series) use (&$urls): void {
                    $lastModified = collect([$series->updated_at, $series->latest_episode_update])
                        ->filter()
                        ->map(fn ($date) => Carbon::parse($date))
                        ->sortDesc()
                        ->first();
                    $images = collect([
                        $series->bannerMediaType() === 'image' ? $series->bannerMediaUrl() : null,
                        $series->coverMediaType() === 'image' ? $series->coverMediaUrl() : null,
                    ])->filter()->unique()->map(fn (string $url): array => [
                        'loc' => $url,
                        'title' => $series->title,
                        'caption' => Str::limit(Str::squish(strip_tags($series->description)), 180, ''),
                    ])->values()->all();

                    $urls[] = [
                        'loc' => route('catalog.series.show', $series->slug),
                        'lastmod' => $lastModified?->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                        'images' => $images,
                    ];
                });

            Episode::query()
                ->with('series')
                ->where('moderation_status', 'approved')
                ->whereNotNull('published_at')
                ->each(function (Episode $episode) use (&$urls): void {
                    $imageUrl = $episode->thumbnailUrl();

                    if (! $imageUrl && $episode->series?->bannerMediaType() === 'image') {
                        $imageUrl = $episode->series->bannerMediaUrl();
                    }

                    if (! $imageUrl && $episode->series?->coverMediaType() === 'image') {
                        $imageUrl = $episode->series->coverMediaUrl();
                    }

                    $seriesTitle = $episode->series?->title ?: 'Mundo Yuri';
                    $urls[] = [
                        'loc' => route('public.episodes.show', $episode->slug),
                        'lastmod' => $episode->updated_at?->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                        'images' => $imageUrl ? [[
                            'loc' => $imageUrl,
                            'title' => $seriesTitle.' episodio '.$episode->episode_number,
                            'caption' => $episode->seoMetaDescription(),
                        ]] : [],
                    ];
                });
        }

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
