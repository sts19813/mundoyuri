<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cuentas bloqueadas · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar />

    <main class="portal-profile-page social-hub-page">
        <div class="profile-ambient profile-ambient-one"></div>
        <div class="profile-ambient profile-ambient-two"></div>

        <div class="container-xl px-4 position-relative">
            <nav class="profile-breadcrumb" aria-label="Migas de pan">
                <a href="{{ route('profile.edit') }}">Mi cuenta</a>
                <span>›</span>
                <span>Cuentas bloqueadas</span>
            </nav>

            @if(session('success'))
                <div class="portal-alert portal-alert-success" role="status">{{ session('success') }}</div>
            @endif

            <header class="social-hub-heading">
                <div>
                    <span class="profile-panel-kicker">Privacidad y seguridad</span>
                    <h1>Cuentas bloqueadas</h1>
                    <p>Estas personas no pueden seguirte ni intercambiar mensajes privados contigo.</p>
                </div>
            </header>

            <section class="profile-panel social-list-panel">
                @forelse($blockedUsers as $blockedUser)
                    <div class="blocked-user-list-item">
                        <a class="conversation-person" href="{{ $blockedUser->publicProfileUrl() }}">
                            @if($blockedUser->hasProfileAvatar())
                                <img class="social-list-avatar" src="{{ $blockedUser->avatarUrl() }}" alt="">
                            @else
                                <span class="social-list-avatar social-list-avatar-fallback">{{ $blockedUser->initials() }}</span>
                            @endif
                            <span>
                                <strong>{{ $blockedUser->alias ?: $blockedUser->name }}</strong>
                                <small>Cuenta bloqueada</small>
                            </span>
                        </a>

                        <form method="POST" action="{{ route('users.block.destroy', $blockedUser) }}">
                            @csrf
                            @method('DELETE')
                            <button class="profile-btn profile-btn-soft" type="submit">Desbloquear</button>
                        </form>
                    </div>
                @empty
                    <div class="social-empty-state">
                        <span aria-hidden="true">◇</span>
                        <h2>No has bloqueado ninguna cuenta</h2>
                        <p>Cuando bloquees a alguien desde su perfil, podrás administrarlo aquí.</p>
                    </div>
                @endforelse
            </section>

            @if($blockedUsers->hasPages())
                <div class="social-pagination">{{ $blockedUsers->links() }}</div>
            @endif
        </div>
    </main>

    <x-footer />
</body>
</html>
