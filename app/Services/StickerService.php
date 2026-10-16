<?php

namespace App\Services;

use App\Models\Sticker;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
}
