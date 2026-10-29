<?php

namespace Database\Seeders;

use App\Models\Series;
use App\Services\CatalogImageOptimizer;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LocalizeYesOrNoFilmImagesSeeder extends Seeder
{
    private const IMAGES = [
        'yes-or-no' => [
            'cover' => ['source' => 'https://asianwiki.com/images/2/21/Yes-or-no_poster01.jpg', 'target' => 'series/covers/yes-or-no.webp'],
            'banner' => ['source' => 'https://img.gagaoolala.com/media/86f8de00-248912-lg.jpg', 'target' => 'series/banners/yes-or-no.webp'],
        ],
        'yes-or-no-2-come-back-to-me' => [
            'cover' => ['source' => 'https://img.kapook.com/u/pailin/Movie%201/poster01.jpg', 'target' => 'series/covers/yes-or-no-2-come-back-to-me.webp'],
            'banner' => ['source' => 'https://www.filmdoo.com/blog/wp-content/uploads/2016/06/Yes-or-No-two1-e1467206986246.jpg', 'target' => 'series/banners/yes-or-no-2-come-back-to-me.webp'],
        ],
    ];

    public function run(CatalogImageOptimizer $optimizer): void
    {
        $disk = Storage::disk('public');

        foreach (self::IMAGES as $slug => $images) {
            $series = Series::query()->where('slug', $slug)->firstOrFail();
            foreach (['cover' => 'cover_image', 'banner' => 'banner_image'] as $kind => $field) {
                $image = $images[$kind];
                if ($series->{$field} === $image['target'] && $disk->exists($image['target'])) {
                    continue;
                }

                $temporaryPath = tempnam(sys_get_temp_dir(), 'yes-or-no-media-');
                if ($temporaryPath === false) {
                    throw new RuntimeException("No se pudo crear el archivo temporal de {$series->title}.");
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
                        throw new RuntimeException("No fue posible convertir la imagen de {$series->title} a WebP.");
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
        }

        $this->command?->info('Yes or No: portadas y banners localizados y optimizados a WebP.');
    }
}
