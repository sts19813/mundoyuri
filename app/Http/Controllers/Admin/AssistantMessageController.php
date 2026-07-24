<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssistantMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssistantMessageController extends Controller
{
    public function index(Request $request): View
    {
        $messages = AssistantMessage::query()
            ->with('user:id,name,email')
            ->when(
                $request->filled('type'),
                fn ($query) => $query->where('type', (string) $request->string('type'))
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', (string) $request->string('status'))
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.assistant-messages.index', compact('messages'));
    }

    public function update(Request $request, AssistantMessage $assistantMessage): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:unread,read,resolved'],
        ]);

        $assistantMessage->update($validated);

        return back()->with('success', 'Estado del mensaje actualizado.');
    }
}
