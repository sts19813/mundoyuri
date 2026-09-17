<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DirectMessageAttachmentService
{
    private const DIRECTORY = 'direct-message-attachments';
    private const MAX_IMAGE_EDGE = 1600;
    private const WEBP_QUALITY = 82;
    private const JPEG_QUALITY = 84;

    /**
     * @return array{attachment_path: string, attachment_name: string, attachment_mime: string|null, attachment_size: int|false}
     */
    public function store(UploadedFile $file): array
    {
        $originalName = Str::limit(basename(str_replace('\\', '/', $file->getClientOriginalName())), 255, '');
        $mime = $file->getMimeType();

        if ($this->shouldCompress($file, $mime)) {
            $compressed = $this->compressImage($file, $originalName);

            if ($compressed !== null) {
                return $compressed;
            }
        }

        $path = $file->store(self::DIRECTORY, 'local');

        return [
            'attachment_path' => $path,
            'attachment_name' => $originalName,
            'attachment_mime' => $mime,
            'attachment_size' => $file->getSize(),
        ];
    }

    private function shouldCompress(UploadedFile $file, ?string $mime): bool
    {
        return extension_loaded('gd')
            && $file->isValid()
            && str_starts_with((string) $mime, 'image/')
            && $mime !== 'image/gif';
    }

    /**
     * @return array{attachment_path: string, attachment_name: string, attachment_mime: string, attachment_size: int|false}|null
     */
    private function compressImage(UploadedFile $file, string $originalName): ?array
    {
        $source = $this->createImageResource($file);

        if (! $source) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width < 1 || $height < 1) {
            imagedestroy($source);

            return null;
        }

        $scale = min(1, self::MAX_IMAGE_EDGE / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, imagecolorallocatealpha($canvas, 255, 255, 255, 127));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $encoded = $this->encodeCompressedImage($canvas);

        imagedestroy($canvas);
        imagedestroy($source);

        if ($encoded === null) {
            return null;
        }

        [$temporaryPath, $extension, $mime] = $encoded;

        if (filesize($temporaryPath) >= $file->getSize()) {
            @unlink($temporaryPath);

            return null;
        }

        $path = self::DIRECTORY.'/'.Str::uuid().'.'.$extension;
        $stream = fopen($temporaryPath, 'rb');
        Storage::disk('local')->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
        @unlink($temporaryPath);

        return [
            'attachment_path' => $path,
            'attachment_name' => Str::limit(pathinfo($originalName, PATHINFO_FILENAME).'.'.$extension, 255, ''),
            'attachment_mime' => $mime,
            'attachment_size' => Storage::disk('local')->size($path),
        ];
    }

    /**
     * @return resource|\GdImage|false
     */
    private function createImageResource(UploadedFile $file): mixed
    {
        $path = $file->getRealPath();

        return match ($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    /**
     * @return array{string, string, string}|null
     */
    private function encodeCompressedImage(\GdImage $image): ?array
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'dm-attachment-');

        if ($temporaryPath === false) {
            return null;
        }

        if (function_exists('imagewebp') && imagewebp($image, $temporaryPath, self::WEBP_QUALITY)) {
            return [$temporaryPath, 'webp', 'image/webp'];
        }

        $white = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefilledrectangle($white, 0, 0, imagesx($image), imagesy($image), imagecolorallocate($white, 255, 255, 255));
        imagecopy($white, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        if (imagejpeg($white, $temporaryPath, self::JPEG_QUALITY)) {
            imagedestroy($white);

            return [$temporaryPath, 'jpg', 'image/jpeg'];
        }

        imagedestroy($white);
        @unlink($temporaryPath);

        return null;
    }
}
