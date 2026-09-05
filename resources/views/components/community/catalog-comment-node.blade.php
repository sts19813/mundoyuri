@props(['comment', 'targetType', 'targetId', 'depth' => 0, 'previousUserId' => null])

@php
    $children = $comment->relationLoaded('treeReplies') ? $comment->getRelation('treeReplies') : collect();
    $visualDepth = min($depth, 6);
@endphp

<div class="comment-tree-branch" style="--tree-depth: {{ $visualDepth }}">
    <x-community.catalog-comment
        :comment="$comment"
        :target-type="$targetType"
        :target-id="$targetId"
        :is-reply="$depth > 0"
        :previous-user-id="$previousUserId"
    >
        @if($children->isNotEmpty())
            <div @class(['comment-tree-children', 'comment-tree-children-capped' => $depth >= 6])>
                @foreach($children as $child)
                    <x-community.catalog-comment-node
                        :comment="$child"
                        :target-type="$targetType"
                        :target-id="$targetId"
                        :depth="$depth + 1"
                        :previous-user-id="$comment->user_id"
                    />
                @endforeach
            </div>
        @endif
    </x-community.catalog-comment>
</div>
