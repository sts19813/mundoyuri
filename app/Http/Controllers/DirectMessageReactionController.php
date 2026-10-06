<?php

namespace App\Http\Controllers;

use App\Models\CommunityReaction;
use App\Models\DirectMessage;
use App\Models\DirectMessageReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DirectMessageReactionController extends Controller
{
    public function store(Request $request, DirectMessage $message): JsonResponse|RedirectResponse
    {
        $viewer = $request->user();
        $message->loadMissing(['conversation.userOne', 'conversation.userTwo']);

        abort_unless(in_array($viewer->id, [
            $message->conversation->user_one_id,
            $message->conversation->user_two_id,
        ], true), 404);

        abort_if($message->isDeleted(), 404);

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(CommunityReaction::typeKeys())],
        ]);

        $activeReaction = DB::transaction(function () use ($message, $viewer, $validated): ?DirectMessageReaction {
            $existing = $message->reactions()
                ->where('user_id', $viewer->id)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->type === $validated['type']) {
                $existing->delete();

                return null;
            }

            if ($existing) {
                $existing->update(['type' => $validated['type']]);

                return $existing->refresh();
            }

            return $message->reactions()->create([
                'user_id' => $viewer->id,
                'type' => $validated['type'],
            ]);
        });

        if ($request->expectsJson()) {
            $otherUser = $message->sender_id === $viewer->id
                ? $message->recipient
                : $message->sender;

            app(ConversationController::class)->hydrateMessages(collect([$message]), $viewer);

            return response()->json([
                'active_type' => $activeReaction?->type,
                'html' => view('messages._message', [
                    'message' => $message->loadMissing(['sender', 'recipient', 'replyTo.sender']),
                    'viewer' => $viewer,
                    'otherUser' => $otherUser,
                ])->render(),
            ]);
        }

        return back();
    }
}
