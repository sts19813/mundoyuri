<?php

namespace Database\Seeders;

use App\Models\Series;
use App\Services\CatalogImageOptimizer;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LocalizeTheLoyalPinImagesSeeder extends Seeder
{
    private const IMAGES = [
        'cover' => [
            'source' => 'https://static.wikia.nocookie.net/drama/images/e/ef/The_Loyal_Pin.jpg/revision/latest/scale-to-width-down/1200?cb=20240911095359',
            'target' => 'series/covers/the-loyal-pin.webp',
        ],
        'banner' => [
            'source' => 'https://s.yimg.com/ny/api/res/1.2/vrZSDtnJhZabGPyPYCVBDQ--/YXBwaWQ9aGlnaGxhbmRlcjt3PTEyNDI7aD02OTk7Y2Y9d2VicA--/https%3A/media.zenfs.com/en/comingsoon_net_477/df66a575fe8aa73be89db3a0fc95a554',
            'target' => 'series/banners/the-loyal-pin.webp',
        ],
    ];

    public function run(CatalogImageOptimizer $optimizer): void
    {
        $series = Series::query()->where('slug', 'the-loyal-pin')->firstOrFail();
        $disk = Storage::disk('public');

        foreach (['cover' => 'cover_image', 'banner' => 'banner_image'] as $kind => $field) {
            $image = self::IMAGES[$kind];
            if ($series->{$field} === $image['target'] && $disk->exists($image['target'])) {
                continue;
            }

            $temporaryPath = tempnam(sys_get_temp_dir(), 'the-loyal-pin-media-');
            if ($temporaryPath === false) {
                throw new RuntimeException('No se pudo crear el archivo temporal de The Loyal Pin.');
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
                    throw new RuntimeException('No fue posible convertir la imagen de The Loyal Pin a WebP.');
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

        $this->command?->info('The Loyal Pin: portada y banner localizados y optimizados a WebP.');
    }
}
