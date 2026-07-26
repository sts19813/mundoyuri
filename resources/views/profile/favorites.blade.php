<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Series favoritas de {{ $profileUser->alias ?: $profileUser->name }} en Mundo Yuri.">
    <title>Favoritas de {{ $profileUser->alias ?: $profileUser->name }} · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar />

    <main class="portal-profile-page profile-connections-page">
        <div class="profile-ambient profile-ambient-one"></div>
        <div class="container-xl px-4 position-relative">
            <nav class="profile-breadcrumb" aria-label="Migas de pan">
                <a href="{{ route('home') }}">Inicio</a>
                <span>›</span>
                <a href="{{ $profileUser->publicProfileUrl() }}">{{ $profileUser->alias ?: $profileUser->name }}</a>
                <span>›</span>
                <span>Series favoritas</span>
            </nav>

            <section class="profile-panel profile-panel-main">
                <div class="profile-panel-heading">
                    <div>
                        <span class="profile-panel-kicker">Mi colección</span>
                        <h2>Series favoritas</h2>
                    </div>
                    <a class="profile-btn profile-btn-soft" href="{{ $profileUser->publicProfileUrl() }}">Volver al perfil</a>
                </div>

                @if($favorites->isNotEmpty())
                    <div class="public-favorites-grid public-favorites-grid-full">
                        @foreach($favorites as $favorite)
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
                    {{ $favorites->links() }}
                @else
                    <div class="public-profile-empty">
                        <span aria-hidden="true">♡</span>
                        <p>Este perfil todavía no tiene series favoritas.</p>
                    </div>
                @endif
            </section>
        </div>
    </main>

    <x-footer />
</body>
</html>
