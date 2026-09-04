@props(['post', 'previousUserId' => null, 'question' => null, 'isAccepted' => false])

@php($author = $post->author)
@php($resolvedRank = $author ? app(\App\Services\CommunityRankResolver::class)->resolve($author) : null)
<article id="post-{{ $post->id }}" class="forum-post {{ $post->is_hidden ? 'is-hidden' : '' }}">
    <aside class="forum-post-author">
        @if($author)
            <a href="{{ $author->publicProfileUrl() }}" class="forum-post-avatar" aria-label="Ver perfil de {{ $author->displayName() }}">
                @if($author->hasProfileAvatar())<img src="{{ $author->avatarUrl() }}" alt="Avatar de {{ $author->displayName() }}">@else<span>{{ $author->initials() }}</span>@endif
            </a>
            <a href="{{ $author->publicProfileUrl() }}" class="forum-post-name">{{ $author->displayName() }}</a>
            <x-community.rank :rank="$resolvedRank" />
            <x-community.user-badges :user="$author" :limit="2" />
            <dl class="forum-post-author-meta"><div><dt>Ingreso</dt><dd>{{ $author->show_join_date ? optional($author->communityJoinDate())->translatedFormat('M Y') : 'Privado' }}</dd></div><div><dt>Mensajes</dt><dd>{{ number_format($author->community_message_count) }}</dd></div></dl>
            @if($question)<small class="question-author-reputation">{{ number_format($author->community_reputation) }} reputación</small>@endif
        @else
            <span class="forum-post-avatar"><span>?</span></span><span class="forum-post-name">{{ $post->authorName() }}</span>
        @endif
    </aside>
    <div class="forum-post-content">
        <header class="forum-post-header"><a href="#post-{{ $post->id }}">{{ $post->created_at->timezone('America/Merida')->translatedFormat('d M Y · HH:mm') }}</a>@if($post->edited_at)<span>Editado</span>@endif</header>
        @if($post->is_hidden)
            <p class="forum-hidden-message">Este mensaje está oculto por moderación.</p>
        @else
            <div class="forum-post-body">{!! nl2br(e($post->body)) !!}</div>
            @if($question)
                <div class="question-post-status">
                    @if($isAccepted)<span class="question-accepted">✓ Respuesta aceptada</span>@endif
                    <span>{{ number_format($post->is_initial ? $question->upvotes_count : $post->upvotes_count) }} votos positivos</span>
                    @auth
                        @if($post->is_initial)
                            @can('vote', $question)<form method="POST" action="{{ route('questions.votes.store', $question) }}">@csrf<button type="submit">Votar</button></form>@endcan
                        @else
                            @can('vote', $post)<form method="POST" action="{{ route('questions.answers.votes.store', $post) }}">@csrf<button type="submit">Votar</button></form>@endcan
                            @can('acceptAnswer', $question)<form method="POST" action="{{ route('questions.answers.accept', [$question, $post]) }}">@csrf<button type="submit">{{ $isAccepted ? 'Aceptada' : 'Aceptar respuesta' }}</button></form>@endcan
                        @endif
                    @endauth
                </div>
            @endif
            @if($author)<x-community.signature :user="$author" :previous-user-id="$previousUserId" />@endif
        @endif
        @auth
            <footer class="forum-post-actions">
                @can('update', $post)<a href="{{ route('forum.posts.edit', $post) }}">Editar</a>@endcan
                @can('delete', $post)<form method="POST" action="{{ route('forum.posts.destroy', $post) }}">@csrf @method('DELETE')<button type="submit" onclick="return confirm('¿Eliminar este mensaje?')">Eliminar</button></form>@endcan
                @can('moderate', $post)<form method="POST" action="{{ route('forum.moderation.post.hide', $post) }}">@csrf @method('PATCH')<button type="submit">Ocultar</button></form>@endcan
            </footer>
        @endauth
    </div>
</article>
