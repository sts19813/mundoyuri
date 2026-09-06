<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\DirectMessage;
use App\Models\User;
use App\Notifications\NewDirectMessageNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

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
            'body' => ['nullable', 'string', 'max:2000', 'required_without:attachment'],
            'attachment' => [
                'nullable',
                'file',
                'max:20480',
                'mimes:jpg,jpeg,png,webp,pdf,txt,csv,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp',
            ],
        ]);

        $body = trim($validated['body'] ?? '');

        if ($body === '' && ! $request->hasFile('attachment')) {
            return back()
                ->withErrors(['body' => 'Escribe un mensaje o adjunta un archivo antes de enviarlo.'])
                ->withInput();
        }

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('direct-message-attachments', 'local');
            if (! $path) {
                throw ValidationException::withMessages(['attachment' => 'No fue posible guardar el archivo. Inténtalo de nuevo.']);
            }
            $attachment = [
                'attachment_path' => $path,
                'attachment_name' => Str::limit(basename(str_replace('\\', '/', $file->getClientOriginalName())), 255, ''),
                'attachment_mime' => $file->getMimeType(),
                'attachment_size' => $file->getSize(),
            ];
        }

        try {
            $message = DB::transaction(function () use ($viewer, $user, $body, $attachment): DirectMessage {
                [$userOneId, $userTwoId] = Conversation::participantIds($viewer, $user);

                $conversation = Conversation::query()->firstOrCreate([
                    'user_one_id' => $userOneId,
                    'user_two_id' => $userTwoId,
                ]);

                $message = $conversation->messages()->create([
                    'sender_id' => $viewer->id,
                    'recipient_id' => $user->id,
                    'body' => $body,
                    ...($attachment ?? []),
                ]);

                $conversation->update(['last_message_at' => $message->created_at]);

                return $message;
            });
        } catch (Throwable $exception) {
            if ($attachment) {
                Storage::disk('local')->delete($attachment['attachment_path']);
            }

            throw $exception;
        }

        $user->notify(new NewDirectMessageNotification($message, $viewer));

        return redirect()
            ->route('messages.show', $user)
            ->with('success', 'Mensaje enviado.');
    }

    public function attachment(Request $request, DirectMessage $message): StreamedResponse
    {
        $viewerId = $request->user()->id;
        $message->loadMissing('conversation');
        abort_unless(in_array($viewerId, [
            $message->conversation->user_one_id,
            $message->conversation->user_two_id,
        ], true), 404);
        abort_unless($message->hasAttachment() && Storage::disk('local')->exists($message->attachment_path), 404);

        return Storage::disk('local')->response(
            $message->attachment_path,
            $message->attachment_name,
            ['Content-Type' => $message->attachment_mime ?: 'application/octet-stream'],
            $message->attachmentIsImage() ? 'inline' : 'attachment',
        );
    }
}
