@props([
    'src' => null,
    'type' => 'image',
    'alt' => '',
    'class' => '',
    'hoverPlay' => false,
    'autoplay' => false,
])

@if($type === 'video' && $src)
    <video
        {!! $attributes->merge([
            'class' => trim($class.' '.($hoverPlay ? 'js-hover-preview' : '')),
            'muted' => true,
            'loop' => true,
            'playsinline' => true,
            'preload' => 'metadata',
        ]) !!}
        @if($autoplay) autoplay @endif
    >
        <source src="{{ $src }}" type="video/{{ pathinfo(parse_url($src, PHP_URL_PATH) ?: $src, PATHINFO_EXTENSION) }}">
    </video>
@else
    <img {!! $attributes->merge(['class' => $class, 'src' => $src, 'alt' => $alt]) !!}>
@endif
