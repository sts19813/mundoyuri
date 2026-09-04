<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\CommunityReaction;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use App\Notifications\CommunityReactionNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;

class CommunityReactionService
{
    /**
     * Toggles an equal reaction off, or replaces the member's previous reaction.
     *
     * @return CommunityReaction|null The active reaction after the operation.
     */
    public function toggle(User $actor, Model $reactable, string $type): ?CommunityReaction
    {
        $reaction = DB::transaction(function () use ($actor, $reactable, $type): ?CommunityReaction {
            $existing = CommunityReaction::query()
                ->where('user_id', $actor->id)
                ->where('reactable_type', $reactable->getMorphClass())
                ->where('reactable_id', $reactable->getKey())
                ->lockForUpdate()
                ->first();

            if ($existing?->type === $type) {
                $existing->delete();

                return null;
            }

            if ($existing) {
                $existing->update(['type' => $type]);

                return $existing->refresh();
            }

            return $reactable->reactions()->create([
                'user_id' => $actor->id,
                'type' => $type,
            ]);
        });

        if ($reaction) {
            $this->notifyAuthor($actor, $reactable, $type);
        }

        return $reaction;
    }

    /**
     * Hydrates a compact summary with one query for a rendered collection.
     *
     * @param  iterable<Model>  $reactables
     */
    public function hydrateSummaries(iterable $reactables, ?User $viewer = null): void
    {
        $reactables = collect($reactables)
            ->filter(fn ($reactable) => $reactable instanceof Model && $reactable->exists)
            ->unique(fn (Model $reactable) => $reactable->getMorphClass().':'.$reactable->getKey())
            ->values();

        if ($reactables->isEmpty()) {
            return;
        }

        $targets = $reactables->groupBy(fn (Model $reactable) => $reactable->getMorphClass());
        $reactions = CommunityReaction::query()
            ->where(function ($query) use ($targets): void {
                foreach ($targets as $morphClass => $models) {
                    $query->orWhere(function ($targetQuery) use ($morphClass, $models): void {
                        $targetQuery->where('reactable_type', $morphClass)
                            ->whereIn('reactable_id', $models->pluck('id'));
                    });
                }
            })
            ->get(['id', 'user_id', 'reactable_type', 'reactable_id', 'type']);

        $grouped = $reactions->groupBy(fn (CommunityReaction $reaction) => $reaction->reactable_type.':'.$reaction->reactable_id);

        $reactables->each(function (Model $reactable) use ($grouped, $viewer): void {
            $key = $reactable->getMorphClass().':'.$reactable->getKey();
            /** @var Collection<int, CommunityReaction> $targetReactions */
            $targetReactions = $grouped->get($key, new Collection);
            $summary = array_fill_keys(CommunityReaction::typeKeys(), 0);

            foreach ($targetReactions as $reaction) {
                $summary[$reaction->type]++;
            }

            $reactable->setAttribute('reaction_summary', $summary);
            $reactable->setAttribute(
                'viewer_reaction_type',
                $viewer ? $targetReactions->firstWhere('user_id', $viewer->id)?->type : null,
            );
        });
    }

    private function notifyAuthor(User $actor, Model $reactable, string $type): void
    {
        $author = $this->authorOf($reactable);

        if (! $author || $author->is($actor)) {
            return;
        }

        $url = $this->urlFor($reactable);

        if (! $url) {
            return;
        }

        $notification = $author->unreadNotifications()
            ->where('type', CommunityReactionNotification::class)
            ->latest()
            ->limit(25)
            ->get()
            ->first(fn (DatabaseNotification $notification) => ($notification->data['reactable_type'] ?? null) === $reactable->getMorphClass()
                && (int) ($notification->data['reactable_id'] ?? 0) === (int) $reactable->getKey()
            );

        if (! $notification) {
            $author->notify(new CommunityReactionNotification($reactable, $actor, $type, $url));

            return;
        }

        $data = $notification->data;
        $actorIds = collect($data['actor_ids'] ?? [])->map(fn ($id) => (int) $id)->push($actor->id)->unique()->values();
        $actorNames = collect($data['actor_names'] ?? [])->filter()->push($actor->displayName())->unique()->values();
        $reaction = CommunityReaction::types()[$type];
        $title = $actorNames->count() === 1
            ? $actor->displayName().' reaccionó '.$reaction['emoji'].' a tu publicación'
            : $actorNames->take(2)->implode(' y ').' y otras personas reaccionaron a tu publicación';

        $notification->update([
            'data' => array_merge($data, [
                'title' => $title,
                'message' => $reaction['label'],
                'actor_id' => $actor->id,
                'actor_name' => $actor->displayName(),
                'actor_avatar' => $actor->avatarUrl(),
                'actor_ids' => $actorIds->all(),
                'actor_names' => $actorNames->all(),
                'actor_count' => $actorIds->count(),
                'reaction_type' => $type,
                'url' => $url,
            ]),
        ]);
    }

    private function authorOf(Model $reactable): ?User
    {
        return match (true) {
            $reactable instanceof ForumThread => $reactable->author,
            $reactable instanceof ForumPost => $reactable->author,
            $reactable instanceof Comment => $reactable->user,
            default => null,
        };
    }

    private function urlFor(Model $reactable): ?string
    {
        if ($reactable instanceof ForumThread) {
            return route($reactable->isQuestion() ? 'questions.show' : 'forum.threads.show', $reactable);
        }

        if ($reactable instanceof ForumPost) {
            $thread = $reactable->thread;

            return route($thread->isQuestion() ? 'questions.show' : 'forum.threads.show', $thread).'#post-'.$reactable->id;
        }

        // Comments are already valid polymorphic targets. A comment UI can opt into
        // reactions later once it has a stable per-comment anchor and public URL.
        return null;
    }
}
