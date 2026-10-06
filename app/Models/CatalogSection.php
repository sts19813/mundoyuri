<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'label',
        'hero_eyebrow',
        'hero_title',
        'hero_description',
        'hero_image_url',
        'hero_desktop_image',
        'hero_mobile_image',
        'hero_video_url',
        'hero_video_on_mobile',
        'hero_primary_enabled',
        'hero_primary_label',
        'hero_primary_url',
        'hero_secondary_enabled',
        'hero_secondary_label',
        'hero_secondary_url',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'hero_video_on_mobile' => 'boolean',
            'hero_primary_enabled' => 'boolean',
            'hero_secondary_enabled' => 'boolean',
        ];
    }

    public function heroDesktopImageUrl(): ?string
    {
        return $this->heroImageUrl($this->hero_desktop_image) ?? $this->hero_image_url;
    }

    public function heroMobileImageUrl(): ?string
    {
        return $this->heroImageUrl($this->hero_mobile_image) ?? $this->heroDesktopImageUrl();
    }

    private function heroImageUrl(?string $path): ?string
    {
        return filled($path) ? Storage::disk('public')->url($path) : null;
    }

    public function heroButtonUrl(?string $url, string $fallback): string
    {
        $url = trim((string) $url);

        $isRelative = Str::startsWith($url, ['/', '#']) && ! Str::startsWith($url, '//');

        if ($url === '' || (! $isRelative && ! filter_var($url, FILTER_VALIDATE_URL))) {
            return $fallback;
        }

        $scheme = Str::lower((string) parse_url($url, PHP_URL_SCHEME));

        return $scheme === '' || in_array($scheme, ['http', 'https'], true) ? $url : $fallback;
    }

    public function heroVideoId(): ?string
    {
        $url = $this->hero_video_url;

        if (! $url) {
            return null;
        }

        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($host === 'youtu.be') {
            return Str::before($path, '/');
        }

        if (Str::contains($host, 'youtube.com')) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

            return $query['v'] ?? (Str::startsWith($path, 'embed/') ? Str::after($path, 'embed/') : null);
        }

        return null;
    }

    public function heroVideoEmbedUrl(): ?string
    {
        $videoId = $this->heroVideoId();

        if (! $videoId) {
            return null;
        }

        return 'https://www.youtube-nocookie.com/embed/'.rawurlencode($videoId)
            .'?autoplay=1&mute=1&loop=1&playlist='.rawurlencode($videoId)
            .'&controls=0&playsinline=1&rel=0&disablekb=1&fs=0&iv_load_policy=3&modestbranding=1';
    }

    public function hasDirectVideo(): bool
    {
        return filled($this->hero_video_url) && ! $this->heroVideoId();
    }
}
