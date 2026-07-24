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
    $usesDefaultShareImage = blank($image);
    $shareImage = $image ?: asset('assets/img/social/mundo-yuri-og.jpg');
    $pageTitle = str_contains($title, $siteName) ? $title : $title.' | '.$siteName;
    $defaultSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $pageTitle,
        'description' => $description,
        'url' => $canonicalUrl,
        'inLanguage' => 'es-MX',
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
<meta property="og:image:secure_url" content="{{ $shareImage }}">
<meta property="og:image:alt" content="{{ $pageTitle }}">
@if($usesDefaultShareImage)
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $shareImage }}">
<meta name="twitter:image:alt" content="{{ $pageTitle }}">

<script type="application/ld+json">{!! json_encode($schema ?: $defaultSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
