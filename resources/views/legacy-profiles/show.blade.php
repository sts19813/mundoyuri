<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, follow">
    <title>{{ $legacyProfile->nickname }} · Perfil histórico · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar />
    <main class="portal-profile-page">
        <div class="profile-ambient profile-ambient-one"></div><div class="profile-ambient profile-ambient-two"></div>
        <div class="container-xl px-4 position-relative">
            <nav class="profile-breadcrumb" aria-label="Migas de pan"><a href="{{ route('home') }}">Inicio</a><span>›</span><a href="{{ route('legacy-profiles.index') }}">Archivo histórico</a><span>›</span><span>{{ $legacyProfile->nickname }}</span></nav>
            <section class="profile-hero-card legacy-profile-detail-hero"><div class="profile-cover-overlay"></div><div class="profile-hero-pattern"></div><div class="profile-identity"><div class="profile-avatar-wrap">@if($legacyProfile->avatarUrl())<img src="{{ $legacyProfile->avatarUrl() }}" alt="Avatar histórico de {{ $legacyProfile->nickname }}" class="profile-avatar-main">@else<span class="profile-avatar-main profile-avatar-generic">{{ mb_strtoupper(mb_substr($legacyProfile->nickname, 0, 1)) }}</span>@endif</div><div class="profile-identity-copy"><span class="profile-eyebrow">Perfil histórico</span><h1>{{ $legacyProfile->nickname }}</h1><p>Datos recuperados del archivo de Mundo Yuri</p>@if($legacyProfile->legacy_rank)<span class="community-rank">{{ $legacyProfile->legacy_rank }}</span>@endif</div></div></section>
            @if(session('success'))<div class="legacy-profile-notice mt-4"><strong>Solicitud enviada</strong><span>{{ session('success') }}</span></div>@endif
            <div class="legacy-profile-notice mt-4"><strong>Registro archivado</strong><span>Esta ficha preserva información pública del foro original. No indica actividad, disponibilidad ni una cuenta actual.</span></div>
            <div class="profile-grid mt-4"><section class="profile-panel profile-panel-main"><div class="profile-panel-heading"><div><span class="profile-panel-kicker">Archivo de Mundo Yuri</span><h2>Datos recuperados</h2></div></div><dl class="profile-details-list legacy-profile-details"><div><dt>Registro histórico</dt><dd>{{ $legacyProfile->legacy_joined_at?->translatedFormat('d M Y') ?: 'Sin fecha disponible' }}</dd></div>@if(! is_null($legacyProfile->legacy_message_count))<div><dt>Mensajes archivados</dt><dd>{{ number_format($legacyProfile->legacy_message_count) }}</dd></div>@endif
                    @if($legacyProfile->legacy_rank)<div><dt>Rango antiguo</dt><dd>{{ $legacyProfile->legacy_rank }}</dd></div>@endif
                    @if($legacyProfile->legacy_location)<div><dt>Localización histórica</dt><dd>{{ $legacyProfile->legacy_location }}</dd></div>@endif
                    @if($legacyProfile->legacy_occupation)<div><dt>Ocupación histórica</dt><dd>{{ $legacyProfile->legacy_occupation }}</dd></div>@endif
                    @if($legacyProfile->legacy_interests)<div><dt>Intereses históricos</dt><dd class="legacy-profile-prewrap">{{ $legacyProfile->legacy_interests }}</dd></div>@endif
                    @if($legacyProfile->legacy_website)<div><dt>Sitio web histórico</dt><dd><a href="{{ $legacyProfile->legacy_website }}" rel="nofollow noopener noreferrer" target="_blank">{{ $legacyProfile->legacy_website }}</a></dd></div>@endif
                </dl></section><aside class="profile-sidebar"><section class="profile-panel profile-account-card"><span class="profile-panel-kicker">Estado</span><h2>Archivo histórico</h2><p class="text-muted mb-0">Importado el {{ $legacyProfile->created_at?->translatedFormat('d M Y') }}. Esta fecha corresponde al registro moderno del archivo, no al ingreso original.</p>@if($legacyProfile->canBeClaimed())<div class="legacy-claim-prompt"><strong>¿Este perfil era tuyo?</strong>@auth<a href="{{ route('legacy-profile-claims.create', ['profile' => $legacyProfile->id]) }}" class="profile-btn profile-btn-soft">Solicitar reclamación</a>@else<a href="{{ route('login') }}" class="profile-btn profile-btn-soft">Solicitar reclamación</a>@endauth</div>@elseif($legacyProfile->claim_status === 'pending')<p class="text-muted fs-8 mt-4 mb-0">Hay una solicitud de reclamación en revisión.</p>@else<p class="text-muted fs-8 mt-4 mb-0">Este perfil histórico ya fue reclamado.</p>@endif</section></aside></div>
        </div>
    </main>
    <x-footer />
</body>
</html>
