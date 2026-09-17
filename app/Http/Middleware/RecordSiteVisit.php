<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordSiteVisit
{
    private const COOKIE_NAME = 'mundo_yuri_visitor_id';

    private const COOKIE_MINUTES = 525600;

    private const DEDUPE_MINUTES = 30;

    public function handle(Request $request, Closure $next): Response
    {
        $shouldTrack = $this->isTrackableRequest($request);
        $visitorId = null;
        $queueAnonymousCookie = false;

        if ($shouldTrack) {
            if ($request->user()) {
                $visitorId = 'user:'.$request->user()->getAuthIdentifier();
            } else {
                $visitorId = $request->cookie(self::COOKIE_NAME);

                if (! is_string($visitorId) || ! Str::isUuid($visitorId)) {
                    $visitorId = (string) Str::uuid();
                    $queueAnonymousCookie = true;
                }
            }
        }

        $response = $next($request);

        if (! $shouldTrack || ! $visitorId || ! $this->isTrackableResponse($response)) {
            return $response;
        }

        if ($queueAnonymousCookie) {
            Cookie::queue(cookie(
                self::COOKIE_NAME,
                $visitorId,
                self::COOKIE_MINUTES,
                null,
                null,
                $request->isSecure(),
                true,
                false,
                'lax'
            ));
        }

        $this->recordVisit($request, $visitorId);

        return $response;
    }

    private function recordVisit(Request $request, string $visitorId): void
    {
        $path = $this->normalizedPath($request);
        $pathHash = sha1($path);
        $cacheKey = 'site_visit:'.sha1($visitorId.'|'.$pathHash);

        if (! Cache::add($cacheKey, true, now()->addMinutes(self::DEDUPE_MINUTES))) {
            return;
        }

        try {
            SiteVisit::query()->create([
                'user_id' => $request->user()?->getAuthIdentifier(),
                'visitor_id' => $visitorId,
                'visited_on' => now()->toDateString(),
                'visited_at' => now(),
                'path' => $path,
                'path_hash' => $pathHash,
            ]);
        } catch (Throwable) {
            Cache::forget($cacheKey);
        }
    }

    private function isTrackableRequest(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        if ($request->is('admin', 'admin/*', 'dashboard', 'up', 'sitemap.xml', 'player', 'player/*', 'api', 'api/*')) {
            return false;
        }

        $extension = strtolower(pathinfo($request->path(), PATHINFO_EXTENSION));

        return ! in_array($extension, [
            'avif',
            'css',
            'gif',
            'ico',
            'jpeg',
            'jpg',
            'js',
            'json',
            'map',
            'mp4',
            'png',
            'svg',
            'txt',
            'webm',
            'webp',
            'woff',
            'woff2',
            'xml',
        ], true);
    }

    private function isTrackableResponse(Response $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type');

        return ! $contentType || str_contains($contentType, 'text/html');
    }

    private function normalizedPath(Request $request): string
    {
        $path = trim($request->path(), '/');

        return $path === '' ? '/' : '/'.$path;
    }
}
