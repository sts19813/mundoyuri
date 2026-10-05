<!DOCTYPE html>
@hasSection('html_attributes')
<html @yield('html_attributes')>
@else
<html lang="es">
@endif
<head>
    <x-google-tag-manager />
    <x-google-analytics />
    @yield('head')
</head>
@hasSection('body_attributes')
<body @yield('body_attributes')>
@else
<body>
@endif
    <x-google-tag-manager-noscript />
    @yield('body')
    <script src="{{ asset('assets/js/lazy-media.js') }}?v={{ filemtime(public_path('assets/js/lazy-media.js')) }}" defer></script>
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
</body>
</html>
