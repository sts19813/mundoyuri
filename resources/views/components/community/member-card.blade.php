@props(['member', 'rankResolver'])

@php($isHistoricalProfile = $member instanceof \App\Models\LegacyProfile)
@php($profileUrl = $isHistoricalProfile ? route('legacy-profiles.show', $member) : $member->publicProfileUrl())
@php($memberName = $isHistoricalProfile ? $member->nickname : $member->displayName())
@php($soulmateLabel = $isHistoricalProfile && $member->legacySoulmate ? 'Alma gemela: '.str($member->legacySoulmate->nickname)->replace('~', '') : null)
@php($memberCover = ! $isHistoricalProfile ? $member->coverImageUrl() : null)
@php($viewer = auth()->user())
@php($viewer?->loadMissing(['blockedUsers:id', 'blockedByUsers:id']))
@php($blocked = ! $isHistoricalProfile && $viewer && ($viewer->blockedUsers->contains('id', $member->id) || $viewer->blockedByUsers->contains('id', $member->id)))
@php($canInspect = ! $isHistoricalProfile && ! $blocked && \Illuminate\Support\Facades\Gate::allows('viewProfile', $member))
@php($canWrite = ! $isHistoricalProfile && $viewer && ! $blocked && ! $viewer->is($member) && $member->is_active)
@php($resolvedRank = ! $isHistoricalProfile ? $rankResolver->resolve($member) : null)
@php($streakDays = ! $isHistoricalProfile ? $member->activeStreakDays() : 0)

<article @class(['community-member-card', 'has-soulmate' => filled($soulmateLabel), 'has-cover' => filled($memberCover)]) @if($memberCover) style="--member-cover: url('{{ $memberCover }}'); --member-cover-position: {{ $member->coverImagePosition() }}" @endif @if($soulmateLabel) data-soulmate="{{ $soulmateLabel }}" title="{{ $soulmateLabel }}" @endif>
    <a @class(['community-member-avatar', 'is-online' => ! $isHistoricalProfile && $member->isOnline()]) href="{{ $profileUrl }}" aria-label="Ver perfil de {{ $memberName }}">
        @if($isHistoricalProfile && $member->avatarUrl())
            <img src="{{ $member->avatarUrl() }}" alt="Avatar histórico de {{ $member->nickname }}">
        @elseif(! $isHistoricalProfile && $member->hasProfileAvatar())
            <img src="{{ $member->avatarUrl() }}" alt="Foto de perfil de {{ $member->displayName() }}">
        @else
            <span>{{ $isHistoricalProfile ? mb_strtoupper(mb_substr($member->nickname, 0, 1)) : $member->initials() }}</span>
        @endif
    </a>
    <div class="community-member-copy">
        <div class="community-member-title">
            <div>
                <h3><a href="{{ $profileUrl }}">{{ $isHistoricalProfile ? $member->nickname : $member->name }}</a></h3>
                @if($isHistoricalProfile)<small>Perfil histórico</small>@elseif($member->alias)<small>{{ '@'.$member->alias }}</small>@endif
            </div>
            @if($isHistoricalProfile)
                @if($member->legacy_rank)<span class="community-rank">{{ $member->legacy_rank }}</span>@endif
            @else
                <x-community.rank :rank="$resolvedRank" />
            @endif
        </div>

        <div class="community-member-badges">
            @if($member->badges->isNotEmpty())<span class="community-member-badges-label">Insignias</span>@endif
            @foreach($member->badges as $badge)<x-community.badge :badge="$badge" compact />@endforeach
            @if(! $isHistoricalProfile && $member->is_legacy && $member->badges->doesntContain('slug', 'miembro-historico'))<span class="community-badge community-badge-legacy community-badge-compact" title="Miembro histórico" aria-label="Miembro histórico"><span aria-hidden="true">🌸</span></span>@endif
        </div>

        <dl class="community-member-stats">
            <div>
                <dt>Ingreso</dt>
                <dd>{{ $isHistoricalProfile ? $member->legacy_joined_at?->translatedFormat('d M Y') : ($member->show_join_date ? optional($member->communityJoinDate())->translatedFormat('d M Y') : 'Fecha privada') }}</dd>
            </div>
            <div>
                <dt>{{ $isHistoricalProfile ? 'Mensajes archivados' : 'Publicaciones' }}</dt>
                <dd>{{ number_format($isHistoricalProfile ? $member->legacy_message_count : $member->community_message_count) }}</dd>
            </div>
        </dl>
    </div>

    <aside
        @class(['forum-author-popover', 'community-member-popover', 'has-cover' => filled($memberCover)])
        @if($memberCover) style="--author-cover: url('{{ $memberCover }}'); --author-cover-position: {{ $member->coverImagePosition() }}" @endif
        aria-label="Información de {{ $memberName }}"
    >
        <div class="forum-author-popover-top">
            <a href="{{ $profileUrl }}" @class(['forum-author-popover-avatar', 'is-online' => ! $isHistoricalProfile && $member->isOnline()]) tabindex="-1" aria-hidden="true">
                @if($isHistoricalProfile && $member->avatarUrl())
                    <img src="{{ $member->avatarUrl() }}" alt="">
                @elseif(! $isHistoricalProfile && $member->hasProfileAvatar())
                    <img src="{{ $member->avatarUrl() }}" alt="">
                @else
                    <span>{{ $isHistoricalProfile ? mb_strtoupper(mb_substr($memberName, 0, 1)) : $member->initials() }}</span>
                @endif
            </a>
            <div>
                <a href="{{ $profileUrl }}" class="forum-author-popover-name">{{ $memberName }}</a>
                @if($isHistoricalProfile)
                    @if($member->legacy_rank)<span class="community-rank">{{ $member->legacy_rank }}</span>@endif
                @else
                    <x-community.rank :rank="$resolvedRank" />
                @endif
            </div>
        </div>
        @if($canInspect || $isHistoricalProfile)
            @if($member->badges->isNotEmpty())
                <div class="forum-author-badges"><span class="forum-author-badges-label">Insignias</span>
                    @foreach($member->badges->take(5) as $badge)<x-community.badge :badge="$badge" compact />@endforeach
                </div>
            @endif
            <dl class="forum-author-popover-meta">
                <div><dt>{{ $isHistoricalProfile ? 'Registro histórico' : 'Miembro desde' }}</dt><dd>{{ $isHistoricalProfile ? ($member->legacy_joined_at?->translatedFormat('d M Y') ?: 'Sin fecha') : ($member->show_join_date ? optional($member->communityJoinDate())->translatedFormat('d M Y') : 'Privado') }}</dd></div>
                <div><dt>{{ $isHistoricalProfile ? 'Mensajes archivados' : 'Mensajes' }}</dt><dd>{{ number_format($isHistoricalProfile ? $member->legacy_message_count : $member->community_message_count) }}</dd></div>
                @unless($isHistoricalProfile)<div title="Mejor racha: {{ $member->longest_streak_days }} {{ $member->longest_streak_days === 1 ? 'día' : 'días' }}"><dt>Racha</dt><dd>🔥 {{ $streakDays }} {{ $streakDays === 1 ? 'día' : 'días' }}</dd></div>@endunless
            </dl>
            @if(! $isHistoricalProfile)<x-community.signature :user="$member" :previous-user-id="null" />
            @elseif(filled($member->legacy_signature_text))<footer class="community-post-signature" aria-label="Firma de {{ $memberName }}"><p>{{ $member->legacy_signature_text }}</p></footer>@endif
        @else
            <p class="forum-author-private">La información de este perfil no está disponible.</p>
        @endif
        <div class="forum-author-popover-actions">
            <a href="{{ $profileUrl }}">Ver perfil</a>
            @if($canWrite)<a class="is-primary" href="{{ route('messages.show', $member) }}">Escribir</a>@endif
        </div>
    </aside>
</article>
