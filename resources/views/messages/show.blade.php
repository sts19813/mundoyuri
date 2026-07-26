<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Conversación con {{ $otherUser->alias ?: $otherUser->name }} · Mundo Yuri</title>
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
                <a href="{{ route('messages.index') }}">Mensajes</a>
                <span>›</span>
                <span>{{ $otherUser->alias ?: $otherUser->name }}</span>
            </nav>

            @if(session('success'))
                <div class="portal-alert portal-alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="portal-alert portal-alert-error" role="alert">{{ session('error') }}</div>
            @endif

            <section class="profile-panel conversation-shell">
                <header class="conversation-header">
                    <a class="conversation-person" href="{{ $otherUser->publicProfileUrl() }}">
                        @if($otherUser->hasProfileAvatar())
                            <img class="social-list-avatar" src="{{ $otherUser->avatarUrl() }}" alt="">
                        @else
                            <span class="social-list-avatar social-list-avatar-fallback">{{ $otherUser->initials() }}</span>
                        @endif
                        <span>
                            <strong>{{ $otherUser->alias ?: $otherUser->name }}</strong>
                            <small>{{ $otherUser->is_active ? 'Ver perfil' : 'Cuenta no disponible' }}</small>
                        </span>
                    </a>

                    @if($viewerHasBlocked)
                        <form method="POST" action="{{ route('users.block.destroy', $otherUser) }}">
                            @csrf
                            @method('DELETE')
                            <button class="profile-btn profile-btn-soft" type="submit">Desbloquear</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('users.block.store', $otherUser) }}" onsubmit="return confirm('¿Quieres bloquear a esta persona? Ya no podrán seguirse ni enviarse mensajes.')">
                            @csrf
                            <button class="profile-btn profile-btn-danger-soft" type="submit">Bloquear</button>
                        </form>
                    @endif
                </header>

                <div class="conversation-messages" id="conversationMessages">
                    @if($messages->hasPages())
                        <div class="conversation-older-link">
                            @if($messages->nextPageUrl())
                                <a href="{{ $messages->nextPageUrl() }}">Ver mensajes anteriores</a>
                            @endif
                        </div>
                    @endif

                    @forelse($messages as $message)
                        <article class="message-row {{ $message->sender_id === $viewer->id ? 'is-outgoing' : 'is-incoming' }}">
                            <div class="message-bubble">
                                <p>{{ $message->body }}</p>
                                <time datetime="{{ $message->created_at->toIso8601String() }}">
                                    {{ $message->created_at->timezone('America/Merida')->format('d M · g:i a') }}
                                    @if($message->sender_id === $viewer->id)
                                        <span aria-label="{{ $message->read_at ? 'Leído' : 'Enviado' }}">{{ $message->read_at ? '✓✓' : '✓' }}</span>
                                    @endif
                                </time>
                            </div>
                        </article>
                    @empty
                        <div class="social-empty-state conversation-empty">
                            <span aria-hidden="true">♡</span>
                            <h2>Inicia la conversación</h2>
                            <p>Escribe un saludo o comparte una serie que te haya encantado.</p>
                        </div>
                    @endforelse
                </div>

                <footer class="conversation-composer">
                    @if($interactionBlocked)
                        <div class="conversation-disabled">
                            {{ $viewerHasBlocked ? 'Desbloquea a esta persona para volver a enviar mensajes.' : 'Esta conversación no admite nuevos mensajes.' }}
                        </div>
                    @elseif(!$otherUser->is_active)
                        <div class="conversation-disabled">Esta cuenta ya no está disponible.</div>
                    @else
                        <form method="POST" action="{{ route('messages.store', $otherUser) }}">
                            @csrf
                            <label class="visually-hidden" for="message-body">Escribe un mensaje</label>
                            <textarea id="message-body" name="body" rows="2" maxlength="2000" required placeholder="Escribe un mensaje…">{{ old('body') }}</textarea>
                            <button class="profile-btn profile-btn-primary" type="submit">Enviar</button>
                        </form>
                        @error('body')
                            <p class="conversation-form-error">{{ $message }}</p>
                        @enderror
                    @endif
                </footer>
            </section>
        </div>
    </main>

    <x-footer />

    <script>
        const conversation = document.getElementById('conversationMessages');
        if (conversation && !new URLSearchParams(window.location.search).has('page')) {
            conversation.scrollTop = conversation.scrollHeight;
        }
    </script>
</body>
</html>
