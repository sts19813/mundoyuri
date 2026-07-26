<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mensajes · Mundo Yuri</title>
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
                <span>Mensajes</span>
            </nav>

            @if(session('success'))
                <div class="portal-alert portal-alert-success" role="status">{{ session('success') }}</div>
            @endif

            <header class="social-hub-heading">
                <div>
                    <span class="profile-panel-kicker">Conversaciones privadas</span>
                    <h1>Tus mensajes</h1>
                    <p>Continúa tus conversaciones con personas de la comunidad.</p>
                </div>
                <a class="profile-btn profile-btn-soft" href="{{ route('notifications.index') }}">Ver notificaciones</a>
            </header>

            <section class="profile-panel social-list-panel">
                @forelse($conversations as $conversation)
                    @php($otherUser = $conversation->otherParticipant($viewer))
                    <a class="conversation-list-item{{ $conversation->unread_messages_count ? ' is-unread' : '' }}" href="{{ route('messages.show', $otherUser) }}">
                        @if($otherUser->hasProfileAvatar())
                            <img class="social-list-avatar" src="{{ $otherUser->avatarUrl() }}" alt="">
                        @else
                            <span class="social-list-avatar social-list-avatar-fallback">{{ $otherUser->initials() }}</span>
                        @endif

                        <span class="conversation-list-copy">
                            <span class="conversation-list-topline">
                                <strong>{{ $otherUser->alias ?: $otherUser->name }}</strong>
                                <time datetime="{{ optional($conversation->last_message_at)->toIso8601String() }}">
                                    {{ optional($conversation->last_message_at)->diffForHumans() }}
                                </time>
                            </span>
                            <span class="conversation-list-preview">
                                @if($conversation->lastMessage?->sender_id === $viewer->id)
                                    Tú:
                                @endif
                                {{ \Illuminate\Support\Str::limit($conversation->lastMessage?->body, 110) }}
                            </span>
                        </span>

                        @if($conversation->unread_messages_count)
                            <span class="social-count-badge" aria-label="{{ $conversation->unread_messages_count }} mensajes sin leer">
                                {{ $conversation->unread_messages_count > 99 ? '99+' : $conversation->unread_messages_count }}
                            </span>
                        @endif
                    </a>
                @empty
                    <div class="social-empty-state">
                        <span aria-hidden="true">✉</span>
                        <h2>Aún no tienes conversaciones</h2>
                        <p>Visita el perfil de otra persona y usa el botón “Mensaje” para comenzar.</p>
                    </div>
                @endforelse
            </section>

            @if($conversations->hasPages())
                <div class="social-pagination">{{ $conversations->links() }}</div>
            @endif
        </div>
    </main>

    <x-footer />
</body>
</html>
