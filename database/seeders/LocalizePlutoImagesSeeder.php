<?php

namespace Database\Seeders;

use App\Models\Series;
use App\Services\CatalogImageOptimizer;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LocalizePlutoImagesSeeder extends Seeder
{
    private const IMAGES = [
        'cover' => [
            'source' => 'https://image.tmdb.org/t/p/original/apDkL8pCpNw5U7EziC0fJeSZVpH.jpg',
            'target' => 'series/covers/pluto.webp',
        ],
        'banner' => [
            'source' => 'https://i.ytimg.com/vi/cBq6p5lMxvg/maxresdefault.jpg',
            'target' => 'series/banners/pluto.webp',
        ],
    ];

    public function run(CatalogImageOptimizer $optimizer): void
    {
        $series = Series::query()->where('slug', 'pluto')->firstOrFail();
        $disk = Storage::disk('public');

        foreach (['cover' => 'cover_image', 'banner' => 'banner_image'] as $kind => $field) {
            $image = self::IMAGES[$kind];
            if ($series->{$field} === $image['target'] && $disk->exists($image['target'])) {
                continue;
            }

            $temporaryPath = tempnam(sys_get_temp_dir(), 'pluto-media-');
            if ($temporaryPath === false) {
                throw new RuntimeException('No se pudo crear el archivo temporal de Pluto.');
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
                    throw new RuntimeException('No fue posible convertir la imagen de Pluto a WebP.');
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

        $this->command?->info('Pluto: portada y banner localizados y optimizados a WebP.');
    }
}
