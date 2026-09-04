<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreForumPostRequest;
use App\Http\Requests\UpdateForumPostRequest;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Services\CommunityReactionService;
use App\Services\ForumPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ForumPostController extends Controller
{
    public function store(StoreForumPostRequest $request, ForumThread $thread, ForumPostService $posts, CommunityReactionService $reactions): RedirectResponse|JsonResponse
    {
        $post = $posts->reply($thread, $request->user(), $request->validated('body'));

        if ($request->expectsJson()) {
            $post->load(['author.badges', 'author.communityRank', 'mentions.mentionedUser']);
            $post->setRelation('thread', $thread);
            $reactions->hydrateSummaries([$post], $request->user());

            return response()->json([
                'html' => view('components.forum.post', ['post' => $post])->render(),
                'replies_count' => $thread->fresh()->replies_count,
            ], 201);
        }

        if ($request->boolean('from_feed')) {
            return redirect()->to(route('forums.show', $thread->forum).'#thread-'.$thread->id);
        }

        return redirect()->to(route('forum.threads.show', $thread).'#post-'.$post->id);
    }

    public function edit(ForumPost $post): View
    {
        $post->load('thread');
        $this->authorize('update', $post);

        return view('forums.posts.edit', compact('post'));
    }

    public function update(UpdateForumPostRequest $request, ForumPost $post, ForumPostService $posts): RedirectResponse
    {
        $posts->update($post, $request->validated('body'));

        return redirect()->to(route('forum.threads.show', $post->thread).'#post-'.$post->id);
    }

    public function destroy(ForumPost $post, ForumPostService $posts): RedirectResponse
    {
        $post->load('thread.forum');
        $this->authorize('delete', $post);
        $thread = $post->thread;
        $forum = $thread->forum;
        $isInitial = $post->is_initial;
        $posts->delete($post);

        return $isInitial
            ? redirect()->route('forums.show', $forum)->with('success', 'Tema eliminado correctamente.')
            : redirect()->route('forum.threads.show', $thread)->with('success', 'Mensaje eliminado correctamente.');
    }
}
