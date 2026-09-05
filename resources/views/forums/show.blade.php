<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $forum->name }} · Foros · Mundo Yuri</title>
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
                <a href="{{ route('forums.index') }}">Foros</a><span>›</span><span>{{ $forum->name }}</span>
            </nav>

            <x-forum.heading :eyebrow="$forum->category->name" :title="$forum->name">
                <p>{{ $forum->description }}</p>
                <x-slot:actions>
                @auth
                    @can('createTopic', $forum)
                        <a href="{{ route('forum.threads.create', $forum) }}" class="profile-btn profile-btn-primary" data-open-topic-modal aria-haspopup="dialog" aria-controls="forum-topic-dialog">
                            Crear tema
                        </a>
                    @endcan
                @endauth
                </x-slot:actions>
            </x-forum.heading>

            @if(session('success'))
                <div class="legacy-profile-notice forum-flash"><strong>Listo</strong><span>{{ session('success') }}</span></div>
            @endif

            <div class="forum-list-toolbar">
                <p>{{ trans_choice(':count tema|:count temas', $threads->total(), ['count' => number_format($threads->total())]) }}</p>
                <form method="GET" class="forum-search forum-search-inline">
                    <label class="visually-hidden" for="forum-search">Buscar temas en {{ $forum->name }}</label>
                    <input id="forum-search" name="q" type="search" value="{{ $search }}" placeholder="Buscar temas">
                    <button type="submit">Buscar</button>
                </form>
            </div>

            <section class="forum-feed" aria-label="Temas de {{ $forum->name }}">
                @forelse($threads as $thread)
                    <article id="thread-{{ $thread->id }}" class="forum-feed-topic">
                        <header class="forum-feed-title">
                            <h2><a href="{{ route('forum.threads.show', $thread) }}">{{ $thread->title }}</a></h2>
                            @if($thread->is_pinned)<span class="forum-state">Fijado</span>@endif
                            @if($thread->is_locked)<span class="forum-state">Cerrado</span>@endif
                        </header>
                        @if($thread->initialPost)
                            <x-forum.post :post="$thread->initialPost" />
                        @endif
                        <div class="forum-feed-divider">
                            <span><span data-reply-count>{{ number_format($thread->replies_count) }}</span> respuestas · <a href="{{ route('forum.threads.show', $thread) }}" @if($thread->replies_count > 2) data-load-replies @endif>{{ $thread->replies_count > 2 ? 'Ver todas las respuestas' : 'Ver conversación' }}</a></span>
                            <span>{{ number_format($thread->views_count) }} vistas</span>
                        </div>
                        <div class="forum-feed-replies" data-feed-replies>
                            @foreach($thread->previewReplies as $reply)
                                <x-forum.post :post="$reply" />
                            @endforeach
                        </div>
                        @auth
                            @can('reply', $thread)
                                <form method="POST" action="{{ route('forum.posts.store', $thread) }}" class="forum-feed-composer" data-feed-reply>
                                    @csrf
                                    <input type="hidden" name="from_feed" value="1">
                                    <input type="hidden" name="reply_thread" value="{{ $thread->id }}">
                                    <label class="visually-hidden" for="reply-{{ $thread->id }}">Responder a {{ $thread->title }}</label>
                                    <textarea id="reply-{{ $thread->id }}" name="body" rows="1" minlength="2" maxlength="12000" required placeholder="Escribe una respuesta…">{{ (int) old('reply_thread') === $thread->id ? old('body') : '' }}</textarea>
                                    <button type="submit" class="profile-btn profile-btn-primary">Responder</button>
                                    <p class="forum-feed-error" role="status" data-reply-status>@if((int) old('reply_thread') === $thread->id){{ $errors->first('body') }}@endif</p>
                                </form>
                            @else
                                <p class="forum-feed-readonly">{{ $thread->is_locked || $forum->is_locked ? 'Esta conversación está cerrada.' : 'No tienes permiso para responder.' }}</p>
                            @endcan
                        @else
                            <p class="forum-feed-readonly"><a href="{{ route('login') }}">Inicia sesión</a> para responder.</p>
                        @endauth
                    </article>
                @empty
                    <div class="profile-panel forum-empty">
                        <h2>{{ $search !== '' ? 'No encontramos temas' : 'No hay temas todavía' }}</h2>
                        <p>{{ $search !== '' ? 'Prueba con otra búsqueda.' : 'Inicia la primera conversación cuando quieras.' }}</p>
                    </div>
                @endforelse
            </section>

            {{ $threads->links() }}
        </div>
    </main>

    @auth
        @can('createTopic', $forum)
            <dialog id="forum-topic-dialog" class="forum-topic-dialog" data-reopen="{{ !old('reply_thread') && $errors->hasAny(['title', 'body']) ? 'true' : 'false' }}" aria-labelledby="forum-topic-dialog-title">
                <div class="forum-topic-dialog-inner">
                    <header>
                        <div>
                            <span class="profile-eyebrow">{{ $forum->name }}</span>
                            <h2 id="forum-topic-dialog-title">Crear un tema</h2>
                            <p>Abre una conversación para las personas de este foro.</p>
                        </div>
                        <button type="button" class="forum-dialog-close" data-close-topic-modal aria-label="Cerrar">×</button>
                    </header>
                    <x-forum.composer :action="route('forum.threads.store', $forum)" submit="Publicar tema" title="" :use-old-input="!old('reply_thread')">
                        <input type="hidden" name="from_feed" value="1">
                    </x-forum.composer>
                </div>
            </dialog>
        @endcan
    @endauth

    <x-footer />
    <script src="{{ asset('assets/js/forum.js') }}?v={{ filemtime(public_path('assets/js/forum.js')) }}" defer></script>
</body>
</html>
