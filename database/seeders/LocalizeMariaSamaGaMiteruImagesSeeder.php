<?php

namespace Database\Seeders;

use App\Models\Series;
use App\Services\CatalogImageOptimizer;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LocalizeMariaSamaGaMiteruImagesSeeder extends Seeder
{
    /** @var array<string, array{cover: array{source: string, target: string}, banner: array{source: string, target: string}}> */
    private const IMAGES = [
        'maria-sama-ga-miteru' => [
            'cover' => [
                'source' => 'https://iv1.lisimg.com/image/332049/404full-maria-watches-over-us-poster.jpg',
                'target' => 'series/covers/maria-sama-ga-miteru.webp',
            ],
            'banner' => [
                'source' => 'https://www.d-rights.com/work/en/img/kv_MARIA_WATCHES_OVER_US.jpg',
                'target' => 'series/banners/maria-sama-ga-miteru.webp',
            ],
        ],
        'maria-sama-ga-miteru-haru' => [
            'cover' => [
                'source' => 'https://yurilibrary.info/sakuhin/7magyou/images7magyou/marimite_anime2.jpg',
                'target' => 'series/covers/maria-sama-ga-miteru-haru.webp',
            ],
            'banner' => [
                'source' => 'https://image-cf.kddi-video.com/144/1445c471848d0a6b6742e5335a4a0ec1/fit-background-transparent/1747267700/1080x608.png',
                'target' => 'series/banners/maria-sama-ga-miteru-haru.webp',
            ],
        ],
        'maria-sama-ga-miteru-3rd-season' => [
            'cover' => [
                'source' => 'https://image-cf.kddi-video.com/26c/26cb1c986af25243d3bad7e66209bea6/fit-background-white/1664218759/1080x608.jpg',
                'target' => 'series/covers/maria-sama-ga-miteru-3rd-season.webp',
            ],
            'banner' => [
                'source' => 'https://www.d-rights.com/work/en/img/kv_MARIA_WATCHES_OVER_US_3rd_season_OVA.jpg',
                'target' => 'series/banners/maria-sama-ga-miteru-3rd-season.webp',
            ],
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

                $temporaryPath = tempnam(sys_get_temp_dir(), 'maria-media-');
                if ($temporaryPath === false) {
                    throw new RuntimeException('No se pudo crear el archivo temporal para las imágenes de Maria-sama.');
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

                    $optimizedPath = $optimizer->store(
                        $uploaded,
                        'series/media-imports',
                        $kind === 'cover' ? 'cover' : 'banner',
                    );

                    if (! str_ends_with($optimizedPath, '.webp')) {
                        $disk->delete($optimizedPath);
                        throw new RuntimeException('No fue posible convertir la imagen a WebP.');
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

        $this->command?->info('Maria-sama ga Miteru: 6 imágenes optimizadas a WebP y alojadas localmente.');
    }
}
