<?php

namespace App\Services;

use App\Models\ForumMention;
use App\Models\ForumPost;
use App\Models\User;
use App\Notifications\ForumMentionNotification;
use Illuminate\Support\Facades\DB;

class ForumMentionService
{
    /** @return array<int, int> */
    public function record(ForumPost $post): array
    {
        preg_match_all('/(?<![\pL\pN_])@([\pL\pN][\pL\pN_.-]{1,59})/u', $post->body, $matches);
        $aliases = collect($matches[1] ?? [])->map(fn (string $alias) => mb_strtolower($alias))->unique()->values();

        if ($aliases->isEmpty()) {
            $post->mentions()->delete();

            return [];
        }

        $author = $post->author;
        $users = User::query()
            ->whereNotNull('alias')
            ->whereIn(DB::raw('LOWER(alias)'), $aliases)
            ->where('id', '!=', $post->user_id)
            ->get();

        $eligibleUsers = [];
        foreach ($users as $user) {
            if (! $author || $author->cannotInteractWith($user)) {
                continue;
            }

            $eligibleUsers[] = $user;
        }

        $post->mentions()->whereNotIn('mentioned_user_id', collect($eligibleUsers)->pluck('id'))->delete();

        $mentioned = [];
        foreach ($eligibleUsers as $user) {
            $mention = ForumMention::query()->firstOrCreate([
                'forum_post_id' => $post->id,
                'mentioned_user_id' => $user->id,
            ], ['mentioner_user_id' => $post->user_id]);

            if (! $mention->wasRecentlyCreated) {
                continue;
            }

            $mentioned[] = $user->id;
            $user->notify(new ForumMentionNotification($post, $author));
        }

        return $mentioned;
    }
}
