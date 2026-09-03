@props(['badge'])

<span class="community-badge" style="--community-accent: {{ $badge->color ?: '#f472b6' }}" title="{{ $badge->description }}">
    @if($badge->icon)<span aria-hidden="true">{{ $badge->icon }}</span>@endif
    {{ $badge->name }}
</span>
