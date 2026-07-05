<?php

namespace App\Http\Controllers;

use App\Models\EpisodeSource;
use App\Services\BackblazeB2Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use RuntimeException;

class EpisodeSourcePlayerController extends Controller
{
    public function __invoke(EpisodeSource $source, BackblazeB2Service $backblaze): Response|View|RedirectResponse
    {
        abort_unless(in_array($source->provider, ['pixeldrain_cdn', 'backblaze_b2'], true), 404);

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

        return response()
            ->view('players.pixeldrain', compact('source'))
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }
}
