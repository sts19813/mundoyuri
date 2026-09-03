@props(['rank'])

@if($rank)
    <span @class([
        'community-rank',
        'community-rank-legacy' => $rank->is_legacy,
        $rank->css_class => filled($rank->css_class),
    ]) style="--community-accent: {{ $rank->color ?: '#f472b6' }}" title="{{ $rank->description }}">
        @if($rank->icon)<span aria-hidden="true">{{ $rank->icon }}</span>@endif
        {{ $rank->name }}
    </span>
@endif
