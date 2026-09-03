<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SafeSignatureImage implements ValidationRule
{
    private const MAX_WIDTH = 600;

    private const MAX_HEIGHT = 180;

    /**
     * @param  Closure(string): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('La imagen de firma no se pudo procesar.');

            return;
        }

        $image = @getimagesize($value->getRealPath());

        if ($image === false || ! in_array($image[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) {
            $fail('La imagen de firma debe ser una imagen JPG, PNG, WebP o GIF válida.');

            return;
        }

        if ($image[0] > self::MAX_WIDTH || $image[1] > self::MAX_HEIGHT) {
            $fail('La imagen de firma no puede superar '.self::MAX_WIDTH.' × '.self::MAX_HEIGHT.' píxeles.');
        }
    }
}
