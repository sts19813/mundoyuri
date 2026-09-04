@props(['badge', 'detailed' => false])

<span
    @class(['community-badge', 'community-badge-tooltip' => $detailed])
    style="--community-accent: {{ $badge->color ?: '#f472b6' }}"
    title="{{ $badge->description ?: $badge->name }}"
    @if($detailed) tabindex="0" aria-describedby="badge-description-{{ $badge->id }}" @endif
>
    @if($badge->icon)<span aria-hidden="true">{{ $badge->icon }}</span>@endif
    {{ $badge->name }}
    @if($detailed)
        <span class="community-badge-tooltip-copy" id="badge-description-{{ $badge->id }}" role="tooltip">
            <strong>{{ $badge->name }}</strong>
            <span>{{ $badge->description ?: 'Reconocimiento de la comunidad Mundo Yuri.' }}</span>
        </span>
    @endif
</span>
