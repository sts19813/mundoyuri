<?php

namespace App\Http\Controllers;

use App\Models\EpisodeSource;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class EpisodeSourcePlayerController extends Controller
{
    public function __invoke(EpisodeSource $source): Response|View
    {
        abort_unless($source->provider === 'pixeldrain_cdn', 404);

        return response()
            ->view('players.pixeldrain', compact('source'))
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }
}
