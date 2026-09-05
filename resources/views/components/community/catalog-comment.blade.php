@props(['comment', 'targetType', 'targetId', 'isReply' => false, 'previousUserId' => null])

<article id="comment-{{ $comment->id }}" class="{{ $isReply ? 'comment-reply' : 'comment-item' }} catalog-social-comment">
    <header class="comment-meta">
        @if($comment->user)
            <x-community.author-card :author="$comment->user" :inline-badges="true" :show-signature="false" />
        @else
            <span class="comment-avatar">{{ $comment->initials() }}</span>
            <span class="comment-user">{{ $comment->display_alias }}</span>
        @endif
        <time class="comment-date" datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->displayTime() }}</time>
    </header>
    <div class="catalog-social-comment-content">
        <p class="comment-text">{{ $comment->body }}</p>
        <x-community.signature :user="$comment->user" :previous-user-id="$previousUserId" />
        <x-community.reactions :reactable="$comment" />
        <details class="message-reply-box" @if((int) old('parent_id') === $comment->id) open @endif>
            <summary>↳ Responder</summary>
            <form method="POST" action="{{ route('comments.store') }}" class="message-inline-form">
                @csrf
                <input type="hidden" name="target_type" value="{{ $targetType }}">
                <input type="hidden" name="target_id" value="{{ $targetId }}">
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <label for="comment-reply-{{ $comment->id }}">Responder a {{ $comment->display_alias }}</label>
                <textarea id="comment-reply-{{ $comment->id }}" name="body" rows="3" minlength="2" maxlength="2500" required placeholder="Escribe tu respuesta…">{{ (int) old('parent_id') === $comment->id ? old('body') : '' }}</textarea>
                @guest
                    <label for="comment-alias-{{ $comment->id }}">Tu alias</label>
                    <input id="comment-alias-{{ $comment->id }}" type="text" name="alias" required minlength="2" maxlength="120" value="{{ (int) old('parent_id') === $comment->id ? old('alias') : '' }}">
                @endguest
                <div class="message-inline-buttons"><button type="submit" class="profile-btn profile-btn-primary">Publicar respuesta</button><button type="button" class="profile-btn profile-btn-text" data-cancel-reply>Cancelar</button></div>
                @if((int) old('parent_id') === $comment->id)
                    <p role="alert">{{ $errors->first('body') ?: $errors->first('parent_id') ?: $errors->first('alias') }}</p>
                @endif
            </form>
        </details>
        {{ $slot }}
    </div>
</article>
