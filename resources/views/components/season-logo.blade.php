@php
    $seasonLogoAsset = \App\Models\SeasonThemeSetting::logoAsset();
    $seasonLogoUrl = asset($seasonLogoAsset);
    if ($seasonLogoAsset !== 'assets/img/logos/mundo-yuri-logo.svg') {
        $seasonLogoUrl .= '?v='.filemtime(public_path($seasonLogoAsset));
    }
@endphp
<img src="{{ $seasonLogoUrl }}" alt="Mundo Yuri" width="382" height="158" decoding="async" {{ $attributes }}>
