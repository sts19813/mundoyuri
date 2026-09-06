<?php

namespace App\Services;

use App\Models\ForumPost;
use App\Models\ForumThread;
use Illuminate\Support\Collection;

class ForumConversationTree
{
    private const MAX_FOCUSED_ANCESTORS = 12;

    /** @return array<int, int> */
    public function focusedPostIds(ForumThread $thread, int $targetPostId, bool $includeHidden = false): array
    {
        $postQuery = fn () => $thread->posts()
            ->when(! $includeHidden, fn ($query) => $query->where('is_hidden', false));

        $target = $postQuery()->whereKey($targetPostId)->firstOrFail();
        $posts = collect([$target->id => $target]);
        $current = $target;
        $remainingAncestors = self::MAX_FOCUSED_ANCESTORS;

        while ($remainingAncestors-- > 0 && $current->reply_to_post_id && ! $posts->has($current->reply_to_post_id)) {
            $parent = $postQuery()->whereKey($current->reply_to_post_id)->first();
            if (! $parent) {
                break;
            }

            $posts->put($parent->id, $parent);
            $current = $parent;
        }

        $initial = $postQuery()->where('is_initial', true)->first();
        if ($initial) {
            $posts->put($initial->id, $initial);
        }

        return $posts->keys()->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @param  Collection<int, ForumPost>  $posts
     * @return Collection<int, ForumPost>
     */
    public function build(Collection $posts): Collection
    {
        $posts = $posts->sortBy('id')->values();
        $byId = $posts->keyBy('id');
        $initial = $posts->firstWhere('is_initial', true);

        foreach ($posts as $post) {
            $post->setRelation('treeReplies', collect());
        }

        $roots = collect();
        foreach ($posts as $post) {
            if ($post->is_initial) {
                $roots->push($post);

                continue;
            }

            // Replies created before directed replies existed belong to the opening post.
            $parent = $post->reply_to_post_id ? $byId->get($post->reply_to_post_id) : $initial;

            if ($parent && ! $parent->is($post)) {
                $parent->treeReplies->push($post);
            } else {
                $roots->push($post);
            }
        }

        return $roots;
    }
}
