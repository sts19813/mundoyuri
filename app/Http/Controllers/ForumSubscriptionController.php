<?php

namespace App\Http\Controllers;

use App\Models\ForumThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ForumSubscriptionController extends Controller
{
    public function store(Request $request, ForumThread $thread): RedirectResponse
    {
        $this->authorize('view', $thread);
        $thread->subscribers()->syncWithoutDetaching([$request->user()->id]);

        return back()->with('success', 'Ahora recibirás avisos de nuevas respuestas.');
    }

    public function destroy(Request $request, ForumThread $thread): RedirectResponse
    {
        $thread->subscribers()->detach($request->user()->id);

        return back()->with('success', 'Dejaste de seguir este tema.');
    }
}
