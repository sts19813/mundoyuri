<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Archivo histórico de perfiles del antiguo foro Mundo Yuri.">
    <title>Archivo histórico · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar />
    <main class="portal-profile-page community-directory-page">
        <div class="profile-ambient profile-ambient-one"></div><div class="profile-ambient profile-ambient-two"></div>
        <div class="container-xl px-4 position-relative">
            <nav class="profile-breadcrumb" aria-label="Migas de pan"><a href="{{ route('home') }}">Inicio</a><span>›</span><a href="{{ route('community.index') }}">Comunidad</a><span>›</span><span>Archivo histórico</span></nav>
            <header class="community-directory-hero legacy-profile-hero"><span class="profile-eyebrow">Archivo comunitario</span><h1>Perfiles <em>históricos</em></h1><p>Datos recuperados del archivo de Mundo Yuri. Estas fichas no representan cuentas activas actuales.</p></header>
            <div class="legacy-profile-notice"><strong>Perfil histórico</strong><span>La información corresponde al antiguo foro y conserva su contexto original.</span></div>
            <div class="community-member-grid">
                @forelse($legacyProfiles as $legacyProfile)
                    <article class="community-member-card legacy-profile-card">
                        <a class="community-member-avatar" href="{{ route('legacy-profiles.show', $legacyProfile) }}" aria-label="Ver perfil histórico de {{ $legacyProfile->nickname }}">
                            @if($legacyProfile->avatarUrl())<img src="{{ $legacyProfile->avatarUrl() }}" alt="Avatar histórico de {{ $legacyProfile->nickname }}">@else<span>{{ mb_strtoupper(mb_substr($legacyProfile->nickname, 0, 1)) }}</span>@endif
                        </a>
                        <div class="community-member-copy"><div class="community-member-title"><div><h3><a href="{{ route('legacy-profiles.show', $legacyProfile) }}">{{ $legacyProfile->nickname }}</a></h3><small>Perfil histórico</small></div>@if($legacyProfile->legacy_rank)<span class="community-rank">{{ $legacyProfile->legacy_rank }}</span>@endif</div><dl class="community-member-stats"><div><dt>Registro histórico</dt><dd>{{ $legacyProfile->legacy_joined_at?->translatedFormat('M Y') ?: 'Sin fecha' }}</dd></div><div><dt>Mensajes archivados</dt><dd>{{ number_format($legacyProfile->legacy_message_count) }}</dd></div></dl></div>
                    </article>
                @empty
                    <div class="profile-panel community-directory-empty"><span aria-hidden="true">✦</span><h2>Aún no hay perfiles publicados</h2><p>El archivo histórico se incorporará de forma gradual y verificada.</p></div>
                @endforelse
            </div>
            @if($legacyProfiles->hasPages())<div class="community-directory-pagination">{{ $legacyProfiles->links() }}</div>@endif
        </div>
    </main>
    <x-footer />
</body>
</html>
