<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Comment;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_type' => ['required', 'in:series,episode'],
            'target_id' => ['required', 'integer'],
            'body' => ['required', 'string', 'min:2', 'max:2500'],
            'alias' => ['nullable', 'string', 'min:2', 'max:120'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        if (!auth()->check() && empty($validated['alias'])) {
            return back()
                ->withInput()
                ->withErrors(['alias' => 'El alias es obligatorio para comentar como anonimo.']);
        }

        $commentable = $validated['target_type'] === 'series'
            ? Series::query()->whereKey($validated['target_id'])->firstOrFail()
            : Episode::query()->whereKey($validated['target_id'])->firstOrFail();

        if ($commentable->moderation_status !== 'approved') {
            abort(404);
        }

        $parent = null;
        if (!empty($validated['parent_id'])) {
            $parent = Comment::query()
                ->whereKey($validated['parent_id'])
                ->whereNull('parent_id')
                ->firstOrFail();

            $sameTarget = $parent->commentable_type === $commentable->getMorphClass()
                && (int) $parent->commentable_id === (int) $commentable->getKey();

            if (!$sameTarget) {
                throw ValidationException::withMessages([
                    'parent_id' => 'No se pudo responder ese comentario.',
                ]);
            }
        }

        $commentable->comments()->create([
            'user_id' => auth()->id(),
            'parent_id' => $parent?->id,
            'alias' => auth()->check() ? (auth()->user()->alias ?: auth()->user()->name) : $validated['alias'],
            'body' => $validated['body'],
            'is_approved' => true,
        ]);

        return back()->with('success', $parent ? 'Respuesta publicada correctamente.' : 'Comentario publicado correctamente.');
    }
}
