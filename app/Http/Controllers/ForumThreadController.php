<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreForumThreadRequest;
use App\Http\Requests\UpdateForumThreadRequest;
use App\Models\Forum;
use App\Models\ForumThread;
use App\Services\ForumPostService;
use App\Services\ForumThreadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForumThreadController extends Controller
{
    public function create(Forum $forum): View
    {
        $this->authorize('createTopic', $forum);

        return view('forums.threads.create', compact('forum'));
    }

    public function store(StoreForumThreadRequest $request, Forum $forum, ForumThreadService $threads): RedirectResponse
    {
        $thread = $threads->create(
            $forum,
            $request->user(),
            $request->validated('title'),
            $request->validated('body'),
        );

        return redirect()->route('forum.threads.show', $thread)->with('success', 'Tema publicado correctamente.');
    }

    public function show(Request $request, ForumThread $thread): View|RedirectResponse
    {
        if ($thread->isQuestion()) {
            return redirect()->route('questions.show', $thread);
        }

        $thread->load('forum.category');
        $this->authorize('view', $thread);
        $thread->increment('views_count');

        $posts = $thread->posts()
            ->when(! $request->user()?->shouldEnterAdminPanel(), fn ($query) => $query->where('is_hidden', false))
            ->with(['author.badges', 'author.communityRank'])
            ->oldest()
            ->paginate(20)
            ->withQueryString();

        $isSubscribed = $request->user()
            ? $thread->subscribers()->whereKey($request->user()->id)->exists()
            : false;
        $moderationForums = $request->user()?->shouldEnterAdminPanel()
            ? Forum::query()->with('category')->orderBy('forum_category_id')->orderBy('sort_order')->get()
            : collect();

        return view('forums.threads.show', compact('thread', 'posts', 'isSubscribed', 'moderationForums'));
    }

    public function edit(ForumThread $thread): View
    {
        $thread->load('forum', 'posts');
        $this->authorize('update', $thread);

        return view('forums.threads.edit', compact('thread'));
    }

    public function update(UpdateForumThreadRequest $request, ForumThread $thread, ForumPostService $posts): RedirectResponse
    {
        $this->authorize('update', $thread);
        $initial = $thread->posts()->where('is_initial', true)->firstOrFail();
        $thread->update(['title' => $request->validated('title')]);
        $posts->update($initial, $request->validated('body'));

        return redirect()->route('forum.threads.show', $thread)->with('success', 'Tema actualizado correctamente.');
    }

    public function destroy(Request $request, ForumThread $thread, ForumPostService $posts): RedirectResponse
    {
        $this->authorize('delete', $thread);
        $initial = $thread->posts()->where('is_initial', true)->firstOrFail();
        $forum = $thread->forum;
        $posts->delete($initial);

        return redirect()->route('forums.show', $forum)->with('success', 'Tema eliminado correctamente.');
    }
}
