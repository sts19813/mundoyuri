<?php

namespace App\Http\Controllers;

use App\Models\DirectMessage;
use App\Models\DirectMessageFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DirectMessageFavoriteController extends Controller
{
    public function toggle(Request $request, DirectMessage $message): JsonResponse|RedirectResponse
    {
        $viewer = $request->user();
        $message->loadMissing('conversation');

        abort_unless(in_array($viewer->id, [
            $message->conversation->user_one_id,
            $message->conversation->user_two_id,
        ], true), 404);
        abort_if($message->isDeleted(), 404);

        $favorited = DB::transaction(function () use ($message, $viewer): bool {
            $favorite = DirectMessageFavorite::query()
                ->where('direct_message_id', $message->id)
                ->where('user_id', $viewer->id)
                ->lockForUpdate()
                ->first();

            if ($favorite) {
                $favorite->delete();

                return false;
            }

            DirectMessageFavorite::query()->create([
                'direct_message_id' => $message->id,
                'user_id' => $viewer->id,
            ]);

            return true;
        });

        if ($request->expectsJson()) {
            $otherUser = $message->sender_id === $viewer->id
                ? $message->recipient
                : $message->sender;

            app(ConversationController::class)->hydrateMessages(collect([$message]), $viewer);

            return response()->json([
                'favorited' => $favorited,
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
