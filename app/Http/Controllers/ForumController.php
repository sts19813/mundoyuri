<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\ForumCategory;
use App\Services\CommunityReactionService;
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
                $query->active()
                    ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                    ->withCount(['threads as visible_threads_count' => fn ($query) => $query->where('type', 'discussion')->where('is_hidden', false)])
                    ->with(['latestVisibleThread.latestVisiblePost.author']);
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn (ForumCategory $category) => $category->forums->isNotEmpty());

        return view('forums.index', compact('categories', 'search'));
    }

    public function show(Request $request, Forum $forum, CommunityReactionService $reactions): View
    {
        $this->authorize('view', $forum);

        $search = trim((string) $request->query('q'));
        $threads = $forum->threads()
            ->where('type', 'discussion')
            ->where('is_hidden', false)
            ->with([
                'author',
                'initialPost.author.badges',
                'initialPost.author.communityRank',
                'initialPost.mentions.mentionedUser',
                'previewReplies' => fn ($query) => $query->latest('id')->limit(2)
                    ->with(['author.badges', 'author.communityRank', 'mentions.mentionedUser']),
            ])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', '%'.$search.'%'))
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_post_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $posts = collect();
        foreach ($threads as $thread) {
            $thread->setRelation('forum', $forum);
            $threadPosts = $thread->previewReplies->sortBy('id')->values();
            $thread->setRelation('previewReplies', $threadPosts);
            if ($thread->initialPost) {
                $threadPosts = collect([$thread->initialPost])->concat($threadPosts);
            }
            foreach ($threadPosts as $post) {
                $post->setRelation('thread', $thread);
                $posts->push($post);
            }
        }
        $reactions->hydrateSummaries($posts, $request->user());

        return view('forums.show', compact('forum', 'threads', 'search'));
    }
}
