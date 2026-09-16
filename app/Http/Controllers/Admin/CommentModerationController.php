<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Episode;
use App\Models\Series;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentModerationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->shouldEnterAdminPanel(), 403);

        $status = $request->string('status')->toString();
        abort_unless($status === '' || in_array($status, ['visible', 'hidden'], true), 404);

        $comments = Comment::query()
            ->with([
                'user',
                'parent.user',
                'commentable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    Episode::class => ['series'],
                    Series::class => [],
                ]),
            ])
            ->when($status === 'visible', fn ($query) => $query->where('is_approved', true))
            ->when($status === 'hidden', fn ($query) => $query->where('is_approved', false))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.comments.index', compact('comments', 'status'));
    }

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless($request->user()->shouldEnterAdminPanel(), 403);

        $validated = $request->validate([
            'is_approved' => ['required', 'boolean'],
        ]);

        $comment->update(['is_approved' => (bool) $validated['is_approved']]);

        return back()->with('success', (bool) $validated['is_approved']
            ? 'Comentario restaurado.'
            : 'Comentario ocultado.');
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless($request->user()->shouldEnterAdminPanel(), 403);

        $comment->delete();

        return back()->with('success', 'Comentario eliminado.');
    }
}
