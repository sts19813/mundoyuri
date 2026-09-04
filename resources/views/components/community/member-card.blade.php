@props(['member', 'rankResolver'])

@php($isHistoricalProfile = $member instanceof \App\Models\LegacyProfile)
@php($profileUrl = $isHistoricalProfile ? route('legacy-profiles.show', $member) : $member->publicProfileUrl())
@php($memberName = $isHistoricalProfile ? $member->nickname : $member->displayName())

<article class="community-member-card">
    <a class="community-member-avatar" href="{{ $profileUrl }}" aria-label="Ver perfil de {{ $memberName }}">
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
                <x-community.rank :rank="$rankResolver->resolve($member)" />
            @endif
        </div>

        <div class="community-member-badges">
            @foreach($member->badges as $badge)<x-community.badge :badge="$badge" />@endforeach
            @if(! $isHistoricalProfile && $member->is_legacy && $member->badges->doesntContain('slug', 'miembro-historico'))<span class="community-badge community-badge-legacy"><span aria-hidden="true">🌸</span> Miembro histórico</span>@endif
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
</article>
