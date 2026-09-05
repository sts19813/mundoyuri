@props(['author', 'showReputation' => false, 'inlineBadges' => false, 'showSignature' => true])

@php
    $resolvedRank = $author ? app(\App\Services\CommunityRankResolver::class)->resolve($author) : null;
    $viewer = auth()->user();
    $viewer?->loadMissing(['blockedUsers:id', 'blockedByUsers:id']);
    $blocked = $author && $viewer && ($viewer->blockedUsers->contains('id', $author->id) || $viewer->blockedByUsers->contains('id', $author->id));
    $canInspect = $author && !$blocked && \Illuminate\Support\Facades\Gate::allows('viewProfile', $author);
@endphp
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
        @if($inlineBadges && $canInspect)
            <x-community.rank :rank="$resolvedRank" />
            <x-community.user-badges :user="$author" />
        @endif
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
                @if($showReputation)
                    <div>
                        <dt>Reputación</dt>
                        <dd>{{ number_format($author->community_reputation) }}</dd>
                    </div>
                @endif
            </dl>
            @if($showSignature)
                <x-community.signature :user="$author" :previous-user-id="null" />
            @endif
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
