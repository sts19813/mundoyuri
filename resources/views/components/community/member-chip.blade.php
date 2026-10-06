@props(['member'])

@php
    $isHistoricalProfile = $member instanceof \App\Models\LegacyProfile;
    $profileUrl = $isHistoricalProfile ? route('legacy-profiles.show', $member) : $member->publicProfileUrl();
    $memberName = $isHistoricalProfile ? $member->nickname : $member->name;
    $hasAvatar = $isHistoricalProfile ? filled($member->avatarUrl()) : $member->hasProfileAvatar();
    $resolvedRank = $isHistoricalProfile ? null : app(\App\Services\CommunityRankResolver::class)->resolve($member);
@endphp

<details class="forum-post-author-compact community-member-chip" data-author-card data-member-chip data-profile-url="{{ $profileUrl }}">
    <summary aria-label="Ver perfil de {{ $memberName }}">
        <span class="forum-post-avatar" aria-hidden="true">
        @if($hasAvatar)
            <img src="{{ $member->avatarUrl() }}" alt="">
        @else
            <span>{{ $isHistoricalProfile ? mb_strtoupper(mb_substr($memberName, 0, 1)) : $member->initials() }}</span>
        @endif
        </span>
        <span class="forum-post-name">{{ $memberName }}</span>
    </summary>

    <aside class="forum-author-popover" aria-label="Información de {{ $memberName }}">
        <div class="forum-author-popover-top">
            <span class="forum-author-popover-avatar" aria-hidden="true">
                @if($hasAvatar)
                    <img src="{{ $member->avatarUrl() }}" alt="">
                @else
                    <span>{{ $isHistoricalProfile ? mb_strtoupper(mb_substr($memberName, 0, 1)) : $member->initials() }}</span>
                @endif
            </span>
            <div>
                <strong class="forum-author-popover-name">{{ $memberName }}</strong>
                @if($isHistoricalProfile)
                    @if($member->legacy_rank)<span class="community-rank">{{ $member->legacy_rank }}</span>@endif
                @else
                    <x-community.rank :rank="$resolvedRank" />
                @endif
            </div>
        </div>
        @if($member->badges->isNotEmpty())
            <div class="forum-author-badges">
                @foreach($member->badges->take(5) as $badge)<x-community.badge :badge="$badge" />@endforeach
            </div>
        @endif
        <dl class="forum-author-popover-meta">
            <div>
                <dt>{{ $isHistoricalProfile ? 'Registro histórico' : 'Miembro desde' }}</dt>
                <dd>{{ $isHistoricalProfile ? ($member->legacy_joined_at?->translatedFormat('d M Y') ?: 'Sin fecha') : ($member->show_join_date ? optional($member->communityJoinDate())->translatedFormat('d M Y') : 'Privado') }}</dd>
            </div>
            <div>
                <dt>{{ $isHistoricalProfile ? 'Mensajes archivados' : 'Mensajes' }}</dt>
                <dd>{{ number_format($isHistoricalProfile ? $member->legacy_message_count : $member->community_message_count) }}</dd>
            </div>
        </dl>
    </aside>
</details>
