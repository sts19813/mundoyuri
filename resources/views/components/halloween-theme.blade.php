@props(['theme' => 'halloween'])

<div class="season-theme season-theme--{{ $theme }}" data-season-theme="{{ $theme }}" aria-hidden="true">
@if($theme === 'halloween')
    @foreach(['one', 'two'] as $variant)
        <x-season-creature kind="spider" :variant="$variant" />
    @endforeach
    @foreach(['one', 'two', 'three', 'four', 'five'] as $variant)
        <x-season-creature kind="ant" :variant="$variant" />
    @endforeach
@elseif($theme === 'christmas')
    <div class="season-garland">✦ &nbsp; ● &nbsp; ✦ &nbsp; ● &nbsp; ✦ &nbsp; ● &nbsp; ✦</div><div class="season-particles">❄ ❅ ❄ ❅ ❄ ❅ ❄ ❅ ❄</div>
@elseif($theme === 'spring')
    <div class="season-particles">✿ ❀ ✿ ❀ ✿ ❀ ✿ ❀ ✿</div><div class="season-vine">❀</div>
@elseif($theme === 'summer')
    <div class="season-sun">☀</div><div class="season-particles">✦ · ✦ · ✦ · ✦</div>
@else
    <div class="season-particles">❦ ❧ ❦ ❧ ❦ ❧ ❦ ❧</div>
@endif
</div>

@if($theme === 'halloween')
<div class="halloween-glitch" aria-hidden="true" hidden><i></i><i></i><i></i></div>
@endif
