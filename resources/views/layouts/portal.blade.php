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
</body>
</html>
