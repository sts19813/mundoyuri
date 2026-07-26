<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Perfil de {{ $profileUser->alias ?: $profileUser->name }} en Mundo Yuri.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $profileUser->alias ?: $profileUser->name }} · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar />

    <main class="portal-profile-page public-profile-page">
        <div class="profile-ambient profile-ambient-one"></div>
        <div class="profile-ambient profile-ambient-two"></div>

        <div class="container-xl px-4 position-relative">
            <nav class="profile-breadcrumb" aria-label="Migas de pan">
                <a href="{{ route('home') }}">Inicio</a>
                <span>›</span>
                <span>Perfil de {{ $profileUser->alias ?: $profileUser->name }}</span>
            </nav>

            <section class="profile-hero-card public-profile-hero">
                @if($profileUser->coverImageUrl())
                    <img src="{{ $profileUser->coverImageUrl() }}" alt="Portada de {{ $profileUser->alias ?: $profileUser->name }}" class="profile-cover-media">
                @endif
                <div class="profile-cover-overlay"></div>
                <div class="profile-hero-pattern"></div>

                <div class="profile-identity">
                    <div class="profile-avatar-wrap">
                        @if($profileUser->hasProfileAvatar())
                            <img src="{{ $profileUser->avatarUrl() }}" alt="Foto de perfil de {{ $profileUser->alias ?: $profileUser->name }}" class="profile-avatar-main">
                        @else
                            <span class="profile-avatar-main profile-avatar-generic">{{ $profileUser->initials() }}</span>
                        @endif
                    </div>
                    <div class="profile-identity-copy">
                        <span class="profile-eyebrow">Perfil de la comunidad</span>
                        <h1>{{ $profileUser->name }}</h1>
                        <p>{{ $profileUser->alias ? '@'.$profileUser->alias : 'Miembro de Mundo Yuri' }}</p>
                    </div>
                </div>

                <div class="profile-hero-actions">
                    @if($isOwner)
                        <a class="profile-btn profile-btn-primary" href="{{ route('profile.edit') }}">
                            Editar mi perfil
                        </a>
                    @endif
                    <div class="profile-status-chip">
                        <span></span>
                        {{ $profileUser->email_verified_at ? 'Cuenta verificada' : 'Miembro de la comunidad' }}
                    </div>
                </div>
            </section>

            <div class="profile-grid public-profile-grid">
                <section class="profile-panel profile-panel-main public-profile-about">
                    <div class="profile-panel-heading">
                        <div>
                            <span class="profile-panel-kicker">Acerca de</span>
                            <h2>Biografía</h2>
                        </div>
                    </div>

                    @if(filled($profileUser->biography))
                        <p class="public-profile-biography">{{ $profileUser->biography }}</p>
                    @else
                        <div class="public-profile-empty">
                            <span aria-hidden="true">✦</span>
                            <p>{{ $isOwner ? 'Todavía no has agregado una biografía.' : 'Esta persona todavía no ha agregado una biografía.' }}</p>
                            @if($isOwner)
                                <a href="{{ route('profile.edit') }}">Agregar biografía</a>
                            @endif
                        </div>
                    @endif
                </section>

                <aside class="profile-sidebar">
                    <section class="profile-panel profile-account-card">
                        <span class="profile-panel-kicker">Información</span>
                        <h2>Perfil</h2>
                        <dl class="profile-details-list">
                            <div>
                                <dt>Miembro desde</dt>
                                <dd>{{ optional($profileUser->created_at)->translatedFormat('M Y') }}</dd>
                            </div>
                            <div>
                                <dt>Comentarios</dt>
                                <dd>{{ $profileUser->comments_count }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="profile-panel public-profile-coming-soon">
                        <span class="public-profile-coming-icon" aria-hidden="true">♡</span>
                        <div>
                            <h3>Series favoritas</h3>
                            <p>Muy pronto aparecerán aquí las favoritas que esta persona quiera compartir.</p>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <x-footer />
</body>
</html>
