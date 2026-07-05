<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view dashboard');
    }

    public function index(): View
    {
        $user = auth()->user();
        $canModerate = $user->can('moderate content');

        $stats = [
            'users' => $user->can('manage users') ? User::count() : null,
            'genres' => Genre::count(),
            'series' => $canModerate ? Series::count() : $user->submittedSeries()->count(),
            'episodes' => $canModerate ? Episode::count() : $user->submittedEpisodes()->count(),
            'pending_series' => $canModerate
                ? Series::where('moderation_status', 'pending')->count()
                : $user->submittedSeries()->where('moderation_status', 'pending')->count(),
            'pending_episodes' => $canModerate
                ? Episode::where('moderation_status', 'pending')->count()
                : $user->submittedEpisodes()->where('moderation_status', 'pending')->count(),
            'comments' => $user->can('manage users') ? Comment::count() : $user->comments()->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
