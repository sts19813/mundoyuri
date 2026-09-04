<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommunityReactionRequest;
use App\Models\Comment;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Services\CommunityReactionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

class CommunityReactionController extends Controller
{
    public function store(StoreCommunityReactionRequest $request, CommunityReactionService $reactions): RedirectResponse
    {
        $reactable = $this->target($request->validated('target'), $request->integer('target_id'));
        $this->authorizeReaction($request->user(), $reactable);

        $activeReaction = $reactions->toggle($request->user(), $reactable, $request->validated('type'));

        return back()->with('success', $activeReaction ? 'Reacción guardada.' : 'Reacción retirada.');
    }

    private function target(string $target, int $id): Model
    {
        return match ($target) {
            'thread' => ForumThread::query()->with(['forum.category', 'author'])->findOrFail($id),
            'post' => ForumPost::query()->with(['thread.forum.category', 'author'])->findOrFail($id),
            'comment' => Comment::query()->with('user')->findOrFail($id),
            default => abort(404),
        };
    }

    private function authorizeReaction($user, Model $reactable): void
    {
        if ($reactable instanceof ForumThread) {
            abort_unless($user->can('react', $reactable), 403);

            return;
        }

        if ($reactable instanceof ForumPost) {
            abort_unless($user->can('react', $reactable), 403);

            return;
        }

        abort_unless($reactable instanceof Comment && $reactable->is_approved, 404);
    }
}
