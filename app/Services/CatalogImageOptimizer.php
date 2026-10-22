<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CatalogImageOptimizer
{
    private const MAX_SOURCE_PIXELS = 50_000_000;

    /** @var array<string, array{width: int, height: int, bytes: int}> */
    private const PROFILES = [
        'cover' => ['width' => 720, 'height' => 1080, 'bytes' => 280 * 1024],
        'banner' => ['width' => 1600, 'height' => 900, 'bytes' => 480 * 1024],
        'hero-desktop' => ['width' => 1920, 'height' => 1080, 'bytes' => 650 * 1024],
        'hero-mobile' => ['width' => 1080, 'height' => 1440, 'bytes' => 420 * 1024],
        'thumbnail' => ['width' => 960, 'height' => 540, 'bytes' => 180 * 1024],
    ];

    public function store(UploadedFile $file, string $directory, string $profile): string
    {
        $optimized = $this->encode($file->getRealPath(), $profile);

        if ($optimized === null) {
            return $file->store($directory, 'public');
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $optimized['contents']);

        return $path;
    }

    /**
     * @return array{path: string|null, source_bytes: int, optimized_bytes: int}|null
     */
    public function optimizeStored(string $path, string $profile, bool $write = true): ?array
    {
        if (Str::isUrl($path) || Str::startsWith($path, ['/', 'storage/'])) {
            return null;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            return null;
        }

        $sourcePath = $disk->path($path);
        $optimized = $this->encode($sourcePath, $profile);
        if ($optimized === null) {
            return null;
        }

        $sourceBytes = filesize($sourcePath) ?: 0;
        $optimizedBytes = strlen($optimized['contents']);
        if ($optimizedBytes >= $sourceBytes) {
            return null;
        }

        $targetPath = null;
        if ($write) {
            $targetPath = trim(dirname($path), '/').'/'.Str::uuid().'.webp';
            $disk->put($targetPath, $optimized['contents']);
        }

        return [
            'path' => $targetPath,
            'source_bytes' => $sourceBytes,
            'optimized_bytes' => $optimizedBytes,
        ];
    }

    /** @return array{contents: string}|null */
    private function encode(string $sourcePath, string $profile): ?array
    {
        $profileSettings = self::PROFILES[$profile] ?? null;
        if (! $profileSettings || ! function_exists('imagewebp')) {
            return null;
        }

        $dimensions = @getimagesize($sourcePath);
        if (! $dimensions || $dimensions[0] < 1 || $dimensions[1] < 1) {
            throw ValidationException::withMessages(['image' => 'No se pudo leer la imagen.']);
        }

        if ($dimensions[0] * $dimensions[1] > self::MAX_SOURCE_PIXELS) {
            throw ValidationException::withMessages(['image' => 'La imagen supera el límite seguro de 50 megapíxeles.']);
        }

        $source = $this->openImage($sourcePath, $dimensions['mime'] ?? null);
        if ($source === null) {
            return null;
        }

        $source = $this->orient($source, $sourcePath, $dimensions['mime'] ?? null);
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $profileSettings['width'] / $width, $profileSettings['height'] / $height);
        $quality = 82;
        $contents = null;

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $target = $this->resize(
                $source,
                max(1, (int) round($width * $scale)),
                max(1, (int) round($height * $scale)),
            );
            $contents = $this->encodeWebp($target, $quality);
            imagedestroy($target);

            if (strlen($contents) <= $profileSettings['bytes']) {
                break;
            }

            if ($quality > 54) {
                $quality -= 7;
            } else {
                $scale *= .84;
            }
        }

        imagedestroy($source);

        return $contents === null ? null : ['contents' => $contents];
    }

    private function openImage(string $path, ?string $mime): ?\GdImage
    {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path) ?: null,
            'image/png' => imagecreatefrompng($path) ?: null,
            'image/webp' => imagecreatefromwebp($path) ?: null,
            // Animated GIFs are left untouched so their animation is never lost.
            default => null,
        };
    }

    private function orient(\GdImage $image, string $path, ?string $mime): \GdImage
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = @exif_read_data($path)['Orientation'] ?? 1;
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        imagedestroy($image);

        return $rotated;
    }

    private function resize(\GdImage $source, int $width, int $height): \GdImage
    {
        $target = imagecreatetruecolor($width, $height);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefilledrectangle($target, 0, 0, $width, $height, imagecolorallocatealpha($target, 0, 0, 0, 127));
        imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

        return $target;
    }

    private function encodeWebp(\GdImage $image, int $quality): string
    {
        ob_start();
        imagewebp($image, null, $quality);

        return (string) ob_get_clean();
    }
}
