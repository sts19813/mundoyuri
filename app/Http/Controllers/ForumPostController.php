<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreForumPostRequest;
use App\Http\Requests\UpdateForumPostRequest;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Services\CommunityPostImageService;
use App\Services\CommunityReactionService;
use App\Services\ForumPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class ForumPostController extends Controller
{
    public function store(StoreForumPostRequest $request, ForumThread $thread, ForumPostService $posts, CommunityReactionService $reactions, CommunityPostImageService $images): RedirectResponse|JsonResponse
    {
        $post = $posts->reply(
            $thread,
            $request->user(),
            $request->validated('body') ?? '',
            $request->validated('reply_to_post_id'),
            $request->hasFile('image') ? $images->store($request->file('image')) : null,
        );

        if ($request->expectsJson()) {
            $post->load(['author.badges', 'author.communityRank', 'mentions.mentionedUser', 'replyTo.author']);
            $post->setRelation('thread', $thread);
            $reactions->hydrateSummaries([$post], $request->user());

            return response()->json([
                'html' => $request->boolean('from_feed')
                    ? view('components.forum.post', ['post' => $post])->render()
                    : view('components.forum.thread-post', ['post' => $post])->render(),
                'replies_count' => $thread->fresh()->replies_count,
            ], 201);
        }

        if ($request->boolean('from_feed')) {
            return redirect()->to(route('forums.show', $thread->forum).'#thread-'.$thread->id);
        }

        return redirect()->to($post->conversationUrl());
    }

    public function edit(ForumPost $post): View
    {
        $post->load('thread');
        $this->authorize('update', $post);

        return view('forums.posts.edit', compact('post'));
    }

    public function update(UpdateForumPostRequest $request, ForumPost $post, ForumPostService $posts, CommunityPostImageService $images): RedirectResponse
    {
        $oldImagePath = $post->image_path;
        $newImagePath = $request->hasFile('image') ? $images->store($request->file('image')) : null;
        $imagePath = $newImagePath ?: ($request->boolean('remove_image') ? null : $oldImagePath);

        try {
            $posts->update($post, $request->validated('body') ?? '', $imagePath, true);
        } catch (Throwable $exception) {
            $images->delete($newImagePath);

            throw $exception;
        }

        if ($oldImagePath !== $imagePath) {
            $images->delete($oldImagePath);
        }

        return redirect()->to($post->conversationUrl());
    }

    public function destroy(ForumPost $post, ForumPostService $posts): RedirectResponse
    {
        $post->load('thread.forum');
        $this->authorize('delete', $post);
        $thread = $post->thread;
        $forum = $thread->forum;
        $isInitial = $post->is_initial;
        $isQuestion = $thread->isQuestion();
        $posts->delete($post);

        if ($isInitial) {
            return $isQuestion
                ? redirect()->route('questions.index')->with('success', 'Pregunta eliminada correctamente.')
                : redirect()->route('forums.show', $forum)->with('success', 'Tema eliminado correctamente.');
        }

        return redirect()
            ->route($isQuestion ? 'questions.show' : 'forum.threads.show', $thread)
            ->with('success', 'Mensaje eliminado correctamente.');
    }
}
