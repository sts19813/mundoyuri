<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\DirectMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConversationModerationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->shouldEnterAdminPanel(), 403);

        $search = trim($request->string('q')->toString());
        $like = '%'.$search.'%';

        $conversations = Conversation::query()
            ->with(['userOne', 'userTwo', 'lastMessage.sender'])
            ->withCount('messages')
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($like): void {
                $query
                    ->whereHas('userOne', fn (Builder $user) => $user->where('name', 'like', $like)->orWhere('alias', 'like', $like)->orWhere('email', 'like', $like))
                    ->orWhereHas('userTwo', fn (Builder $user) => $user->where('name', 'like', $like)->orWhere('alias', 'like', $like)->orWhere('email', 'like', $like));
            }))
            ->orderByDesc('last_message_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.conversations.index', compact('conversations', 'search'));
    }

    public function show(Request $request, Conversation $conversation): View
    {
        abort_unless($request->user()->shouldEnterAdminPanel(), 403);

        $conversation->load(['userOne', 'userTwo']);
        $messages = $conversation->messages()
            ->with(['sender', 'recipient', 'deletedBy'])
            ->oldest()
            ->paginate(100);

        return view('admin.conversations.show', compact('conversation', 'messages'));
    }

    public function attachment(Request $request, DirectMessage $message): StreamedResponse
    {
        abort_unless($request->user()->shouldEnterAdminPanel(), 403);
        abort_unless($message->hasStoredAttachment() && Storage::disk('local')->exists($message->attachment_path), 404);

        return Storage::disk('local')->response(
            $message->attachment_path,
            $message->attachment_name,
            ['Content-Type' => $message->attachment_mime ?: 'application/octet-stream'],
            str_starts_with((string) $message->attachment_mime, 'image/') ? 'inline' : 'attachment',
        );
    }
}
