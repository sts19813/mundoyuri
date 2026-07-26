<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notificaciones · Mundo Yuri</title>
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
                <a href="{{ route('home') }}">Inicio</a>
                <span>›</span>
                <span>Notificaciones</span>
            </nav>

            @if(session('success'))
                <div class="portal-alert portal-alert-success" role="status">{{ session('success') }}</div>
            @endif

            <header class="social-hub-heading">
                <div>
                    <span class="profile-panel-kicker">Actividad de tu cuenta</span>
                    <h1>Notificaciones</h1>
                    <p>{{ $unreadCount ? $unreadCount.' pendientes por leer' : 'Estás al día con tu comunidad' }}</p>
                </div>
                @if($unreadCount)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button class="profile-btn profile-btn-soft" type="submit">Marcar todas como leídas</button>
                    </form>
                @endif
            </header>

            <section class="profile-panel social-list-panel">
                @forelse($notifications as $notification)
                    <a class="notification-list-item{{ $notification->read_at ? '' : ' is-unread' }}" href="{{ route('notifications.open', $notification) }}">
                        @if(filled($notification->data['actor_avatar'] ?? null))
                            <img class="social-list-avatar" src="{{ $notification->data['actor_avatar'] }}" alt="">
                        @else
                            <span class="social-list-avatar social-list-avatar-fallback">MY</span>
                        @endif

                        <span class="notification-list-icon" aria-hidden="true">
                            {{ ($notification->data['kind'] ?? null) === 'direct_message' ? '✉' : '♡' }}
                        </span>

                        <span class="conversation-list-copy">
                            <span class="conversation-list-topline">
                                <strong>{{ $notification->data['title'] ?? 'Nueva actividad' }}</strong>
                                <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                            </span>
                            <span class="conversation-list-preview">{{ $notification->data['message'] ?? '' }}</span>
                        </span>

                        @unless($notification->read_at)
                            <span class="notification-unread-dot" aria-label="Sin leer"></span>
                        @endunless
                    </a>
                @empty
                    <div class="social-empty-state">
                        <span aria-hidden="true">✦</span>
                        <h2>No hay notificaciones todavía</h2>
                        <p>Aquí verás nuevos seguidores y mensajes privados.</p>
                    </div>
                @endforelse
            </section>

            @if($notifications->hasPages())
                <div class="social-pagination">{{ $notifications->links() }}</div>
            @endif
        </div>
    </main>

    <x-footer />
</body>
</html>
