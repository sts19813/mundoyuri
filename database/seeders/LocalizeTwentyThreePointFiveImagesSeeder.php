<?php

namespace Database\Seeders;

use App\Models\Series;
use App\Services\CatalogImageOptimizer;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LocalizeTwentyThreePointFiveImagesSeeder extends Seeder
{
    private const IMAGES = [
        'cover' => [
            'source' => 'https://images.justwatch.com/poster/318421961/s718/23-5.jpg',
            'target' => 'series/covers/23-5.webp',
        ],
        'banner' => [
            'source' => 'https://i.ytimg.com/vi/mdOlxH11WnQ/maxresdefault.jpg',
            'target' => 'series/banners/23-5.webp',
        ],
    ];

    public function run(CatalogImageOptimizer $optimizer): void
    {
        $series = Series::query()->where('slug', '23-5')->firstOrFail();
        $disk = Storage::disk('public');

        foreach (['cover' => 'cover_image', 'banner' => 'banner_image'] as $kind => $field) {
            $image = self::IMAGES[$kind];
            if ($series->{$field} === $image['target'] && $disk->exists($image['target'])) {
                continue;
            }

            $temporaryPath = tempnam(sys_get_temp_dir(), '23-5-media-');
            if ($temporaryPath === false) {
                throw new RuntimeException('No se pudo crear el archivo temporal de 23.5.');
            }

            try {
                $response = Http::timeout(30)->get($image['source'])->throw();
                $mime = (string) $response->header('Content-Type');
                if (! str_starts_with($mime, 'image/')) {
                    throw new RuntimeException("El recurso {$image['source']} no es una imagen válida.");
                }

                file_put_contents($temporaryPath, $response->body(), LOCK_EX);
                $uploaded = new UploadedFile($temporaryPath, basename((string) parse_url($image['source'], PHP_URL_PATH)), $mime, null, true);
                $optimizedPath = $optimizer->store($uploaded, 'series/media-imports', $kind);

                if (! str_ends_with($optimizedPath, '.webp')) {
                    $disk->delete($optimizedPath);
                    throw new RuntimeException('No fue posible convertir la imagen de 23.5 a WebP.');
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

        $this->command?->info('23.5: portada y banner localizados y optimizados a WebP.');
    }
}
