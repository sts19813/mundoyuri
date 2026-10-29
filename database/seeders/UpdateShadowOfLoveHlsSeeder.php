<?php

namespace Database\Seeders;

use App\Models\EpisodeSource;
use App\Models\Series;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateShadowOfLoveHlsSeeder extends Seeder
{
    /** @var array<int, string> */
    private const HLS_PATHS = [
        1 => 'Shadow%20Of%20Love%20Ep.1.HLS',
        2 => 'Shadow%20Of%20Love%20Ep.2.HLS',
        3 => 'Shadow%20Of%20Love%20Ep%203.HLS',
        4 => 'Shadow%20Of%20Love%20Ep.4.HLS',
        5 => 'Shadow%20Of%20Love%20-%20Ep.5.HLS',
        6 => 'Shadow%20Of%20Love%20Ep.6.HLS',
        7 => 'Shadow%20Of%20Love%20Ep.7.HLS',
        8 => 'Shadow%20Of%20Love%20Ep.8.HLS',
        9 => 'Shadow%20Of%20Love%20Ep.9.HLS',
        10 => 'Shadow%20Of%20Love%20-%20Ep.10.HLS',
        11 => 'Shadow%20Of%20Love%20-%20Ep.11.HLS',
        12 => 'Shadow%20Of%20Love%20Ep.12.HLS',
        13 => 'Shadow%20Of%20Love%20Ep.13.HLS',
        14 => 'Shadow%20Of%20Love%20Ep.14.HLS',
        15 => 'Shadow%20Of%20Love%20-%20Ep.15.HLS',
        16 => 'Shadow%20Of%20Love%20Ep.16.HLS',
        17 => 'Shadow%20Of%20Love%20-%20Ep.17.HLS',
        18 => 'Shadow%20Of%20Love%20Ep.18.HLS',
        19 => 'Shadow%20Of%20Love%20Ep.19.HLS',
        20 => 'Shadow%20Of%20Love%20Ep.20.HLS',
        21 => 'Shadow%20Of%20Love%20Ep.21.HLS',
        22 => 'Shadow%20Of%20Love%20Ep%2022.HLS',
        23 => 'Shadow%20Of%20Love%20Ep.23.HLS',
        24 => 'Shadow%20Of%20Love%20Ep%2024%20Final.HLS',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $series = Series::query()
                ->where('slug', 'shadow-of-love')
                ->with('episodes')
                ->firstOrFail();

            foreach ($series->episodes as $episode) {
                $path = self::HLS_PATHS[$episode->episode_number] ?? null;

                if ($path === null) {
                    continue;
                }

                EpisodeSource::query()->updateOrCreate(
                    ['episode_id' => $episode->id, 'sort_order' => 1],
                    [
                        'provider' => 'cloudflare_hls',
                        'source_type' => 'full',
                        'label' => 'Principal',
                        'video_url' => "https://video.mundoyuri.com/Shadow%20Of%20Love/{$path}/master.m3u8",
                        'is_primary' => true,
                    ]
                );
            }
        });

        $this->command?->info('Shadow of Love: 24 fuentes actualizadas a HLS de video.mundoyuri.com.');
    }
}
