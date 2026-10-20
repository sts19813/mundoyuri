<?php

namespace App\Http\Controllers;

use App\Models\Series;
use App\Services\BadgeAutomationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SeriesFavoriteController extends Controller
{
    public function store(Request $request, Series $series, BadgeAutomationService $badges): RedirectResponse
    {
        $this->ensurePublicSeries($series);
        $request->user()->favoriteSeries()->syncWithoutDetaching([$series->id]);
        $badges->sync($request->user());

        return back()->with('success', 'Serie agregada a tus favoritas.');
    }

    public function destroy(Request $request, Series $series, BadgeAutomationService $badges): RedirectResponse
    {
        $request->user()->favoriteSeries()->detach($series->id);
        $badges->sync($request->user());

        return back()->with('success', 'Serie eliminada de tus favoritas.');
    }

    private function ensurePublicSeries(Series $series): void
    {
        abort_unless(
            $series->moderation_status === 'approved' && $series->published_at !== null,
            404
        );
    }
}
