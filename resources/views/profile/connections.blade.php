<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $type === 'followers' ? 'Seguidores' : 'Personas seguidas' }} de {{ $profileUser->alias ?: $profileUser->name }} en Mundo Yuri.">
    <title>{{ $type === 'followers' ? 'Seguidores' : 'Siguiendo' }} de {{ $profileUser->alias ?: $profileUser->name }} · Mundo Yuri</title>
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
                <span>{{ $type === 'followers' ? 'Seguidores' : 'Siguiendo' }}</span>
            </nav>

            <section class="profile-panel profile-panel-main">
                <div class="profile-panel-heading">
                    <div>
                        <span class="profile-panel-kicker">Comunidad</span>
                        <h2>{{ $type === 'followers' ? 'Seguidores' : 'Personas que sigue' }}</h2>
                    </div>
                    <a class="profile-btn profile-btn-soft" href="{{ $profileUser->publicProfileUrl() }}">Volver al perfil</a>
                </div>

                <div class="profile-connections-grid">
                    @forelse($connections as $connection)
                        <a class="profile-connection-card" href="{{ $connection->publicProfileUrl() }}">
                            @if($connection->hasProfileAvatar())
                                <img src="{{ $connection->avatarUrl() }}" alt="Foto de {{ $connection->alias ?: $connection->name }}">
                            @else
                                <span class="profile-connection-avatar">{{ $connection->initials() }}</span>
                            @endif
                            <span>
                                <strong>{{ $connection->name }}</strong>
                                <small>{{ $connection->alias ? '@'.$connection->alias : 'Miembro de Mundo Yuri' }}</small>
                            </span>
                        </a>
                    @empty
                        <div class="public-profile-empty profile-connections-empty">
                            <span aria-hidden="true">♡</span>
                            <p>{{ $type === 'followers' ? 'Este perfil todavía no tiene seguidores.' : 'Este perfil todavía no sigue a otras personas.' }}</p>
                        </div>
                    @endforelse
                </div>

                {{ $connections->links() }}
            </section>
        </div>
    </main>

    <x-footer />
</body>
</html>
