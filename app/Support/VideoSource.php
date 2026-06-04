<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VideoSource
{
    public static function normalizeProvider(string $provider): string
    {
        $provider = strtolower(trim($provider));
        $providerConfig = config('episode_sources.providers', []);

        return $providerConfig[$provider]['stores_as'] ?? $provider;
    }

    public static function normalizeUrl(string $provider, string $rawValue): ?string
    {
        $rawValue = trim($rawValue);

        if ($rawValue === '') {
            return null;
        }

        if ($provider === 'bunny_stream') {
            return self::normalizeBunnyUrl($rawValue);
        }

        $url = self::extractIframeSrc($rawValue) ?? $rawValue;
        $url = trim($url);

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        if ($provider === 'youtube') {
            return self::normalizeYouTubeEmbedUrl($url);
        }

        if ($provider === 'pixeldrain_cdn') {
            return self::normalizePixeldrainUrl($url);
        }

        return $url;
    }

    public static function playableUrl(?string $provider, string $videoUrl, mixed $source = null): string
    {
        if ($provider === 'pixeldrain_cdn' && $source) {
            return route('episode-sources.player', $source);
        }

        if ($provider === 'bunny_stream') {
            return self::signBunnyEmbedUrl($videoUrl);
        }

        return $videoUrl;
    }

    public static function playerType(?string $provider): string
    {
        return 'iframe';
    }

    public static function directVideoUrl(?string $provider, string $videoUrl): string
    {
        if ($provider !== 'pixeldrain_cdn') {
            return $videoUrl;
        }

        return self::normalizePixeldrainUrl($videoUrl) ?? $videoUrl;
    }

    public static function validateRemote(string $provider, string $normalizedUrl): ?string
    {
        if ($provider !== 'bunny_stream') {
            return null;
        }

        $videoId = self::extractBunnyVideoId($normalizedUrl);
        $libraryId = config('services.bunny.library_id');
        $apiKey = config('services.bunny.api_key');

        if (! $videoId || ! $libraryId || ! $apiKey) {
            return null;
        }

        $response = Http::acceptJson()
            ->withHeaders(['AccessKey' => $apiKey])
            ->get("https://video.bunnycdn.com/library/{$libraryId}/videos/{$videoId}");

        if ($response->successful()) {
            $status = (int) $response->json('status', -1);
            $isPublic = (bool) $response->json('isPublic', true);

            if (! $isPublic) {
                return config('services.bunny.token_key')
                    ? null
                    : 'El video de Bunny existe, pero no es público. Configura BUNNY_STREAM_TOKEN_KEY para reproducir embeds privados.';
            }

            if (in_array($status, [0, 1, 2, 3], true)) {
                return null;
            }

            return 'El video de Bunny aún no está listo para reproducirse.';
        }

        if ($response->status() === 404) {
            return 'No se encontró el video en Bunny con ese ID.';
        }

        if (in_array($response->status(), [401, 403], true)) {
            return 'No se pudo validar Bunny. Revisa BUNNY_STREAM_LIBRARY_ID y BUNNY_STREAM_API_KEY.';
        }

        return 'No se pudo validar el video en Bunny en este momento.';
    }

    public static function extractIframeSrc(string $rawValue): ?string
    {
        if (! str_contains(strtolower($rawValue), '<iframe')) {
            return null;
        }

        if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $rawValue, $matches) === 1) {
            return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }

    public static function normalizeYouTubeEmbedUrl(string $url): ?string
    {
        $parts = parse_url($url);

        if (! $parts || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $path = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';
        $queryParams = [];
        parse_str($query, $queryParams);

        $videoId = null;

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $videoId = trim($path, '/');
        }

        if (! $videoId && isset($queryParams['v'])) {
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

        if (! $videoId) {
            return null;
        }

        $videoId = preg_replace('/[^a-zA-Z0-9_-]/', '', $videoId);

        if (! $videoId) {
            return null;
        }

        $embedParams = [];
        $allowedParams = ['start', 'list', 'index', 'si', 'rel', 'autoplay'];

        foreach ($allowedParams as $param) {
            if (isset($queryParams[$param]) && $queryParams[$param] !== '') {
                $embedParams[$param] = $queryParams[$param];
            }
        }

        if (! isset($embedParams['start']) && isset($queryParams['t'])) {
            $seconds = self::parseYouTubeTimeToSeconds((string) $queryParams['t']);

            if ($seconds > 0) {
                $embedParams['start'] = $seconds;
            }
        }

        $embedUrl = 'https://www.youtube.com/embed/'.$videoId;

        if (! empty($embedParams)) {
            $embedUrl .= '?'.http_build_query($embedParams);
        }

        return $embedUrl;
    }

    public static function normalizePixeldrainUrl(string $url): ?string
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

    public static function normalizeBunnyUrl(string $rawValue): ?string
    {
        $value = trim(self::extractIframeSrc($rawValue) ?? $rawValue);
        $videoId = self::extractBunnyVideoId($value);

        if (! $videoId) {
            return null;
        }

        $libraryId = self::resolveBunnyLibraryId($value);

        if (! $libraryId) {
            return null;
        }

        return self::buildBunnyEmbedUrl($libraryId, $videoId);
    }

    public static function extractBunnyVideoId(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^[a-f0-9-]{32,36}$/i', $value) === 1) {
            return strtolower($value);
        }

        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($value);

        if (! $parts || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $path = trim($parts['path'] ?? '', '/');

        if (in_array($host, ['player.mediadelivery.net', 'iframe.mediadelivery.net'], true)
            && preg_match('~^embed/\d+/([a-f0-9-]{32,36})$~i', $path, $matches) === 1) {
            return strtolower($matches[1]);
        }

        if ($host === 'video.bunnycdn.com'
            && preg_match('~^play/\d+/([a-f0-9-]{32,36})$~i', $path, $matches) === 1) {
            return strtolower($matches[1]);
        }

        if (preg_match('~^([a-f0-9-]{32,36})/(?:playlist\.m3u8|play_\d+p\.mp4|original)$~i', $path, $matches) === 1) {
            return strtolower($matches[1]);
        }

        return null;
    }

    public static function buildBunnyEmbedUrl(string|int $libraryId, string $videoId): string
    {
        $host = trim((string) config('services.bunny.embed_host', 'player.mediadelivery.net'));
        $host = preg_replace('~^https?://~i', '', $host);

        return 'https://'.$host.'/embed/'.$libraryId.'/'.strtolower($videoId);
    }

    public static function signBunnyEmbedUrl(string $embedUrl): string
    {
        $tokenKey = trim((string) config('services.bunny.token_key', ''));

        if ($tokenKey === '') {
            return $embedUrl;
        }

        $videoId = self::extractBunnyVideoId($embedUrl);

        if (! $videoId) {
            return $embedUrl;
        }

        $ttlMinutes = max(1, (int) config('services.bunny.token_ttl', 120));
        $expires = now()->addMinutes($ttlMinutes)->timestamp;
        $token = hash('sha256', $tokenKey.$videoId.$expires);

        $separator = Str::contains($embedUrl, '?') ? '&' : '?';

        return $embedUrl.$separator.http_build_query([
            'token' => $token,
            'expires' => $expires,
        ]);
    }

    private static function resolveBunnyLibraryId(string $value): string|int|null
    {
        $configuredLibraryId = config('services.bunny.library_id');

        if (preg_match('/^[a-f0-9-]{32,36}$/i', $value) === 1) {
            return $configuredLibraryId;
        }

        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($value);
        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');

        if (in_array($host, ['player.mediadelivery.net', 'iframe.mediadelivery.net'], true)
            && preg_match('~^embed/(\d+)/[a-f0-9-]{32,36}$~i', $path, $matches) === 1) {
            return $matches[1];
        }

        if ($host === 'video.bunnycdn.com'
            && preg_match('~^play/(\d+)/[a-f0-9-]{32,36}$~i', $path, $matches) === 1) {
            return $matches[1];
        }

        return $configuredLibraryId;
    }

    private static function parseYouTubeTimeToSeconds(string $value): int
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
