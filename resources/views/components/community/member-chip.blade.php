@props(['member'])

@php
    $isHistoricalProfile = $member instanceof \App\Models\LegacyProfile;
    $profileUrl = $isHistoricalProfile ? route('legacy-profiles.show', $member) : $member->publicProfileUrl();
    $memberName = $isHistoricalProfile ? $member->nickname : $member->name;
@endphp

@if(! $isHistoricalProfile)
    @php
        $resolvedRank = app(\App\Services\CommunityRankResolver::class)->resolve($member);
        $viewer = auth()->user();
        $viewer?->loadMissing(['blockedUsers:id', 'blockedByUsers:id']);
        $blocked = $viewer && ($viewer->blockedUsers->contains('id', $member->id) || $viewer->blockedByUsers->contains('id', $member->id));
        $canInspect = !$blocked && \Illuminate\Support\Facades\Gate::allows('viewProfile', $member);
    @endphp

    <details class="forum-post-author-compact" data-author-card>
        <summary aria-label="Información de {{ $memberName }}">
            <span class="forum-post-avatar">
                @if($member->hasProfileAvatar())
                    <img src="{{ $member->avatarUrl() }}" alt="Avatar de {{ $memberName }}">
                @else
                    <span>{{ $member->initials() }}</span>
                @endif
            </span>
            <span class="forum-post-name">{{ $memberName }}</span>
        </summary>

        <aside class="forum-author-popover" aria-label="Información de {{ $memberName }}">
            <div class="forum-author-popover-top">
                <a href="{{ $profileUrl }}" class="forum-author-popover-avatar" tabindex="-1" aria-hidden="true">
                    @if($member->hasProfileAvatar())
                        <img src="{{ $member->avatarUrl() }}" alt="">
                    @else
                        <span>{{ $member->initials() }}</span>
                    @endif
                </a>
                <div>
                    <a href="{{ $profileUrl }}" class="forum-author-popover-name">{{ $member->displayName() }}</a>
                    <x-community.rank :rank="$resolvedRank" />
                </div>
            </div>
            @if($canInspect)
                <div class="forum-author-badges"><x-community.user-badges :user="$member" :limit="5" /></div>
                <dl class="forum-author-popover-meta">
                    <div>
                        <dt>Miembro desde</dt>
                        <dd>{{ $member->show_join_date ? optional($member->communityJoinDate())->translatedFormat('d M Y') : 'Privado' }}</dd>
                    </div>
                    <div>
                        <dt>Mensajes</dt>
                        <dd>{{ number_format($member->community_message_count) }}</dd>
                    </div>
                </dl>
            @else
                <p class="forum-author-private">La información de este perfil no está disponible.</p>
            @endif
            <div class="forum-author-popover-actions">
                <a href="{{ $profileUrl }}">Ver perfil</a>
                @auth
                    @if(!$blocked && !$viewer->is($member) && $member->is_active)
                        <a href="{{ route('messages.show', $member) }}">Escribir</a>
                    @endif
                @endauth
            </div>
        </aside>
    </details>
@else
    <details class="forum-post-author-compact" data-author-card>
        <summary aria-label="Información de {{ $memberName }}">
            <span class="forum-post-avatar">
                @if($member->avatarUrl())
                    <img src="{{ $member->avatarUrl() }}" alt="Avatar histórico de {{ $memberName }}">
                @else
                    <span>{{ mb_strtoupper(mb_substr($memberName, 0, 1)) }}</span>
                @endif
            </span>
            <span class="forum-post-name">{{ $memberName }}</span>
        </summary>

        <aside class="forum-author-popover" aria-label="Información de {{ $memberName }}">
            <div class="forum-author-popover-top">
                <a href="{{ $profileUrl }}" class="forum-author-popover-avatar" tabindex="-1" aria-hidden="true">
                    @if($member->avatarUrl())
                        <img src="{{ $member->avatarUrl() }}" alt="">
                    @else
                        <span>{{ mb_strtoupper(mb_substr($memberName, 0, 1)) }}</span>
                    @endif
                </a>
                <div>
                    <a href="{{ $profileUrl }}" class="forum-author-popover-name">{{ $memberName }}</a>
                    @if($member->legacy_rank)
                        <span class="community-rank">{{ $member->legacy_rank }}</span>
                    @endif
                </div>
            </div>
            @if($member->badges->isNotEmpty())
                <div class="forum-author-badges">
                    @foreach($member->badges->take(5) as $badge)
                        <x-community.badge :badge="$badge" />
                    @endforeach
                </div>
            @endif
            <dl class="forum-author-popover-meta">
                <div>
                    <dt>Registro histórico</dt>
                    <dd>{{ $member->legacy_joined_at?->translatedFormat('d M Y') ?: 'Sin fecha' }}</dd>
                </div>
                <div>
                    <dt>Mensajes archivados</dt>
                    <dd>{{ number_format($member->legacy_message_count) }}</dd>
                </div>
            </dl>
            <div class="forum-author-popover-actions">
                <a href="{{ $profileUrl }}">Ver perfil</a>
            </div>
        </aside>
    </details>
@endif
