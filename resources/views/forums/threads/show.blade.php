<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $thread->title }} · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar />
    <main class="portal-profile-page forum-page">
        <div class="container-xl px-4 position-relative community-conversation-shell">
            <nav class="profile-breadcrumb">
                <a href="{{ route('forums.index') }}">Foros</a><span>›</span>
                <a href="{{ route('forums.show', $thread->forum) }}">{{ $thread->forum->name }}</a><span>›</span>
                <span>{{ $thread->title }}</span>
            </nav>

            <x-forum.heading :eyebrow="$thread->forum->name" :title="$thread->title">
                <p>
                    Creado por @if($thread->author)<a href="{{ $thread->author->publicProfileUrl() }}">{{ $thread->author->displayName() }}</a>@else{{ $thread->authorName() }}@endif
                    · {{ number_format($thread->replies_count) }} {{ $thread->replies_count === 1 ? 'respuesta' : 'respuestas' }} · {{ number_format($thread->views_count) }} vistas
                </p>
                <x-slot:actions>
                @auth
                        @can('reply', $thread)<a class="profile-btn profile-btn-primary" href="#responder">Responder</a>@endcan
                        @if($isSubscribed)
                            <form method="POST" action="{{ route('forum.subscriptions.destroy', $thread) }}">@csrf @method('DELETE')<button class="profile-btn profile-btn-soft" type="submit">Dejar de seguir</button></form>
                        @else
                            <form method="POST" action="{{ route('forum.subscriptions.store', $thread) }}">@csrf <button class="profile-btn profile-btn-soft" type="submit">Seguir tema</button></form>
                        @endif
                        <details class="forum-action-menu">
                            <summary aria-label="Acciones del tema">•••</summary>
                            <div class="forum-action-menu-panel">
                                @can('update', $thread)<a href="{{ route('forum.threads.edit', $thread) }}">Editar tema</a>@endcan
                                @can('delete', $thread)
                                    <form method="POST" action="{{ route('forum.threads.destroy', $thread) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('¿Eliminar este tema?')">Eliminar tema</button>
                                    </form>
                                @endcan
                                <x-community.report-form :reportable="$thread" />
                            </div>
                        </details>
                @endauth
                </x-slot:actions>
            </x-forum.heading>

            @if(session('success'))
                <div class="legacy-profile-notice forum-flash"><strong>Listo</strong><span>{{ session('success') }}</span></div>
            @endif

            <section class="forum-posts" aria-label="Mensajes del tema">
                @foreach($posts as $post)
                    <x-forum.post :post="$post" />
                @endforeach
            </section>

            {{ $posts->links() }}

            @auth
                @can('reply', $thread)
                    <section class="forum-reply-section" id="responder">
                        <div class="forum-section-heading"><h2>Responder</h2><p>Tu mensaje se publicará en este tema.</p></div>
                        <x-forum.composer :action="route('forum.posts.store', $thread)" submit="Publicar respuesta" />
                    </section>
                @else
                    <section class="profile-panel forum-readonly"><p>{{ $thread->is_locked || $thread->forum->is_locked ? 'Este tema está cerrado para nuevas respuestas.' : 'No tienes permiso para responder en este foro.' }}</p></section>
                @endcan
            @else
                <section class="profile-panel forum-readonly"><p><a href="{{ route('login') }}">Inicia sesión</a> para participar en la conversación.</p></section>
            @endauth

            @auth
                @can('moderate', $thread)
                    <details class="profile-panel forum-moderation" id="moderacion">
                        <summary><span>Moderación</span><small>Gestionar visibilidad, estado y ubicación</small></summary>
                        <form method="POST" action="{{ route('forum.moderation.thread.update', $thread) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="is_locked" value="0">
                            <label><input type="checkbox" name="is_locked" value="1" @checked($thread->is_locked)> Cerrar tema</label>
                            <input type="hidden" name="is_pinned" value="0">
                            <label><input type="checkbox" name="is_pinned" value="1" @checked($thread->is_pinned)> Fijar tema</label>
                            <input type="hidden" name="is_hidden" value="0">
                            <label><input type="checkbox" name="is_hidden" value="1" @checked($thread->is_hidden)> Ocultar tema</label>
                            <label>Mover a
                                <select name="forum_id">
                                    @foreach($moderationForums as $moderationForum)
                                        <option value="{{ $moderationForum->id }}" @selected($thread->forum_id === $moderationForum->id)>{{ $moderationForum->category->name }} · {{ $moderationForum->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button class="profile-btn profile-btn-soft" type="submit">Guardar moderación</button>
                        </form>
                    </details>
                @endcan
            @endauth
        </div>
    </main>
    <x-footer />
    <script src="{{ asset('assets/js/forum.js') }}?v={{ filemtime(public_path('assets/js/forum.js')) }}" defer></script>
</body>
</html>
