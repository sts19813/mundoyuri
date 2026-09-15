<!DOCTYPE html>
@hasSection('html_attributes')
<html @yield('html_attributes')>
@else
<html lang="es">
@endif
<head>
    <x-google-tag-manager />
    @yield('head')
</head>
@hasSection('body_attributes')
<body @yield('body_attributes')>
@else
<body>
@endif
    <x-google-tag-manager-noscript />
    @yield('body')
</body>
</html>
