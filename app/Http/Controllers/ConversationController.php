<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\DirectMessage;
use App\Models\User;
use App\Notifications\NewDirectMessageNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();

        $conversations = Conversation::query()
            ->where(function ($query) use ($viewer): void {
                $query
                    ->where('user_one_id', $viewer->id)
                    ->orWhere('user_two_id', $viewer->id);
            })
            ->with(['userOne', 'userTwo', 'lastMessage.sender'])
            ->withCount([
                'messages as unread_messages_count' => fn ($query) => $query
                    ->where('recipient_id', $viewer->id)
                    ->whereNull('read_at'),
            ])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('messages.index', [
            'conversations' => $conversations,
            'viewer' => $viewer,
        ]);
    }

    public function show(Request $request, User $user): View|RedirectResponse
    {
        $viewer = $request->user();

        if ($viewer->is($user)) {
            return redirect()->route('messages.index');
        }

        $conversation = Conversation::between($viewer, $user)->first();

        if ($conversation) {
            $conversation->messages()
                ->where('recipient_id', $viewer->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $messages = $conversation->messages()
                ->with('sender')
                ->latest()
                ->paginate(50);
            $messages->setCollection($messages->getCollection()->reverse()->values());
        } else {
            $messages = DirectMessage::query()
                ->whereRaw('1 = 0')
                ->paginate(50);
        }

        return view('messages.show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'otherUser' => $user,
            'viewer' => $viewer,
            'interactionBlocked' => $viewer->cannotInteractWith($user),
            'viewerHasBlocked' => $viewer->hasBlocked($user),
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $viewer = $request->user();

        abort_if($viewer->is($user), 404);
        abort_unless($user->is_active, 404);

        if ($viewer->cannotInteractWith($user)) {
            return back()->with('error', 'No es posible enviar mensajes entre estas cuentas.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $body = trim($validated['body']);

        if ($body === '') {
            return back()
                ->withErrors(['body' => 'Escribe un mensaje antes de enviarlo.'])
                ->withInput();
        }

        $message = DB::transaction(function () use ($viewer, $user, $body): DirectMessage {
            [$userOneId, $userTwoId] = Conversation::participantIds($viewer, $user);

            $conversation = Conversation::query()->firstOrCreate([
                'user_one_id' => $userOneId,
                'user_two_id' => $userTwoId,
            ]);

            $message = $conversation->messages()->create([
                'sender_id' => $viewer->id,
                'recipient_id' => $user->id,
                'body' => $body,
            ]);

            $conversation->update(['last_message_at' => $message->created_at]);

            return $message;
        });

        $user->notify(new NewDirectMessageNotification($message, $viewer));

        return redirect()
            ->route('messages.show', $user)
            ->with('success', 'Mensaje enviado.');
    }
}
