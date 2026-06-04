<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodeSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'episode_id',
        'provider',
        'source_type',
        'label',
        'sort_order',
        'video_url',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function getPlayableUrlAttribute(): string
    {
        if ($this->provider === 'pixeldrain_cdn') {
            return route('episode-sources.player', $this);
        }

        if ($this->provider !== 'youtube') {
            return $this->video_url;
        }

        return $this->normalizeYouTubeEmbedUrl($this->video_url) ?? $this->video_url;
    }

    public function getPlayerTypeAttribute(): string
    {
        return 'iframe';
    }

    public function getDirectVideoUrlAttribute(): string
    {
        if ($this->provider !== 'pixeldrain_cdn') {
            return $this->video_url;
        }

        return $this->normalizePixeldrainUrl($this->video_url) ?? $this->video_url;
    }

    public function isPart(): bool
    {
        return $this->source_type === 'part';
    }

    private function normalizeYouTubeEmbedUrl(string $url): ?string
    {
        $parts = parse_url($url);

        if (! $parts || empty($parts['host'])) {
            return null;
        }

        $path = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';
        $queryParams = [];
        parse_str($query, $queryParams);

        $videoId = null;

        if (isset($queryParams['v'])) {
            $videoId = (string) $queryParams['v'];
        }

        if (! $videoId && preg_match('~/embed/([^/?&]+)~', $path, $matches) === 1) {
            $videoId = $matches[1];
        }

        if (! $videoId && preg_match('~/shorts/([^/?&]+)~', $path, $matches) === 1) {
            $videoId = $matches[1];
        }

        if (! $videoId && preg_match('~/v/([^/?&]+)~', $path, $matches) === 1) {
            $videoId = $matches[1];
        }

        if (! $videoId && preg_match('~/live/([^/?&]+)~', $path, $matches) === 1) {
            $videoId = $matches[1];
        }

        if (! $videoId && in_array(strtolower($parts['host']), ['youtu.be', 'www.youtu.be'], true)) {
            $videoId = trim($path, '/');
        }

        if (! $videoId) {
            return null;
        }

        $videoId = preg_replace('/[^a-zA-Z0-9_-]/', '', $videoId);

        if (! $videoId) {
            return null;
        }

        $embedParams = [];

        if (! empty($queryParams['start'])) {
            $embedParams['start'] = $queryParams['start'];
        } elseif (! empty($queryParams['t'])) {
            $seconds = $this->parseYouTubeTimeToSeconds((string) $queryParams['t']);

            if ($seconds > 0) {
                $embedParams['start'] = $seconds;
            }
        }

        if (! empty($queryParams['list'])) {
            $embedParams['list'] = $queryParams['list'];
        }

        $embedUrl = 'https://www.youtube.com/embed/'.$videoId;

        if (! empty($embedParams)) {
            $embedUrl .= '?'.http_build_query($embedParams);
        }

        return $embedUrl;
    }

    private function normalizePixeldrainUrl(string $url): ?string
    {
        $parts = parse_url($url);

        if (! $parts || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $path = trim($parts['path'] ?? '', '/');

        if (! str_contains($host, 'pixeldrain')) {
            return null;
        }

        $fileId = null;

        if (preg_match('~^api/file/([^/?#]+)$~', $path, $matches) === 1) {
            $fileId = $matches[1];
        } elseif (preg_match('~^u/([^/?#]+)$~', $path, $matches) === 1) {
            $fileId = $matches[1];
        } elseif (preg_match('~^([^/?#]+)$~', $path, $matches) === 1) {
            $fileId = $matches[1];
        }

        if (! $fileId) {
            return null;
        }

        $fileId = preg_replace('/[^a-zA-Z0-9_-]/', '', $fileId);

        if (! $fileId) {
            return null;
        }

        return 'https://pixeldrain.com/api/file/'.$fileId;
    }

    private function parseYouTubeTimeToSeconds(string $value): int
    {
        $value = strtolower(trim($value));

        if ($value === '') {
            return 0;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        $hours = 0;
        $minutes = 0;
        $seconds = 0;

        if (preg_match('/(\d+)h/', $value, $match) === 1) {
            $hours = (int) $match[1];
        }

        if (preg_match('/(\d+)m/', $value, $match) === 1) {
            $minutes = (int) $match[1];
        }

        if (preg_match('/(\d+)s/', $value, $match) === 1) {
            $seconds = (int) $match[1];
        }

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }
}
