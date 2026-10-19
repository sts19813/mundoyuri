<?php

namespace App\Services;

use App\Models\Sticker;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StickerService
{
    private const DIRECTORY = 'stickers';

    public function store(UploadedFile $file, User $owner, bool $isGeneral = false): Sticker
    {
        $path = $file->store(self::DIRECTORY, 'local');
        $original = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $extension = strtolower($file->getClientOriginalExtension());
        $baseName = Str::limit(pathinfo($original, PATHINFO_FILENAME), 100, '');

        return Sticker::query()->create([
            'owner_id' => $owner->id,
            'name' => ($baseName !== '' ? $baseName : 'sticker').'.'.$extension,
            'path' => $path,
            'mime' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => Storage::disk('local')->size($path),
            'is_general' => $isGeneral,
            'approved_at' => $isGeneral ? now() : null,
            'approved_by' => $isGeneral ? $owner->id : null,
        ]);
    }

    public function copyFromStoredFile(string $path, string $name, string $mime, User $owner): Sticker
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            throw new RuntimeException('El sticker ya no está disponible.');
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (! in_array($extension, ['png', 'webp', 'gif', 'jpg', 'jpeg'], true)) {
            $extension = match ($mime) {
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'image/jpeg' => 'jpg',
                default => 'png',
            };
        }

        $baseName = Str::limit(pathinfo($name, PATHINFO_FILENAME), 100, '');
        $destination = self::DIRECTORY.'/'.Str::uuid().'.'.$extension;

        if (! $disk->copy($path, $destination)) {
            throw new RuntimeException('No fue posible guardar el sticker.');
        }

        return Sticker::query()->create([
            'owner_id' => $owner->id,
            'name' => ($baseName !== '' ? $baseName : 'sticker').'.'.$extension,
            'path' => $destination,
            'mime' => $mime,
            'size' => $disk->size($destination),
        ]);
    }
}
