@props(['member', 'class' => ''])

@php
    $historical = $member instanceof \App\Models\LegacyProfile;
    $name = $historical ? $member->nickname : $member->displayName();
    $hasAvatar = $historical ? filled($member->avatarUrl()) : $member->hasProfileAvatar();
@endphp
<span @class(['community-avatar', $class, 'is-online' => ! $historical && $member->isOnline()]) aria-hidden="true">
    <span class="community-avatar-image">
        @if($hasAvatar)
            <img src="{{ $member->avatarUrl() }}" alt="" @unless($historical) style="object-position: {{ $member->profileImagePosition() }}" @endunless>
        @else
            <span>{{ $historical ? mb_strtoupper(mb_substr($name, 0, 1)) : $member->initials() }}</span>
        @endif
    </span>
</span>
