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
        $this->middleware('admin');
    }

    public function index(): View
    {
        $stats = [
            'users' => User::count(),
            'genres' => Genre::count(),
            'series' => Series::count(),
            'episodes' => Episode::count(),
            'pending_series' => Series::where('moderation_status', 'pending')->count(),
            'pending_episodes' => Episode::where('moderation_status', 'pending')->count(),
            'comments' => Comment::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
