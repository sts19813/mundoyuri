<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreForumThreadRequest;
use App\Http\Requests\UpdateForumThreadRequest;
use App\Models\Forum;
use App\Models\ForumThread;
use App\Services\CommunityPostImageService;
use App\Services\CommunityReactionService;
use App\Services\ForumConversationTree;
use App\Services\ForumPostService;
use App\Services\ForumThreadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class ForumThreadController extends Controller
{
    public function create(Forum $forum): View
    {
        $this->authorize('createTopic', $forum);

        return view('forums.threads.create', compact('forum'));
    }

    public function store(StoreForumThreadRequest $request, Forum $forum, ForumThreadService $threads, CommunityPostImageService $images): RedirectResponse
    {
        $thread = $threads->create(
            $forum,
            $request->user(),
            $request->validated('title'),
            $request->validated('body') ?? '',
            'discussion',
            $request->hasFile('image') ? $images->store($request->file('image')) : null,
        );

        if ($request->boolean('from_feed')) {
            return redirect()->to(route('forums.show', $forum).'#thread-'.$thread->id)
                ->with('success', 'Tema publicado correctamente.');
        }

        return redirect()->route('forum.threads.show', $thread)->with('success', 'Tema publicado correctamente.');
    }

    public function show(Request $request, ForumThread $thread, CommunityReactionService $reactions, ForumConversationTree $tree): View|RedirectResponse|JsonResponse
    {
        if ($thread->isQuestion()) {
            return redirect()->route('questions.show', $thread);
        }

        $thread->load('forum.category');
        $this->authorize('view', $thread);
        if (! $request->expectsJson()) {
            $thread->increment('views_count');
        }

        $postsQuery = $thread->posts()
            ->when($request->expectsJson(), fn ($query) => $query->where('is_initial', false))
            ->when(! $request->user()?->shouldEnterAdminPanel(), fn ($query) => $query->where('is_hidden', false))
            ->with(['author.badges', 'author.communityRank', 'mentions.mentionedUser', 'replyTo.author'])
            ->oldest();

        if ($request->expectsJson()) {
            $posts = $postsQuery->paginate(20)->withQueryString();
            $reactions->hydrateSummaries($posts->getCollection(), $request->user());
            $posts->each(fn ($post) => $post->setRelation('thread', $thread));

            return response()->json([
                'html' => $posts->map(fn ($post) => view('components.forum.post', ['post' => $post])->render())->implode(''),
                'next_page_url' => $posts->nextPageUrl(),
            ]);
        }

        $focusedPostId = $request->has('post') ? $request->integer('post') : null;
        abort_if($request->has('post') && ! $focusedPostId, 404);
        $showAllReplies = ! $focusedPostId && $request->boolean('all');
        $posts = $focusedPostId
            ? $postsQuery->whereIn('id', $tree->focusedPostIds($thread, $focusedPostId, $request->user()?->shouldEnterAdminPanel() ?? false))->get()
            : ($showAllReplies ? $postsQuery->get() : $postsQuery->limit(101)->get());

        $reactions->hydrateSummaries($posts, $request->user());
        $posts->each(fn ($post) => $post->setRelation('thread', $thread));
        $postTree = $tree->build($posts);
        $hasMoreReplies = ! $focusedPostId && ! $showAllReplies && $thread->replies_count > 100;

        $isSubscribed = $request->user()
            ? $thread->subscribers()->whereKey($request->user()->id)->exists()
            : false;
        $moderationForums = $request->user()?->shouldEnterAdminPanel()
            ? Forum::query()->with('category')->orderBy('forum_category_id')->orderBy('sort_order')->get()
            : collect();

        return view('forums.threads.show', compact('thread', 'postTree', 'hasMoreReplies', 'showAllReplies', 'focusedPostId', 'isSubscribed', 'moderationForums'));
    }

    public function edit(ForumThread $thread): View
    {
        $thread->load('forum', 'posts');
        $this->authorize('update', $thread);

        return view('forums.threads.edit', compact('thread'));
    }

    public function update(UpdateForumThreadRequest $request, ForumThread $thread, ForumPostService $posts, CommunityPostImageService $images): RedirectResponse
    {
        $this->authorize('update', $thread);
        $initial = $thread->posts()->where('is_initial', true)->firstOrFail();
        $oldImagePath = $initial->image_path;
        $newImagePath = $request->hasFile('image') ? $images->store($request->file('image')) : null;
        $imagePath = $newImagePath ?: ($request->boolean('remove_image') ? null : $oldImagePath);

        try {
            DB::transaction(function () use ($request, $thread, $initial, $posts, $imagePath): void {
                $thread->update(['title' => $request->validated('title')]);
                $posts->update($initial, $request->validated('body') ?? '', $imagePath, true);
            });
        } catch (Throwable $exception) {
            $images->delete($newImagePath);

            throw $exception;
        }

        if ($oldImagePath !== $imagePath) {
            $images->delete($oldImagePath);
        }

        return redirect()->route('forum.threads.show', $thread)->with('success', 'Tema actualizado correctamente.');
    }

    public function destroy(Request $request, ForumThread $thread, ForumPostService $posts): RedirectResponse
    {
        $this->authorize('delete', $thread);
        $initial = $thread->posts()->where('is_initial', true)->firstOrFail();
        $forum = $thread->forum;
        $isQuestion = $thread->isQuestion();
        $posts->delete($initial);

        return $isQuestion
            ? redirect()->route('questions.index')->with('success', 'Pregunta eliminada correctamente.')
            : redirect()->route('forums.show', $forum)->with('success', 'Tema eliminado correctamente.');
    }
}
