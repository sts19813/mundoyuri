<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'hero_video_url',
        'hero_primary_label',
        'hero_secondary_label',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
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
