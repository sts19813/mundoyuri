@php($portalFavicon = asset('assets/img/logos/favicon-flor.png').'?v='.filemtime(public_path('assets/img/logos/favicon-flor.png')))
<link rel="icon" type="image/png" sizes="128x128" href="{{ $portalFavicon }}">
<link rel="shortcut icon" href="{{ $portalFavicon }}">
<link rel="apple-touch-icon" href="{{ $portalFavicon }}">
