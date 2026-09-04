<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForumModerationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin.panel']);
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->shouldEnterAdminPanel(), 403);

        return view('admin.forum-moderation.index', [
            'threads' => ForumThread::query()->with(['forum', 'author'])->latest()->paginate(20, ['*'], 'threads'),
            'posts' => ForumPost::query()->with(['thread', 'author'])->latest()->paginate(20, ['*'], 'posts'),
        ]);
    }
}
