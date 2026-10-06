<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\CommunityReaction;
use App\Models\DirectMessage;
use App\Models\User;
use App\Notifications\NewDirectMessageNotification;
use App\Services\DirectMessageAttachmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();
        $search = trim($request->string('q')->toString());
        $conversations = $this->conversationList($viewer, $search)
            ->orderByDesc('last_message_at')
            ->paginate(30, ['*'], 'conversations_page')
            ->withQueryString();

        return view('messages.index', [
            'conversations' => $conversations,
            'viewer' => $viewer,
            'search' => $search,
            'activeUser' => null,
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
                ->with(['sender', 'recipient', 'replyTo.sender'])
                ->latest()
                ->paginate(50, ['*'], 'messages_page')
                ->withQueryString();
            $messages->setCollection($messages->getCollection()->reverse()->values());
            $this->hydrateMessages($messages->getCollection(), $viewer);
        } else {
            $messages = DirectMessage::query()
                ->whereRaw('1 = 0')
                ->paginate(50, ['*'], 'messages_page');
        }

        $search = trim($request->string('q')->toString());
        $conversations = $this->conversationList($viewer, $search)
            ->orderByDesc('last_message_at')
            ->paginate(30, ['*'], 'conversations_page')
            ->withQueryString();

        return view('messages.show', [
            'conversation' => $conversation,
            'conversations' => $conversations,
            'messages' => $messages,
            'otherUser' => $user,
            'viewer' => $viewer,
            'search' => $search,
            'activeUser' => $user,
            'interactionBlocked' => $viewer->cannotInteractWith($user),
            'viewerHasBlocked' => $viewer->hasBlocked($user),
        ]);
    }

    public function poll(Request $request, User $user): JsonResponse
    {
        $viewer = $request->user();

        abort_if($viewer->is($user), 404);

        $afterId = max(0, $request->integer('after_id'));
        $conversation = Conversation::between($viewer, $user)->first();

        if (! $conversation) {
            return response()->json([
                'messages' => [],
                'latest_message_id' => $afterId,
                'read_outgoing_ids' => [],
            ]);
        }

        abort_unless(in_array($viewer->id, [
            $conversation->user_one_id,
            $conversation->user_two_id,
        ], true), 404);

        $messages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->with(['sender', 'recipient', 'replyTo.sender'])
            ->orderBy('id')
            ->limit(50)
            ->get();
        $this->hydrateMessages($messages, $viewer);

        $incomingMessageIds = $messages
            ->where('recipient_id', $viewer->id)
            ->whereNull('read_at')
            ->pluck('id');

        if ($incomingMessageIds->isNotEmpty()) {
            DirectMessage::query()
                ->whereIn('id', $incomingMessageIds)
                ->update(['read_at' => now()]);

            $messages->each(function (DirectMessage $message) use ($incomingMessageIds): void {
                if ($incomingMessageIds->contains($message->id)) {
                    $message->read_at = now();
                }
            });
        }

        $latestMessageId = max($afterId, (int) ($conversation->messages()->max('id') ?? 0));

        return response()->json([
            'messages' => $messages
                ->map(fn (DirectMessage $message) => $this->messagePayload($message, $viewer, $user))
                ->values(),
            'latest_message_id' => $latestMessageId,
            'read_outgoing_ids' => $conversation->messages()
                ->where('sender_id', $viewer->id)
                ->whereNotNull('read_at')
                ->pluck('id')
                ->values(),
        ]);
    }

    public function store(Request $request, User $user, DirectMessageAttachmentService $attachments): JsonResponse|RedirectResponse
    {
        $viewer = $request->user();

        abort_if($viewer->is($user), 404);
        abort_unless($user->is_active, 404);

        if ($viewer->cannotInteractWith($user)) {
            return back()->with('error', 'No es posible enviar mensajes entre estas cuentas.');
        }

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:2000', 'required_without:attachment'],
            'reply_to_message_id' => ['nullable', 'integer'],
            'attachment' => [
                'nullable',
                'file',
                'max:20480',
                'mimes:jpg,jpeg,png,webp,gif,pdf,txt,csv,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp',
            ],
        ]);

        $body = trim($validated['body'] ?? '');
        $replyToMessageId = $this->validReplyTargetId($viewer, $user, $validated['reply_to_message_id'] ?? null);

        if ($body === '' && ! $request->hasFile('attachment')) {
            return back()
                ->withErrors(['body' => 'Escribe un mensaje o adjunta un archivo antes de enviarlo.'])
                ->withInput();
        }

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $attachment = $attachments->store($request->file('attachment'));

            if (! $attachment['attachment_path']) {
                throw ValidationException::withMessages(['attachment' => 'No fue posible guardar el archivo. Inténtalo de nuevo.']);
            }
        }

        try {
            $message = DB::transaction(function () use ($viewer, $user, $body, $attachment, $replyToMessageId): DirectMessage {
                [$userOneId, $userTwoId] = Conversation::participantIds($viewer, $user);

                $conversation = Conversation::query()->firstOrCreate([
                    'user_one_id' => $userOneId,
                    'user_two_id' => $userTwoId,
                ]);

                $message = $conversation->messages()->create([
                    'sender_id' => $viewer->id,
                    'recipient_id' => $user->id,
                    'reply_to_message_id' => $replyToMessageId,
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

        if ($request->expectsJson()) {
            $message->load(['sender', 'recipient', 'replyTo.sender']);
            $this->hydrateMessages(collect([$message]), $viewer);

            return response()->json([
                'messages' => [$this->messagePayload($message, $viewer, $user)],
                'latest_message_id' => $message->id,
            ], 201);
        }

        return redirect()->route('messages.show', $user);
    }

    public function destroy(Request $request, DirectMessage $message): RedirectResponse
    {
        $viewer = $request->user();
        $message->loadMissing('conversation');

        abort_unless($message->sender_id === $viewer->id, 403);
        abort_unless(in_array($viewer->id, [
            $message->conversation->user_one_id,
            $message->conversation->user_two_id,
        ], true), 404);

        if (! $message->isDeleted()) {
            $message->update([
                'deleted_at' => now(),
                'deleted_by' => $viewer->id,
            ]);
        }

        return back()->with('success', 'Mensaje eliminado.');
    }

    public function attachment(Request $request, DirectMessage $message): StreamedResponse
    {
        $viewerId = $request->user()->id;
        $message->loadMissing('conversation');
        abort_unless(in_array($viewerId, [
            $message->conversation->user_one_id,
            $message->conversation->user_two_id,
        ], true), 404);
        abort_unless(! $message->isDeleted() && $message->hasStoredAttachment() && Storage::disk('local')->exists($message->attachment_path), 404);

        return Storage::disk('local')->response(
            $message->attachment_path,
            $message->attachment_name,
            ['Content-Type' => $message->attachment_mime ?: 'application/octet-stream'],
            $message->attachmentIsImage() ? 'inline' : 'attachment',
        );
    }

    private function conversationList(User $viewer, string $search): Builder
    {
        return Conversation::query()
            ->where(function (Builder $query) use ($viewer): void {
                $query
                    ->where('user_one_id', $viewer->id)
                    ->orWhere('user_two_id', $viewer->id);
            })
            ->when($search !== '', function (Builder $query) use ($viewer, $search): void {
                $like = '%'.$search.'%';
                $query->where(function (Builder $participants) use ($viewer, $like): void {
                    $participants
                        ->where(function (Builder $first) use ($viewer, $like): void {
                            $first->where('user_one_id', $viewer->id)
                                ->whereHas('userTwo', fn (Builder $user) => $user
                                    ->where('name', 'like', $like)
                                    ->orWhere('alias', 'like', $like));
                        })
                        ->orWhere(function (Builder $second) use ($viewer, $like): void {
                            $second->where('user_two_id', $viewer->id)
                                ->whereHas('userOne', fn (Builder $user) => $user
                                    ->where('name', 'like', $like)
                                    ->orWhere('alias', 'like', $like));
                        });
                });
            })
            ->with(['userOne', 'userTwo', 'lastMessage.sender'])
            ->withCount([
                'messages as unread_messages_count' => fn (Builder $query) => $query
                    ->where('recipient_id', $viewer->id)
                    ->whereNull('read_at'),
            ]);
    }

    /**
     * @param  iterable<int, DirectMessage>|Collection<int, DirectMessage>  $messages
     */
    public function hydrateMessages(iterable $messages, User $viewer): void
    {
        $collection = $messages instanceof Collection ? $messages : collect($messages);
        $ids = $collection->pluck('id')->filter()->values();

        if ($ids->isEmpty()) {
            return;
        }

        $summary = DB::table('direct_message_reactions')
            ->select('direct_message_id', 'type')
            ->selectRaw('COUNT(*) as total')
            ->whereIn('direct_message_id', $ids)
            ->groupBy('direct_message_id', 'type')
            ->get()
            ->groupBy('direct_message_id');

        $viewerReactions = DB::table('direct_message_reactions')
            ->where('user_id', $viewer->id)
            ->whereIn('direct_message_id', $ids)
            ->pluck('type', 'direct_message_id');

        $emptySummary = array_fill_keys(CommunityReaction::typeKeys(), 0);

        $collection->each(function (DirectMessage $message) use ($summary, $viewerReactions, $emptySummary): void {
            $messageSummary = $emptySummary;

            foreach ($summary->get($message->id, collect()) as $reaction) {
                $messageSummary[$reaction->type] = (int) $reaction->total;
            }

            $message->setAttribute('reaction_summary', $messageSummary);
            $message->setAttribute('viewer_reaction_type', $viewerReactions[$message->id] ?? null);
        });
    }

    /**
     * @return array{id: int, incoming: bool, html: string}
     */
    private function messagePayload(DirectMessage $message, User $viewer, User $otherUser): array
    {
        return [
            'id' => $message->id,
            'incoming' => $message->sender_id !== $viewer->id,
            'html' => view('messages._message', [
                'message' => $message,
                'viewer' => $viewer,
                'otherUser' => $otherUser,
            ])->render(),
        ];
    }

    private function validReplyTargetId(User $viewer, User $recipient, mixed $replyToMessageId): ?int
    {
        if (! $replyToMessageId) {
            return null;
        }

        $conversation = Conversation::between($viewer, $recipient)->first();

        if (! $conversation) {
            throw ValidationException::withMessages([
                'reply_to_message_id' => 'El mensaje citado ya no está disponible.',
            ]);
        }

        $target = $conversation->messages()
            ->whereKey((int) $replyToMessageId)
            ->whereNull('deleted_at')
            ->first();

        if (! $target) {
            throw ValidationException::withMessages([
                'reply_to_message_id' => 'El mensaje citado ya no está disponible.',
            ]);
        }

        return $target->id;
    }
}
