<?php

namespace Database\Seeders;

use App\Models\Series;
use App\Services\CatalogImageOptimizer;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LocalizeExternalCatalogImagesSeeder extends Seeder
{
    /** @var array<string, array{cover: string, banner: string}> */
    private const SOURCES = [
        'izetta-the-last-witch' => [
            'cover' => 'https://image.tmdb.org/t/p/w500/nk7y1rsfH31EpY1x3z40f5CZuP8.jpg',
            'banner' => 'https://m.media-amazon.com/images/S/pv-target-images/73b38f05efcaceb2fa7140935a468f339139a491115da7f63a3a204e063107c8._BR-6_AC_SX720_FMpng_.png',
        ],
        'revolutionary-girl-utena' => [
            'cover' => 'https://s4.anilist.co/file/anilistcdn/media/anime/cover/medium/b440-r4l3N5WGgUOZ.jpg',
            'banner' => 'https://s4.anilist.co/file/anilistcdn/media/anime/banner/440-0Gzds5AlVDN6.jpg',
        ],
        'chaser-game-w' => [
            'cover' => 'https://image.tmdb.org/t/p/w500/450LkifqTnYQW4IZVNl26Vyzsha.jpg',
            'banner' => 'https://video-tvtokyo.imgix.net/chasergamew/_MASTER/image/chasergamew_pg_05.jpg',
        ],
    ];

    public function run(CatalogImageOptimizer $optimizer): void
    {
        $disk = Storage::disk('public');

        foreach (self::SOURCES as $slug => $sources) {
            $series = Series::query()->where('slug', $slug)->firstOrFail();

            foreach (['cover' => 'cover_image', 'banner' => 'banner_image'] as $kind => $field) {
                $target = "series/{$kind}s/{$slug}.webp";

                if ($series->{$field} === $target && $disk->exists($target)) {
                    continue;
                }

                $temporaryPath = tempnam(sys_get_temp_dir(), 'catalog-media-');
                if ($temporaryPath === false) {
                    throw new RuntimeException("No se pudo crear el archivo temporal para {$slug}.");
                }

                try {
                    $response = Http::timeout(30)->get($sources[$kind])->throw();
                    $mime = (string) $response->header('Content-Type');
                    if (! str_starts_with($mime, 'image/')) {
                        throw new RuntimeException("El recurso remoto de {$slug} no es una imagen válida.");
                    }

                    file_put_contents($temporaryPath, $response->body(), LOCK_EX);
                    $uploaded = new UploadedFile(
                        $temporaryPath,
                        $kind.'.'.(str_contains($mime, 'png') ? 'png' : 'jpg'),
                        $mime,
                        null,
                        true,
                    );
                    $optimizedPath = $optimizer->store(
                        $uploaded,
                        'series/media-imports',
                        $kind === 'cover' ? 'cover' : 'banner',
                    );

                    if (! str_ends_with($optimizedPath, '.webp')) {
                        $disk->delete($optimizedPath);
                        throw new RuntimeException("No fue posible optimizar la imagen de {$slug}.");
                    }

                    $disk->delete($target);
                    $disk->move($optimizedPath, $target);
                    $series->forceFill([$field => $target])->saveQuietly();
                } finally {
                    if (is_file($temporaryPath)) {
                        unlink($temporaryPath);
                    }
                }
            }
        }

        $this->command?->info('Imágenes externas restantes migradas a WebP local.');
    }
}
