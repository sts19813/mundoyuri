@props(['rank'])

@if($rank)
    <span class="community-rank" style="--community-accent: {{ $rank->color ?: '#f472b6' }}">
        {{ $rank->name }}
    </span>
@endif
