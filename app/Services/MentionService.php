<?php

namespace App\Services;

use App\Models\ForumMention;
use App\Models\ForumPost;
use App\Models\User;
use App\Notifications\ForumMentionNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class MentionService
{
    // A dot is supported inside an alias, but never consumed as sentence punctuation.
    private const PATTERN = '/(?<![\pL\pN_])@([\pL\pN](?:[\pL\pN_-]|\.(?=[\pL\pN_-])){1,59})/u';

    /**
     * Synchronize valid mentions for a forum post and notify only newly added users.
     *
     * @return array<int, int>
     */
    public function record(ForumPost $post): array
    {
        $aliases = $this->extractAliases($post->body);

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

        $eligibleUsers = $users
            ->filter(fn (User $user) => $author && ! $author->cannotInteractWith($user))
            ->values();

        $post->mentions()->whereNotIn('mentioned_user_id', $eligibleUsers->modelKeys())->delete();

        $mentioned = [];
        foreach ($eligibleUsers as $user) {
            $mention = ForumMention::query()->firstOrCreate([
                'forum_post_id' => $post->id,
                'mentioned_user_id' => $user->id,
            ], ['mentioner_user_id' => $post->user_id]);

            // Existing records mean this person was already notified for this same
            // unchanged mention, including a regular post edit.
            if (! $mention->wasRecentlyCreated) {
                continue;
            }

            $mentioned[] = $user->id;
            $user->notify(new ForumMentionNotification($post, $author));
        }

        return $mentioned;
    }

    /**
     * Escape a body and replace only recorded, valid aliases with profile links.
     * No source HTML is rendered as markup.
     *
     * @param  iterable<User|null>  $mentionedUsers
     */
    public function render(string $body, iterable $mentionedUsers): HtmlString
    {
        $usersByAlias = collect($mentionedUsers)
            ->filter(fn ($user) => $user instanceof User && filled($user->alias))
            ->keyBy(fn (User $user) => mb_strtolower($user->alias));

        $escaped = e($body);
        $rendered = preg_replace_callback(self::PATTERN, function (array $match) use ($usersByAlias): string {
            $alias = mb_strtolower($match[1]);
            /** @var User|null $user */
            $user = $usersByAlias->get($alias);

            if (! $user) {
                return $match[0];
            }

            return '<a class="forum-mention" href="'.e($user->publicProfileUrl()).'">'.e($match[0]).'</a>';
        }, $escaped) ?? $escaped;

        return new HtmlString($rendered);
    }

    /** @return Collection<int, string> */
    private function extractAliases(string $body)
    {
        preg_match_all(self::PATTERN, $body, $matches);

        return collect($matches[1] ?? [])
            ->map(fn (string $alias) => mb_strtolower($alias))
            ->unique()
            ->values();
    }
}
