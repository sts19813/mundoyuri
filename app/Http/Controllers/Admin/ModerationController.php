<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Series;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:moderate content']);
    }

    public function index(): View
    {
        $pendingSeries = Series::query()
            ->with(['genre', 'creator'])
            ->where('moderation_status', 'pending')
            ->latest()
            ->paginate(10, ['*'], 'series_page');

        $pendingEpisodes = Episode::query()
            ->with(['series', 'creator', 'sources'])
            ->where('moderation_status', 'pending')
            ->latest()
            ->paginate(10, ['*'], 'episodes_page');

        return view('admin.moderation.index', compact('pendingSeries', 'pendingEpisodes'));
    }

    public function approveSeries(Series $series): RedirectResponse
    {
        $series->update([
            'moderation_status' => 'approved',
            'approved_by' => auth()->id(),
            'published_at' => now(),
        ]);

        return back()->with('success', 'Serie aprobada.');
    }

    public function rejectSeries(Request $request, Series $series): RedirectResponse
    {
        $request->validate([
            'moderation_notes' => ['required', 'string', 'min:4'],
        ]);

        $series->update([
            'moderation_status' => 'rejected',
            'moderation_notes' => $request->string('moderation_notes'),
            'approved_by' => auth()->id(),
            'published_at' => null,
        ]);

        return back()->with('success', 'Serie rechazada.');
    }

    public function approveEpisode(Episode $episode): RedirectResponse
    {
        $episode->update([
            'moderation_status' => 'approved',
            'approved_by' => auth()->id(),
            'published_at' => now(),
        ]);

        return back()->with('success', 'Episodio aprobado.');
    }

    public function rejectEpisode(Request $request, Episode $episode): RedirectResponse
    {
        $request->validate([
            'moderation_notes' => ['required', 'string', 'min:4'],
        ]);

        $episode->update([
            'moderation_status' => 'rejected',
            'moderation_notes' => $request->string('moderation_notes'),
            'approved_by' => auth()->id(),
            'published_at' => null,
        ]);

        return back()->with('success', 'Episodio rechazado.');
    }
}
