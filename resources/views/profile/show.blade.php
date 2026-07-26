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

            @if(session('success'))
                <div class="portal-alert portal-alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="portal-alert portal-alert-error" role="alert">{{ session('error') }}</div>
            @endif

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
                        <div class="public-profile-social-stats">
                            <a href="{{ route('profiles.followers', $profileUser) }}">
                                <strong>{{ $profileUser->followers_count }}</strong> seguidores
                            </a>
                            <a href="{{ route('profiles.following', $profileUser) }}">
                                <strong>{{ $profileUser->following_count }}</strong> siguiendo
                            </a>
                            <span><strong>{{ $profileUser->favorite_series_count }}</strong> favoritas</span>
                        </div>
                    </div>
                </div>

                <div class="profile-hero-actions">
                    @if($isOwner)
                        <a class="profile-btn profile-btn-primary" href="{{ route('profile.edit') }}">
                            Editar mi perfil
                        </a>
                    @else
                        @auth
                            <form method="POST" action="{{ $isFollowing ? route('users.follow.destroy', $profileUser) : route('users.follow.store', $profileUser) }}">
                                @csrf
                                @if($isFollowing)
                                    @method('DELETE')
                                @endif
                                <button class="profile-btn {{ $isFollowing ? 'profile-btn-soft' : 'profile-btn-primary' }}" type="submit">
                                    {{ $isFollowing ? 'Siguiendo' : 'Seguir' }}
                                </button>
                            </form>
                        @else
                            <a class="profile-btn profile-btn-primary" href="{{ route('login') }}">Inicia sesión para seguir</a>
                        @endauth
                    @endif
                    <div class="profile-status-chip">
                        <span></span>
                        {{ $profileUser->email_verified_at ? 'Cuenta verificada' : 'Miembro de la comunidad' }}
                    </div>
                </div>
            </section>

            <div class="profile-grid public-profile-grid">
                <div class="public-profile-main">
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

                    <section class="profile-panel profile-panel-main public-profile-favorites">
                        <div class="profile-panel-heading">
                            <div>
                                <span class="profile-panel-kicker">Mi colección</span>
                                <h2>Series favoritas</h2>
                            </div>
                            <div class="profile-panel-heading-actions">
                                <p>{{ $profileUser->favorite_series_count }} {{ $profileUser->favorite_series_count === 1 ? 'historia guardada' : 'historias guardadas' }}</p>
                                <a href="{{ route('profiles.favorites', $profileUser) }}">Ver todas</a>
                            </div>
                        </div>

                        @if($favoriteSeries->isNotEmpty())
                            <div class="public-favorites-grid">
                                @foreach($favoriteSeries as $favorite)
                                    <a class="public-favorite-card" href="{{ route('catalog.series.show', $favorite) }}">
                                        <x-media-preview
                                            :src="$favorite->coverMediaUrl() ?: 'https://picsum.photos/360/500?favorite='.$favorite->id"
                                            :type="$favorite->coverMediaUrl() ? $favorite->coverMediaType() : 'image'"
                                            :alt="$favorite->title"
                                            class="public-favorite-media"
                                        />
                                        <span class="public-favorite-overlay"></span>
                                        <span class="public-favorite-copy">
                                            <strong>{{ $favorite->title }}</strong>
                                            <small>{{ $favorite->genre?->name ?: 'Girls’ Love' }}</small>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="public-profile-empty">
                                <span aria-hidden="true">♡</span>
                                <p>{{ $isOwner ? 'Aún no has agregado series a tus favoritas.' : 'Esta persona todavía no ha compartido series favoritas.' }}</p>
                                @if($isOwner)
                                    <a href="{{ route('catalog.series.index') }}">Explorar el catálogo</a>
                                @endif
                            </div>
                        @endif
                    </section>
                </div>

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
                            <div>
                                <dt>Favoritas</dt>
                                <dd>{{ $profileUser->favorite_series_count }}</dd>
                            </div>
                        </dl>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <x-footer />
</body>
</html>
