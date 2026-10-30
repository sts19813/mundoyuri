<?php

namespace Database\Seeders;

use App\Models\Series;
use App\Services\CatalogImageOptimizer;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LocalizePoisonousLoveImagesSeeder extends Seeder
{
    private const IMAGES = [
        'cover' => [
            'source' => 'https://image.tmdb.org/t/p/original/oTzt4Fe1IIwbn0ocJWPqkKk4CDu.jpg',
            'target' => 'series/covers/poisonous-love.webp',
        ],
        'banner' => [
            'source' => 'https://image.tmdb.org/t/p/original/fcPjN4ShY98g52jyQEQRE6EQahO.jpg',
            'target' => 'series/banners/poisonous-love.webp',
        ],
    ];

    public function run(CatalogImageOptimizer $optimizer): void
    {
        $series = Series::query()->where('slug', 'poisonous-love')->firstOrFail();
        $disk = Storage::disk('public');

        foreach (['cover' => 'cover_image', 'banner' => 'banner_image'] as $kind => $field) {
            $image = self::IMAGES[$kind];
            if ($series->{$field} === $image['target'] && $disk->exists($image['target'])) {
                continue;
            }

            $temporaryPath = tempnam(sys_get_temp_dir(), 'poisonous-love-media-');
            if ($temporaryPath === false) {
                throw new RuntimeException('No se pudo crear el archivo temporal de Poisonous Love.');
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
                    basename((string) parse_url($image['source'], PHP_URL_PATH)),
                    $mime,
                    null,
                    true,
                );
                $optimizedPath = $optimizer->store($uploaded, 'series/media-imports', $kind);

                if (! str_ends_with($optimizedPath, '.webp')) {
                    $disk->delete($optimizedPath);
                    throw new RuntimeException('No fue posible convertir la imagen de Poisonous Love a WebP.');
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

        $this->command?->info('Poisonous Love: portada y banner localizados y optimizados a WebP.');
    }
}
