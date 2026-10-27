@php
    if (!($seasonThemeResolved ?? false)) {
        $activeSeasonTheme = \App\Models\SeasonThemeSetting::activeTheme();
    }
@endphp
<!DOCTYPE html>
@hasSection('html_attributes')
<html @yield('html_attributes') @if($activeSeasonTheme) data-season-palette="{{ $activeSeasonTheme }}" @endif>
@else
<html lang="es" @if($activeSeasonTheme) data-season-palette="{{ $activeSeasonTheme }}" @endif>
@endif
<head>
    <x-google-tag-manager />
    <x-google-analytics />
    <link rel="stylesheet" href="{{ asset('assets/css/dialogs.css') }}?v={{ filemtime(public_path('assets/css/dialogs.css')) }}">
    @yield('head')
    @if($activeSeasonTheme)
        @php($seasonThemeCss = $activeSeasonTheme === 'halloween' ? 'assets/css/halloween-theme.css' : 'assets/css/season-themes.css')
        <link rel="stylesheet" href="{{ asset($seasonThemeCss) }}?v={{ filemtime(public_path($seasonThemeCss)) }}">
        <link rel="stylesheet" href="{{ asset('assets/css/season-palette.css') }}?v={{ filemtime(public_path('assets/css/season-palette.css')) }}">
    @endif
</head>
@hasSection('body_attributes')
<body @yield('body_attributes')>
@else
<body>
@endif
    <x-google-tag-manager-noscript />
    @yield('body')
    @if($activeSeasonTheme)
        <x-halloween-theme :theme="$activeSeasonTheme" />
    @endif
    <script src="{{ asset('assets/js/lazy-media.js') }}?v={{ filemtime(public_path('assets/js/lazy-media.js')) }}" defer></script>
    <script src="{{ asset('assets/js/dialogs.js') }}?v={{ filemtime(public_path('assets/js/dialogs.js')) }}" defer></script>
    @php($pwaConfig = [
        'csrfToken' => csrf_token(),
        'pushPublicKey' => config('webpush.vapid.public_key'),
        'pushEnabled' => auth()->check() ? (bool) auth()->user()->push_notifications_enabled : false,
        'routes' => auth()->check() ? [
            'pushStore' => route('push-subscriptions.store'),
            'pushDestroy' => route('push-subscriptions.destroy'),
            'pushPreference' => route('push-subscriptions.preference'),
        ] : null,
    ])
    <script id="mundo-yuri-pwa-config" type="application/json">@json($pwaConfig)</script>
    <script src="{{ asset('assets/js/pwa.js') }}?v={{ filemtime(public_path('assets/js/pwa.js')) }}" defer></script>
    @if($activeSeasonTheme === 'halloween')
        <script src="{{ asset('assets/js/halloween-theme.js') }}?v={{ filemtime(public_path('assets/js/halloween-theme.js')) }}" defer></script>
    @endif
    @auth
        @php($presenceConfig = [
            'enabled' => true,
            'endpoint' => route('presence.heartbeat'),
            'csrfToken' => csrf_token(),
        ])
        <script id="mundo-yuri-presence-config" type="application/json">@json($presenceConfig)</script>
        <script src="{{ asset('assets/js/presence.js') }}?v={{ filemtime(public_path('assets/js/presence.js')) }}" defer></script>
    @endauth
</body>
</html>
