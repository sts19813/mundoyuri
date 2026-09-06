@props(['post', 'question' => null, 'acceptedAnswerPostId' => null, 'depth' => 0])

@php
    $children = $post->relationLoaded('treeReplies') ? $post->getRelation('treeReplies') : collect();
    $visualDepth = min($depth, 6);
@endphp

<div class="forum-post-branch" style="--tree-depth: {{ $visualDepth }}">
    <x-forum.post
        :post="$post"
        :question="$question"
        :is-accepted="$acceptedAnswerPostId === $post->id"
    />
    @if($children->isNotEmpty())
        <div @class(['forum-post-children', 'forum-post-children-capped' => $depth >= 6])>
            @foreach($children as $child)
                <x-forum.thread-post
                    :post="$child"
                    :question="$question"
                    :accepted-answer-post-id="$acceptedAnswerPostId"
                    :depth="$depth + 1"
                />
            @endforeach
        </div>
    @endif
</div>
