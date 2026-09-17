@props([
    'src' => null,
    'type' => 'image',
    'alt' => '',
    'class' => '',
    'hoverPlay' => false,
    'autoplay' => false,
    'lazy' => true,
])

@php
    $shouldLazy = $lazy && ! $autoplay && filled($src);
    $placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';
@endphp

@if($type === 'video' && $src)
    <video
        {!! $attributes->merge([
            'class' => trim($class.' '.($hoverPlay ? 'js-hover-preview' : '').' '.($shouldLazy ? 'js-lazy-media' : '')),
            'muted' => true,
            'loop' => true,
            'playsinline' => true,
            'preload' => $autoplay ? 'metadata' : 'none',
        ]) !!}
        @if($autoplay) autoplay @endif
    >
        <source @if($shouldLazy) data-src="{{ $src }}" @else src="{{ $src }}" @endif type="video/{{ pathinfo(parse_url($src, PHP_URL_PATH) ?: $src, PATHINFO_EXTENSION) }}">
    </video>
@else
    <img
        {!! $attributes->merge([
            'class' => trim($class.' '.($shouldLazy ? 'js-lazy-media' : '')),
            'src' => $shouldLazy ? $placeholder : $src,
            'data-src' => $shouldLazy ? $src : null,
            'alt' => $alt,
            'loading' => $shouldLazy ? 'lazy' : 'eager',
            'decoding' => 'async',
            'fetchpriority' => $shouldLazy ? 'low' : 'auto',
        ]) !!}
    >
@endif
