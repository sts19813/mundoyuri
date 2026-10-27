@props(['theme' => 'halloween'])

<div class="season-theme season-theme--{{ $theme }}" data-season-theme="{{ $theme }}" aria-hidden="true">
@if($theme === 'halloween')
    @foreach(['one', 'two'] as $variant)
        <x-season-creature kind="spider" :variant="$variant" />
    @endforeach
    @foreach(['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight'] as $variant)
        <x-season-creature kind="ant" :variant="$variant" />
    @endforeach
    <div class="halloween-pumpkin-rain" data-halloween-pumpkin-rain
        data-pumpkin-src="{{ asset('assets/img/seasonal/pumpkin-anime-grin.webp') }}"
        data-pumpkin-alt-src="{{ asset('assets/img/seasonal/pumpkin-anime-smile.webp') }}">
        @for($particle = 0; $particle < 12; $particle++)
            <img class="halloween-pumpkin" data-halloween-pumpkin width="192" height="192" alt="" decoding="async" draggable="false" hidden>
        @endfor
    </div>
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
