@props(['post', 'question' => null, 'isAccepted' => false])

@php($author = $post->author)
<article id="post-{{ $post->id }}" class="forum-post {{ !$post->is_initial ? 'forum-post-comment' : '' }} {{ $post->is_hidden ? 'is-hidden' : '' }} {{ $isAccepted ? 'forum-post-accepted' : '' }}">
    <header class="forum-post-header">
        @if($author)
            <x-community.author-card :author="$author" :show-reputation="(bool) $question" />
        @else
            <div class="forum-post-author-compact"><span class="forum-post-avatar">?</span><span class="forum-post-name">{{ $post->authorName() }}</span></div>
        @endif

        <div class="forum-post-meta">
            <a href="#post-{{ $post->id }}"><time datetime="{{ $post->created_at->toIso8601String() }}">{{ $post->created_at->timezone('America/Merida')->translatedFormat('d M Y · H:i') }}</time></a>
            @if($post->edited_at)<span>Editado</span>@endif
        </div>

        @auth
            <details class="forum-post-menu">
                <summary aria-label="Acciones para este mensaje">•••</summary>
                <div class="forum-post-menu-panel">
                    @can('update', $post)
                        <a href="{{ $post->is_initial && !$post->thread->isQuestion() ? route('forum.threads.edit', $post->thread) : route('forum.posts.edit', $post) }}">Editar</a>
                    @endcan
                    @can('delete', $post)
                        <form method="POST" action="{{ route('forum.posts.destroy', $post) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('¿Eliminar este mensaje?')">Eliminar</button>
                        </form>
                    @endcan
                    @can('moderate', $post)
                        @if($post->is_initial && !$post->thread->isQuestion())
                            <a href="{{ route('forum.threads.show', $post->thread) }}#moderacion">Moderar tema</a>
                        @endif
                        @if(!$post->is_hidden)
                        <form method="POST" action="{{ route('forum.moderation.post.hide', $post) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit">Ocultar</button>
                        </form>
                        @endif
                    @endcan
                    <x-community.report-form :reportable="$post" />
                </div>
            </details>
        @endauth
    </header>

    <div class="forum-post-content">
        @if($post->is_hidden)
            <p class="forum-hidden-message">Este mensaje está oculto por moderación.</p>
        @else
            <div class="forum-post-body">{!! nl2br(app(\App\Services\MentionService::class)->render($post->body, $post->mentions->pluck('mentionedUser'))) !!}</div>
            @if($post->imageUrl())
                <a class="forum-post-image" href="{{ $post->imageUrl() }}" target="_blank" rel="noopener">
                    <img src="{{ $post->imageUrl() }}" alt="Imagen adjunta por {{ $post->authorName() }}" loading="lazy">
                </a>
            @endif
            <div class="forum-post-feedback">
                <div class="forum-post-interactions">
                    <x-community.reactions :reactable="$question && $post->is_initial ? $question : $post" />
                    @auth
                        @can('reply', $post->thread)
                            <details class="message-reply-box" @if((int) old('reply_to_post_id') === $post->id) open @endif>
                                <summary>↳ Responder</summary>
                                <form method="POST" action="{{ $question ? route('questions.answers.store', $question) : route('forum.posts.store', $post->thread) }}" class="message-inline-form" data-inline-reply enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="reply_to_post_id" value="{{ $post->id }}">
                                    <label for="post-reply-{{ $post->id }}">Responder a {{ $post->authorName() }}</label>
                                    <textarea id="post-reply-{{ $post->id }}" name="body" rows="3" minlength="2" maxlength="12000" placeholder="Escribe tu respuesta…">{{ (int) old('reply_to_post_id') === $post->id ? old('body') : '' }}</textarea>
                                    <div class="message-inline-buttons"><x-forum.image-field :id="'post-image-'.$post->id" :show-errors="false" /><button type="submit" class="profile-btn profile-btn-primary">Publicar respuesta</button><button type="button" class="profile-btn profile-btn-text" data-cancel-reply>Cancelar</button></div>
                                    <p role="status" data-reply-status>@if((int) old('reply_to_post_id') === $post->id){{ $errors->first('body') ?: $errors->first('reply_to_post_id') }}@endif</p>
                                </form>
                            </details>
                        @endcan
                    @endauth
                </div>
                @if($question)
                    <div class="question-post-status">
                        @if($isAccepted)<span class="question-accepted">✓ Respuesta aceptada</span>@endif
                        @if(($post->is_initial ? $question->upvotes_count : $post->upvotes_count) > 0)
                            <span title="Personas que encontraron útil este mensaje">Útil · {{ number_format($post->is_initial ? $question->upvotes_count : $post->upvotes_count) }}</span>
                        @endif
                        @auth
                            @if($post->is_initial)
                                @can('vote', $question)<form method="POST" action="{{ route('questions.votes.store', $question) }}">@csrf<button type="submit" title="Marcar como útil">Me ayudó</button></form>@endcan
                            @else
                                @can('vote', $post)<form method="POST" action="{{ route('questions.answers.votes.store', $post) }}">@csrf<button type="submit" title="Marcar como útil">Me ayudó</button></form>@endcan
                                @can('acceptAnswer', $question)<form method="POST" action="{{ route('questions.answers.accept', [$question, $post]) }}">@csrf<button type="submit">{{ $isAccepted ? 'Aceptada' : 'Aceptar respuesta' }}</button></form>@endcan
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        @endif
    </div>
</article>
