@props(['member'])

@php
    $isHistoricalProfile = $member instanceof \App\Models\LegacyProfile;
    $profileUrl = $isHistoricalProfile ? route('legacy-profiles.show', $member) : $member->publicProfileUrl();
    $memberName = $isHistoricalProfile ? $member->nickname : $member->name;
    $hasAvatar = $isHistoricalProfile ? filled($member->avatarUrl()) : $member->hasProfileAvatar();
@endphp

<a class="community-member-chip" href="{{ $profileUrl }}" aria-label="Ver perfil de {{ $memberName }}">
    <span class="forum-post-avatar" aria-hidden="true">
        @if($hasAvatar)
            <img src="{{ $member->avatarUrl() }}" alt="">
        @else
            <span>{{ $isHistoricalProfile ? mb_strtoupper(mb_substr($memberName, 0, 1)) : $member->initials() }}</span>
        @endif
    </span>
    <span class="forum-post-name">{{ $memberName }}</span>
</a>
