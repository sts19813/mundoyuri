<?php

namespace App\Support;

final class YouTubeVideoUrl
{
    public static function isValid(?string $url): bool
    {
        return self::videoId($url) !== null;
    }

    public static function embedUrl(?string $url): ?string
    {
        $videoId = self::videoId($url);

        return $videoId
            ? 'https://www.youtube-nocookie.com/embed/'.$videoId.'?autoplay=1&mute=1&controls=0&loop=1&playlist='.$videoId.'&modestbranding=1&playsinline=1&rel=0'
            : null;
    }

    private static function videoId(?string $url): ?string
    {
        if (! filled($url)) {
            return null;
        }

        $parts = parse_url(trim($url));
        if ($parts === false
            || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || isset($parts['user'])
            || isset($parts['pass'])) {
            return null;
        }

        $host = strtolower(rtrim($parts['host'] ?? '', '.'));
        $path = trim($parts['path'] ?? '', '/');
        $videoId = null;

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $videoId = explode('/', $path)[0] ?? null;
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'music.youtube.com'], true)) {
            if ($path === 'watch') {
                parse_str($parts['query'] ?? '', $query);
                $videoId = $query['v'] ?? null;
            } elseif (preg_match('#^(?:embed|shorts|live)/([^/]+)$#', $path, $matches)) {
                $videoId = $matches[1];
            }
        }

        return is_string($videoId) && preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)
            ? $videoId
            : null;
    }
}
