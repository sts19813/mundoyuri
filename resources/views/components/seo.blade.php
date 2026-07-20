@props([
    'title',
    'description',
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
    'schema' => null,
])
@php
    $siteName = 'Mundo Yuri';
    $canonicalUrl = $canonical ?: url()->current();
    $shareImage = $image ?: asset('assets/img/logos/Logo Mundo yuri Original.png');
    $pageTitle = str_contains($title, $siteName) ? $title : $title.' | '.$siteName;
    $defaultSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $pageTitle,
        'description' => $description,
        'url' => $canonicalUrl,
        'inLanguage' => 'es',
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => route('home'),
        ],
    ];
@endphp
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<meta name="googlebot" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:locale" content="es_MX">
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $shareImage }}">
<meta property="og:image:alt" content="{{ $pageTitle }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $shareImage }}">

<script type="application/ld+json">{!! json_encode($schema ?: $defaultSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
