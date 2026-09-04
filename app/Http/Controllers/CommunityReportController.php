<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommunityReportRequest;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use App\Services\CommunityModerationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class CommunityReportController extends Controller
{
    public function store(StoreCommunityReportRequest $request, CommunityModerationService $moderation): RedirectResponse
    {
        $target = $this->target($request->validated('target'), $request->integer('target_id'));
        $this->authorizeTarget($request->user(), $target);

        $report = $moderation->submit(
            $request->user(),
            $target,
            $request->validated('reason'),
            $request->validated('details'),
        );

        return back()->with('success', $report->wasRecentlyCreated ? 'Gracias. El reporte fue enviado al equipo de moderación.' : 'Ya existe un reporte pendiente sobre este contenido.');
    }

    private function target(string $target, int $id): Model
    {
        return match ($target) {
            'thread' => ForumThread::query()->with('forum.category')->findOrFail($id),
            'post' => ForumPost::query()->with('thread.forum.category')->findOrFail($id),
            'user' => User::query()->findOrFail($id),
            default => abort(404),
        };
    }

    private function authorizeTarget(User $reporter, Model $target): void
    {
        if ($target instanceof User) {
            if ($reporter->is($target)) {
                throw ValidationException::withMessages(['target' => 'No puedes reportarte a ti misma.']);
            }

            $this->authorize('viewProfile', $target);

            return;
        }

        if ($target instanceof ForumPost) {
            $this->authorize('view', $target->thread);

            return;
        }

        $this->authorize('view', $target);
    }
}
