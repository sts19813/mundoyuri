@props(['badge', 'detailed' => false, 'compact' => false])

<span
    @class(['community-badge', 'community-badge-tooltip' => $detailed, 'community-badge-compact' => $compact])
    style="--community-accent: {{ $badge->color ?: '#f472b6' }}"
    title="{{ $badge->description ?: $badge->name }}"
    @if($compact) aria-label="{{ $badge->name }}: {{ $badge->description ?: 'Reconocimiento de la comunidad Mundo Yuri.' }}" @endif
    @if($detailed) tabindex="0" aria-describedby="badge-description-{{ $badge->id }}" @endif
>
    @if($badge->imageUrl())
        <img src="{{ $badge->imageUrl() }}" alt="" class="community-badge-image">
    @else
        <span aria-hidden="true">{{ $badge->icon ?: '✦' }}</span>
    @endif
    @unless($compact){{ $badge->name }}@endunless
    @if($detailed)
        <span class="community-badge-tooltip-copy" id="badge-description-{{ $badge->id }}" role="tooltip">
            <strong>{{ $badge->name }}</strong>
            <span>{{ $badge->description ?: 'Reconocimiento de la comunidad Mundo Yuri.' }}</span>
        </span>
    @endif
</span>
