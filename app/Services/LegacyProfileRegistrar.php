<?php

namespace App\Services;

use App\Models\LegacyProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LegacyProfileRegistrar
{
    /**
     * Registra una ficha archivada sin crear ni modificar una cuenta de usuario.
     *
     * @param  array<string, mixed>  $input
     */
    public function register(array $input, ?bool $verified = null, ?bool $published = null): LegacyProfile
    {
        $validated = $this->validate($input);
        $externalKey = $validated['legacy_external_key'] ?? $this->manualExternalKey($validated);
        $existing = LegacyProfile::query()->where('legacy_external_key', $externalKey)->first();

        return LegacyProfile::query()->updateOrCreate(
            ['legacy_external_key' => $externalKey],
            [
                'slug' => $existing?->slug ?: $this->slugFor($validated['legacy_username'], $externalKey),
                'nickname' => $validated['legacy_username'],
                'legacy_joined_at' => Carbon::parse($validated['legacy_joined_at']),
                'legacy_message_count' => $validated['legacy_message_count'] ?? null,
                'legacy_rank' => $validated['legacy_rank'] ?? null,
                'legacy_location' => $validated['legacy_location'] ?? null,
                'legacy_occupation' => $validated['legacy_occupation'] ?? null,
                'legacy_interests' => $validated['legacy_interests'] ?? null,
                'legacy_website' => $validated['legacy_website'] ?? null,
                'legacy_avatar_url' => $validated['legacy_avatar_url'] ?? null,
                'source' => $existing?->source ?: 'administrative-manual-entry',
                'legacy_source_url' => $validated['legacy_source_url'] ?? null,
                'legacy_source_description' => $validated['legacy_source_description'] ?? null,
                'is_legacy' => true,
                'legacy_verified' => $verified ?? $existing?->legacy_verified ?? false,
                'is_published' => $published ?? $existing?->is_published ?? false,
            ],
        );
    }

    /** @param array<string, mixed> $input */
    public function validate(array $input): array
    {
        $input = array_map(function (mixed $value): mixed {
            return is_string($value) && trim($value) === '' ? null : $value;
        }, $input);

        return Validator::make($input, [
            'legacy_external_key' => ['nullable', 'string', 'max:191', 'regex:/\A[A-Za-z0-9._:-]+\z/'],
            'legacy_username' => ['required', 'string', 'max:120'],
            'legacy_joined_at' => ['required', 'date_format:Y-m-d'],
            'legacy_message_count' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'legacy_rank' => ['nullable', 'string', 'max:120'],
            'legacy_location' => ['nullable', 'string', 'max:120'],
            'legacy_occupation' => ['nullable', 'string', 'max:160'],
            'legacy_interests' => ['nullable', 'string', 'max:2000'],
            'legacy_website' => ['nullable', 'url:http,https', 'max:2048'],
            'legacy_avatar_url' => ['nullable', 'url:http,https', 'max:2048'],
            'legacy_source_url' => ['nullable', 'url:http,https', 'max:2048'],
            'legacy_source_description' => ['nullable', 'string', 'max:5000'],
        ])->validate();
    }

    /** @param array<string, mixed> $validated */
    private function manualExternalKey(array $validated): string
    {
        return 'manual:'.hash('sha256', $validated['legacy_username'].'|'.$validated['legacy_joined_at']);
    }

    private function slugFor(string $username, string $externalKey): string
    {
        $base = Str::slug($username) ?: 'miembro-historico';

        return Str::limit($base, 135, '').'-'.substr(sha1($externalKey), 0, 12);
    }
}
