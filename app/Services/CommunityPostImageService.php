<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CommunityPostImageService
{
    private const MAX_BYTES = 800 * 1024;

    private const MAX_EDGE = 1920;

    private const MAX_SOURCE_PIXELS = 50_000_000;

    public function store(UploadedFile $file): string
    {
        $dimensions = @getimagesize($file->getRealPath());
        if (! $dimensions || $dimensions[0] < 1 || $dimensions[1] < 1) {
            throw ValidationException::withMessages(['image' => 'No se pudo leer la imagen.']);
        }
        if ($dimensions[0] * $dimensions[1] > self::MAX_SOURCE_PIXELS) {
            throw ValidationException::withMessages(['image' => 'La imagen supera el límite seguro de 50 megapíxeles.']);
        }

        $source = $this->openImage($file);
        [$width, $height] = [imagesx($source), imagesy($source)];
        $source = $this->orient($source, $file);
        [$width, $height] = [imagesx($source), imagesy($source)];

        $scale = min(1, self::MAX_EDGE / max($width, $height));
        $quality = 82;
        $encoded = null;

        for ($attempt = 0; $attempt < 8; $attempt++) {
            $target = $this->resize($source, max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale)));
            $encoded = $this->encodeWebp($target, $quality);
            imagedestroy($target);

            if (strlen($encoded) <= self::MAX_BYTES) {
                break;
            }

            if ($quality > 52) {
                $quality -= 10;
            } else {
                $scale *= .72;
            }
        }

        imagedestroy($source);

        if (! $encoded || strlen($encoded) > self::MAX_BYTES) {
            throw ValidationException::withMessages(['image' => 'No fue posible optimizar la imagen. Prueba con otra más pequeña.']);
        }

        $path = 'community-post-images/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }

    private function openImage(UploadedFile $file): \GdImage
    {
        return match ($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            default => throw ValidationException::withMessages(['image' => 'El archivo no es una imagen compatible.']),
        } ?: throw ValidationException::withMessages(['image' => 'No se pudo leer la imagen.']);
    }

    private function orient(\GdImage $image, UploadedFile $file): \GdImage
    {
        if ($file->getMimeType() !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;
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
        if (! function_exists('imagewebp')) {
            throw ValidationException::withMessages(['image' => 'El servidor no puede optimizar imágenes WebP en este momento.']);
        }

        ob_start();
        imagewebp($image, null, $quality);

        return (string) ob_get_clean();
    }
}
