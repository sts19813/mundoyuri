<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
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

            Series::query()->where('moderation_status', 'approved')->each(function (Series $series) use (&$urls): void {
                $urls[] = ['loc' => route('catalog.series.show', $series->slug), 'lastmod' => $series->updated_at?->toAtomString(), 'changefreq' => 'weekly', 'priority' => '0.8'];
            });

            Episode::query()->where('moderation_status', 'approved')->each(function (Episode $episode) use (&$urls): void {
                $urls[] = ['loc' => route('public.episodes.show', $episode->slug), 'lastmod' => $episode->updated_at?->toAtomString(), 'changefreq' => 'monthly', 'priority' => '0.6'];
            });
        }

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
