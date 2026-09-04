<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Services\ForumCounterService;
use App\Services\ForumPostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ForumModerationController extends Controller
{
    public function updateThread(Request $request, ForumThread $thread, ForumCounterService $counters): RedirectResponse
    {
        $this->authorize('moderate', $thread);
        $data = $request->validate([
            'is_locked' => ['nullable', 'boolean'],
            'is_pinned' => ['nullable', 'boolean'],
            'is_hidden' => ['nullable', 'boolean'],
            'forum_id' => ['nullable', 'exists:forums,id'],
        ]);

        $updates = [];
        foreach (['is_locked', 'is_pinned', 'is_hidden'] as $field) {
            if ($request->has($field)) {
                $updates[$field] = $request->boolean($field);
            }
        }
        if (isset($data['forum_id'])) {
            $updates['forum_id'] = $data['forum_id'];
        }

        $thread->update($updates);

        $counters->synchronizeThread($thread);
        $thread->posts()->with('author')->get()->pluck('author')->filter()->unique('id')
            ->each(fn ($author) => $counters->synchronizeUser($author));

        return back()->with('success', 'Moderación del tema actualizada.');
    }

    public function hidePost(ForumPost $post, ForumPostService $posts): RedirectResponse
    {
        $this->authorize('moderate', $post);
        $posts->hide($post);

        return back()->with('success', 'Mensaje ocultado.');
    }
}
