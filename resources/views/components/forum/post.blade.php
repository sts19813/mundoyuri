@props(['post', 'previousUserId' => null, 'question' => null, 'isAccepted' => false])

@php
    $author = $post->author;
    $resolvedRank = $author ? app(\App\Services\CommunityRankResolver::class)->resolve($author) : null;
    $viewer = auth()->user();
    $viewer?->loadMissing(['blockedUsers:id', 'blockedByUsers:id']);
    $blocked = $author && $viewer && ($viewer->blockedUsers->contains('id', $author->id) || $viewer->blockedByUsers->contains('id', $author->id));
    $canInspect = $author && !$blocked && \Illuminate\Support\Facades\Gate::allows('viewProfile', $author);
@endphp

<article id="post-{{ $post->id }}" class="forum-post {{ $post->is_hidden ? 'is-hidden' : '' }}">
    <header class="forum-post-header">
        @if($author)
            <details class="forum-post-author-compact" data-author-card>
                <summary aria-label="Información de {{ $author->displayName() }}">
                <span class="forum-post-avatar">
                    @if($author->hasProfileAvatar())
                        <img src="{{ $author->avatarUrl() }}" alt="Avatar de {{ $author->displayName() }}">
                    @else
                        <span>{{ $author->initials() }}</span>
                    @endif
                </span>
                <span class="forum-post-name">{{ $author->displayName() }}</span>
                </summary>

                <aside class="forum-author-popover" aria-label="Información de {{ $author->displayName() }}">
                    <div class="forum-author-popover-top">
                        <a href="{{ $author->publicProfileUrl() }}" class="forum-author-popover-avatar" tabindex="-1" aria-hidden="true">
                            @if($author->hasProfileAvatar())
                                <img src="{{ $author->avatarUrl() }}" alt="">
                            @else
                                <span>{{ $author->initials() }}</span>
                            @endif
                        </a>
                        <div>
                            <a href="{{ $author->publicProfileUrl() }}" class="forum-author-popover-name">{{ $author->displayName() }}</a>
                            <x-community.rank :rank="$resolvedRank" />
                        </div>
                    </div>
                    @if($canInspect)
                    <div class="forum-author-badges"><x-community.user-badges :user="$author" :limit="5" /></div>
                    <dl class="forum-author-popover-meta">
                        <div>
                            <dt>Miembro desde</dt>
                            <dd>{{ $author->show_join_date ? optional($author->communityJoinDate())->translatedFormat('d M Y') : 'Privado' }}</dd>
                        </div>
                        <div>
                            <dt>Mensajes</dt>
                            <dd>{{ number_format($author->community_message_count) }}</dd>
                        </div>
                        @if($question)
                            <div>
                                <dt>Reputación</dt>
                                <dd>{{ number_format($author->community_reputation) }}</dd>
                            </div>
                        @endif
                    </dl>
                    <x-community.signature :user="$author" :previous-user-id="null" />
                    @else
                        <p class="forum-author-private">La información de este perfil no está disponible.</p>
                    @endif
                    <div class="forum-author-popover-actions">
                        <a href="{{ $author->publicProfileUrl() }}">Ver perfil</a>
                        @auth
                            @if(!$blocked && !$viewer->is($author) && $author->is_active)
                                <a href="{{ route('messages.show', $author) }}">Escribir</a>
                            @endif
                        @endauth
                    </div>
                </aside>
            </details>
        @else
            <div class="forum-post-author-compact">
                <span class="forum-post-avatar"><span>?</span></span>
                <span class="forum-post-name">{{ $post->authorName() }}</span>
            </div>
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
            <x-community.reactions :reactable="$question && $post->is_initial ? $question : $post" />
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
        @endif
    </div>
</article>
