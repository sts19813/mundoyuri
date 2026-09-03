<?php

namespace App\Http\Requests\Admin;

use App\Models\LegacyProfile;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreLegacyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage', LegacyProfile::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('nickname'))),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'legacy_external_key' => ['required', 'string', 'max:191', 'regex:/\A[A-Za-z0-9._:-]+\z/', Rule::unique(LegacyProfile::class)],
            'nickname' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', Rule::unique(LegacyProfile::class)],
            'legacy_joined_at' => ['nullable', 'date'],
            'legacy_rank' => ['nullable', 'string', 'max:120'],
            'legacy_message_count' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'legacy_location' => ['nullable', 'string', 'max:120'],
            'legacy_occupation' => ['nullable', 'string', 'max:160'],
            'legacy_interests' => ['nullable', 'string', 'max:2000'],
            'legacy_website' => ['nullable', 'url:http,https', 'max:2048'],
            'legacy_avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=800,max_height=800'],
            'legacy_avatar_url' => ['nullable', 'url:http,https', 'max:2048'],
            'source' => ['required', 'string', 'max:255'],
            'legacy_source_url' => ['nullable', 'url:http,https', 'max:2048'],
            'legacy_source_description' => ['nullable', 'string', 'max:5000'],
            'evidence' => ['nullable', 'string', 'max:5000'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'legacy_verified' => ['sometimes', 'boolean'],
            'is_published' => ['required', 'boolean'],
        ];
    }
}
