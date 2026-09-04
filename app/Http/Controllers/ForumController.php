<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\ForumCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $categories = ForumCategory::query()
            ->active()
            ->with(['forums' => function ($query) use ($search): void {
                $query->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                    ->withCount(['threads as visible_threads_count' => fn ($query) => $query->where('is_hidden', false)])
                    ->with(['latestVisibleThread.latestVisiblePost.author']);
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn (ForumCategory $category) => $category->forums->isNotEmpty());

        return view('forums.index', compact('categories', 'search'));
    }

    public function show(Request $request, Forum $forum): View
    {
        $this->authorize('view', $forum);

        $search = trim((string) $request->query('q'));
        $threads = $forum->threads()
            ->where('is_hidden', false)
            ->with(['author.badges', 'author.communityRank', 'latestVisiblePost.author'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', '%'.$search.'%'))
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_post_at')
            ->paginate(20)
            ->withQueryString();

        return view('forums.show', compact('forum', 'threads', 'search'));
    }
}
