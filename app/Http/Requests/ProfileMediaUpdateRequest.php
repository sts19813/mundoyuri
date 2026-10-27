<?php

namespace App\Http\Requests;

use App\Support\YouTubeVideoUrl;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileMediaUpdateRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'avatar_remove' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:max_width=3840,max_height=2160'],
            'cover_remove' => ['nullable', 'boolean'],
            'cover_video_url' => ['nullable', 'string', 'max:2048', function (string $attribute, mixed $value, Closure $fail): void {
                if (filled($value) && ! YouTubeVideoUrl::isValid($value)) {
                    $fail('Ingresa un enlace válido de un video de YouTube.');
                }
            }],
        ];
    }
}
