<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Support\Collection;

class CommentConversationTree
{
    /**
     * @param  Collection<int, Comment>  $comments
     * @return Collection<int, Comment>
     */
    public function build(Collection $comments): Collection
    {
        $comments = $comments->sortBy('id')->values();
        $byId = $comments->keyBy('id');

        foreach ($comments as $comment) {
            $comment->setRelation('treeReplies', collect());
        }

        $roots = collect();
        foreach ($comments as $comment) {
            $parent = $comment->parent_id ? $byId->get($comment->parent_id) : null;

            if ($parent && ! $parent->is($comment)) {
                $parent->treeReplies->push($comment);
            } else {
                $roots->push($comment);
            }
        }

        return $roots->sortByDesc('created_at')->values();
    }
}
