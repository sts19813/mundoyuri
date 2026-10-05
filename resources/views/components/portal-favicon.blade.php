@php($portalFaviconSvg = asset('favicon.svg').'?v='.filemtime(public_path('favicon.svg')))
@php($portalFavicon32 = asset('assets/img/pwa/favicon-32.png').'?v='.filemtime(public_path('assets/img/pwa/favicon-32.png')))
@php($portalFavicon16 = asset('assets/img/pwa/favicon-16.png').'?v='.filemtime(public_path('assets/img/pwa/favicon-16.png')))
@php($appleTouchIcon = asset('assets/img/pwa/apple-touch-icon.png').'?v='.filemtime(public_path('assets/img/pwa/apple-touch-icon.png')))
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#e83691">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Mundo Yuri">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<link rel="icon" href="{{ asset('favicon.ico') }}?v={{ filemtime(public_path('favicon.ico')) }}" sizes="any">
<link rel="icon" type="image/svg+xml" href="{{ $portalFaviconSvg }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ $portalFavicon32 }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ $portalFavicon16 }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ filemtime(public_path('favicon.ico')) }}">
<link rel="apple-touch-icon" href="{{ $appleTouchIcon }}">
