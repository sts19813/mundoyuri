<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeriesMedia
{
    /**
     * @return array<int, string>
     */
    public static function validationRules(): array
    {
        return ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,mp4,webm', 'max:51200'];
    }

    public static function syncUploadedField(Request $request, string $field, ?string $existingPath = null): ?string
    {
        if (! $request->hasFile($field)) {
            return $existingPath;
        }

        self::deleteIfStored($existingPath);

        return $request->file($field)->store(self::directoryFor($field), 'public');
    }

    public static function deleteIfStored(?string $path): void
    {
        if (! $path || Str::isUrl($path) || Str::startsWith($path, ['/', 'storage/'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    public static function publicUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::isUrl($path) || Str::startsWith($path, '//')) {
            return $path;
        }

        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }

        if (Str::startsWith($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public static function detectType(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $parsedPath = parse_url($path, PHP_URL_PATH) ?: $path;
        $extension = Str::lower(pathinfo($parsedPath, PATHINFO_EXTENSION));

        if (in_array($extension, ['mp4', 'webm'], true)) {
            return 'video';
        }

        if ($extension === 'gif') {
            return 'gif';
        }

        return 'image';
    }

    private static function directoryFor(string $field): string
    {
        return match ($field) {
            'banner_image' => 'series/banners',
            'cover_image' => 'series/covers',
            default => 'series/media',
        };
    }
}
