<?php

namespace App\Support;

final class BackblazeB2Url
{
    /**
     * @return array{bucket: string, file: string}|null
     */
    public static function parse(string $url): ?array
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = ltrim($parts['path'] ?? '', '/');

        if ($host === '' || $path === '' || ! self::isBackblazeHost($host)) {
            return null;
        }

        if (preg_match('~^f\d+\.backblazeb2\.com$~', $host) === 1) {
            if (preg_match('~^file/([^/]+)/(.+)$~', $path, $matches) !== 1) {
                return null;
            }

            return self::result($matches[1], $matches[2]);
        }

        if (preg_match('~^([a-z0-9.-]+)\.s3\.[a-z0-9-]+\.backblazeb2\.com$~', $host, $matches) === 1) {
            return self::result($matches[1], $path);
        }

        if (preg_match('~^s3\.[a-z0-9-]+\.backblazeb2\.com$~', $host) === 1
            && preg_match('~^([^/]+)/(.+)$~', $path, $matches) === 1) {
            return self::result($matches[1], $matches[2]);
        }

        return null;
    }

    public static function normalize(string $url): ?string
    {
        $url = trim($url);
        $parsed = self::parse($url);

        if (! $parsed) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host']);

        return 'https://'.$host.'/'.ltrim($parts['path'], '/');
    }

    public static function canonicalUrl(string $bucket, string $file, ?string $downloadUrl = null): string
    {
        $baseUrl = rtrim($downloadUrl ?: 'https://f000.backblazeb2.com', '/');

        return $baseUrl.'/file/'.rawurlencode($bucket).'/'.self::encodePath($file);
    }

    private static function isBackblazeHost(string $host): bool
    {
        return $host === 'backblazeb2.com' || str_ends_with($host, '.backblazeb2.com');
    }

    /**
     * @return array{bucket: string, file: string}|null
     */
    private static function result(string $bucket, string $file): ?array
    {
        $bucket = urldecode(trim($bucket));
        $file = urldecode(ltrim($file, '/'));

        if ($bucket === '' || $file === '' || str_contains($file, "\0")) {
            return null;
        }

        return compact('bucket', 'file');
    }

    private static function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
