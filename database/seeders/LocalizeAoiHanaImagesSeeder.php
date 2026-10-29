<?php

namespace Database\Seeders;

use App\Models\Series;
use App\Services\CatalogImageOptimizer;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LocalizeAoiHanaImagesSeeder extends Seeder
{
    private const IMAGES = [
        'cover' => [
            'source' => 'https://image.tmdb.org/t/p/w780/r6HKAGL5MJH0QiaQIqrIc3vHqLD.jpg',
            'target' => 'series/covers/aoi-hana.webp',
        ],
        'banner' => [
            'source' => 'https://www.manga-news.com/public/images/dvd/banners/aoi-hana-anime-banner.jpg',
            'target' => 'series/banners/aoi-hana.webp',
        ],
    ];

    public function run(CatalogImageOptimizer $optimizer): void
    {
        $series = Series::query()->where('slug', 'aoi-hana')->firstOrFail();
        $disk = Storage::disk('public');

        foreach (['cover' => 'cover_image', 'banner' => 'banner_image'] as $kind => $field) {
            $image = self::IMAGES[$kind];

            if ($series->{$field} === $image['target'] && $disk->exists($image['target'])) {
                continue;
            }

            $temporaryPath = tempnam(sys_get_temp_dir(), 'aoi-hana-media-');
            if ($temporaryPath === false) {
                throw new RuntimeException('No se pudo crear el archivo temporal para las imágenes de Aoi Hana.');
            }

            try {
                $response = Http::timeout(30)->get($image['source'])->throw();
                $mime = (string) $response->header('Content-Type');
                if (! str_starts_with($mime, 'image/')) {
                    throw new RuntimeException("El recurso {$image['source']} no es una imagen válida.");
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
                    throw new RuntimeException('No fue posible convertir la imagen de Aoi Hana a WebP.');
                }

                $disk->delete($image['target']);
                $disk->move($optimizedPath, $image['target']);
                $series->forceFill([$field => $image['target']])->saveQuietly();
            } finally {
                if (is_file($temporaryPath)) {
                    unlink($temporaryPath);
                }
            }
        }

        $this->command?->info('Aoi Hana: imágenes optimizadas a WebP y alojadas localmente.');
    }
}
