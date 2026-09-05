<?php

namespace App\Services;

use App\Models\ForumPost;
use Illuminate\Support\Collection;

class ForumConversationTree
{
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
