<?php

namespace App\Http\Controllers;

use App\Models\EpisodeSource;
use App\Services\BackblazeB2Service;
use App\Support\VideoSource;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EpisodeSourcePlayerController extends Controller
{
    public function __invoke(EpisodeSource $source, BackblazeB2Service $backblaze, Request $request): Response|View|RedirectResponse
    {
        abort_unless(in_array($source->provider, ['pixeldrain_cdn', 'backblaze_b2', 'cloudflare_hls'], true), 404);

        if ($source->provider === 'backblaze_b2') {
            try {
                $url = $backblaze->temporaryDownloadUrl($source->video_url);
            } catch (RuntimeException $exception) {
                report($exception);

                abort(503, 'No se pudo preparar el video de Backblaze B2 en este momento.');
            }

            return redirect()->away($url, 302, [
                'Cache-Control' => 'private, no-store, max-age=0',
                'Referrer-Policy' => 'no-referrer',
            ]);
        }

        if ($source->provider === 'cloudflare_hls') {
            return $this->cloudflareHlsPlaylist($source, $request);
        }

        return response()
            ->view('players.pixeldrain', compact('source'))
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    private function cloudflareHlsPlaylist(EpisodeSource $source, Request $request): Response
    {
        $playlistUrl = VideoSource::normalizeCloudflareHlsUrl((string) $request->query('url', $source->video_url));

        abort_unless($playlistUrl, 404);

        $origin = $this->trustedMundoYuriOrigin($request);
        $remoteResponse = Http::timeout(15)
            ->withHeaders([
                'Accept' => 'application/vnd.apple.mpegurl, application/x-mpegURL, */*',
                'Origin' => $origin,
                'Referer' => $origin.'/',
                'User-Agent' => 'MundoYuriPlayer/1.0',
            ])
            ->get($playlistUrl);

        abort_unless($remoteResponse->successful(), $remoteResponse->status());

        $cacheBuster = (string) $request->query('v', bin2hex(random_bytes(8)));
        $playlist = $this->rewriteHlsPlaylist($remoteResponse->body(), $playlistUrl, $source, $cacheBuster);

        return response($playlist, 200, [
            'Content-Type' => 'application/vnd.apple.mpegurl; charset=UTF-8',
            'Cache-Control' => 'private, no-store, no-cache, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'Access-Control-Allow-Origin' => $origin,
            'Vary' => 'Origin, Referer',
        ]);
    }

    private function rewriteHlsPlaylist(string $playlist, string $playlistUrl, EpisodeSource $source, string $cacheBuster): string
    {
        return collect(preg_split("/(\r\n|\n|\r)/", $playlist))
            ->map(function (string $line) use ($playlistUrl, $source, $cacheBuster): string {
                $trimmedLine = trim($line);

                if ($trimmedLine === '') {
                    return $line;
                }

                if (str_starts_with($trimmedLine, '#')) {
                    return preg_replace_callback('/URI="([^"]+)"/', function (array $matches) use ($playlistUrl, $source, $cacheBuster): string {
                        return 'URI="'.$this->rewriteHlsUri($matches[1], $playlistUrl, $source, $cacheBuster).'"';
                    }, $line) ?? $line;
                }

                return $this->rewriteHlsUri($trimmedLine, $playlistUrl, $source, $cacheBuster);
            })
            ->implode("\n");
    }

    private function rewriteHlsUri(string $uri, string $playlistUrl, EpisodeSource $source, string $cacheBuster): string
    {
        if (preg_match('/^(?:data|blob|about):/i', $uri) === 1) {
            return $uri;
        }

        $absoluteUrl = (string) UriResolver::resolve(new Uri($playlistUrl), new Uri($uri));
        $path = parse_url($absoluteUrl, PHP_URL_PATH) ?: '';

        if (str_ends_with(strtolower($path), '.m3u8')) {
            return route('episode-sources.player', [
                'source' => $source,
                'url' => $absoluteUrl,
                'v' => $cacheBuster,
            ], false);
        }

        return $this->appendQueryParameter($absoluteUrl, '_my_hls_session', $cacheBuster);
    }

    private function appendQueryParameter(string $url, string $key, string $value): string
    {
        $fragment = '';

        if (str_contains($url, '#')) {
            [$url, $fragment] = explode('#', $url, 2);
            $fragment = '#'.$fragment;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.rawurlencode($key).'='.rawurlencode($value).$fragment;
    }

    private function trustedMundoYuriOrigin(Request $request): string
    {
        $host = strtolower($request->getHost());

        if (($host === 'mundoyuri.com' || $host === 'www.mundoyuri.com') && $request->isSecure()) {
            return $request->getSchemeAndHttpHost();
        }

        return 'https://mundoyuri.com';
    }
}
