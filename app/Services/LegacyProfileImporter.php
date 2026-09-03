<?php

namespace App\Services;

use App\Models\LegacyProfile;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LegacyProfileImporter
{
    /**
     * @param  array<string, string|null>  $row
     * @return array{attributes: array<string, mixed>, exists: bool}
     *
     * @throws ValidationException
     */
    public function prepare(array $row): array
    {
        $externalKey = $this->required($row, 'legacy_external_key');
        $nickname = $this->required($row, 'nickname');

        if (! preg_match('/\A[A-Za-z0-9._:-]+\z/', $externalKey)) {
            throw ValidationException::withMessages([
                'legacy_external_key' => 'Solo puede contener letras, números, punto, guion, guion bajo o dos puntos.',
            ]);
        }

        $existing = LegacyProfile::query()->where('legacy_external_key', $externalKey)->first();
        $website = $this->value($row, 'legacy_website');

        if ($website !== null && filter_var($website, FILTER_VALIDATE_URL) === false) {
            throw ValidationException::withMessages(['legacy_website' => 'Debe ser una URL válida.']);
        }

        if ($website !== null && ! in_array(parse_url($website, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw ValidationException::withMessages(['legacy_website' => 'Solo se permiten URLs HTTP o HTTPS.']);
        }

        $avatarPath = $this->value($row, 'legacy_avatar_path');

        if ($avatarPath !== null && (! str_starts_with($avatarPath, 'legacy-avatars/') || str_contains($avatarPath, '..'))) {
            throw ValidationException::withMessages([
                'legacy_avatar_path' => 'Debe ser una ruta local dentro de legacy-avatars/. No se descargan URLs remotas.',
            ]);
        }

        $attributes = [
            'legacy_external_key' => $externalKey,
            'slug' => $existing?->slug ?: $this->slugFor($nickname, $externalKey),
            'nickname' => $nickname,
            'legacy_joined_at' => $this->date($row, 'legacy_joined_at'),
            'legacy_rank' => $this->value($row, 'legacy_rank'),
            'legacy_message_count' => $this->unsignedInteger($row, 'legacy_message_count'),
            'legacy_location' => $this->value($row, 'legacy_location'),
            'legacy_occupation' => $this->value($row, 'legacy_occupation'),
            'legacy_interests' => $this->value($row, 'legacy_interests'),
            'legacy_website' => $website,
            'legacy_avatar_path' => $avatarPath,
            'source' => $this->value($row, 'source') ?: 'archivo-mundo-yuri',
            'evidence' => $this->value($row, 'evidence'),
            'admin_notes' => $this->value($row, 'admin_notes'),
            'is_published' => $this->boolean($row, 'is_published', true),
        ];

        return [
            'attributes' => $attributes,
            'exists' => $existing !== null,
        ];
    }

    /** @param array<string, mixed> $attributes */
    public function persist(array $attributes): LegacyProfile
    {
        return LegacyProfile::query()->updateOrCreate(
            ['legacy_external_key' => $attributes['legacy_external_key']],
            $attributes,
        );
    }

    /** @param array<string, string|null> $row */
    private function required(array $row, string $field): string
    {
        $value = $this->value($row, $field);

        if ($value === null) {
            throw ValidationException::withMessages([$field => 'Este campo es obligatorio.']);
        }

        return $value;
    }

    /** @param array<string, string|null> $row */
    private function value(array $row, string $field): ?string
    {
        $value = trim((string) ($row[$field] ?? ''));

        return $value === '' ? null : $value;
    }

    /** @param array<string, string|null> $row */
    private function date(array $row, string $field): ?Carbon
    {
        $value = $this->value($row, $field);

        if ($value === null) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            throw ValidationException::withMessages([$field => 'La fecha no es válida.']);
        }
    }

    /** @param array<string, string|null> $row */
    private function unsignedInteger(array $row, string $field): ?int
    {
        $value = $this->value($row, $field);

        if ($value === null) {
            return null;
        }

        if (! ctype_digit($value) || (int) $value > 4294967295) {
            throw ValidationException::withMessages([$field => 'Debe ser un entero sin signo válido.']);
        }

        return (int) $value;
    }

    /** @param array<string, string|null> $row */
    private function boolean(array $row, string $field, bool $default): bool
    {
        $value = $this->value($row, $field);

        if ($value === null) {
            return $default;
        }

        $boolean = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($boolean === null) {
            throw ValidationException::withMessages([$field => 'Debe ser true/false, 1/0, sí/no.']);
        }

        return $boolean;
    }

    private function slugFor(string $nickname, string $externalKey): string
    {
        $base = Str::slug($nickname) ?: 'miembro-historico';

        return Str::limit($base, 135, '').'-'.substr(sha1($externalKey), 0, 12);
    }
}
