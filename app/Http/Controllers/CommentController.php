<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

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

        $commentable->comments()->create([
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'alias' => auth()->check() ? (auth()->user()->alias ?: auth()->user()->name) : $validated['alias'],
            'body' => $validated['body'],
            'is_approved' => true,
        ]);

        return back()->with('success', 'Comentario publicado correctamente.');
    }
}
