<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="El punto de encuentro de la comunidad Mundo Yuri.">
    <title>Comunidad · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar />

    <main class="portal-profile-page community-home-page">
        <div class="profile-ambient profile-ambient-one"></div><div class="profile-ambient profile-ambient-two"></div>
        <div class="container-xl px-4 position-relative">
            <nav class="profile-breadcrumb" aria-label="Migas de pan"><a href="{{ route('home') }}">Inicio</a><span>›</span><span>Comunidad</span></nav>

            <header class="community-home-hero">
                <div>
                    <span class="profile-eyebrow">El punto de encuentro</span>
                    <h1>Bienvenida a la <em>comunidad</em></h1>
                    <p>Un lugar para conversar sobre Girls' Love, compartir hallazgos y reencontrarnos con la historia de Mundo Yuri.</p>
                </div>
                <div class="community-home-actions">
                    <a class="profile-btn profile-btn-primary" href="{{ route('forums.index') }}">Foros</a>
                    <a class="profile-btn profile-btn-soft" href="{{ route('questions.index') }}">Preguntas</a>
                    <a class="profile-btn profile-btn-soft" href="{{ route('community.members') }}">Miembros</a>
                </div>
            </header>

            <div class="community-home-grid">
                <section id="actividad-reciente" class="community-home-section">
                    <div class="community-home-heading"><div><span class="profile-panel-kicker">Ahora mismo</span><h2>Actividad reciente</h2></div><a href="{{ route('community.members', ['sort' => 'activity']) }}">Ver actividad</a></div>
                    <div class="community-activity-list">
                        @forelse($recentActivity as $post)
                            @php($thread = $post->thread)
                            <article>
                                <span class="community-activity-dot" aria-hidden="true"></span>
                                <div><strong>{{ $post->authorName() }}</strong> @if($post->is_initial){{ $thread->isQuestion() ? 'hizo una pregunta' : 'abrió un tema' }}@else{{ $thread->isQuestion() ? 'respondió una pregunta' : 'respondió un tema' }}@endif <a href="{{ $thread->isQuestion() ? route('questions.show', $thread) : route('forum.threads.show', $thread) }}{{ $post->is_initial ? '' : '#post-'.$post->id }}">{{ $thread->title }}</a><small>{{ $post->created_at->diffForHumans() }}</small></div>
                            </article>
                        @empty
                            <div class="community-home-empty"><strong>La actividad aparecerá aquí</strong><span>Cuando comencemos a conversar, este espacio se irá llenando.</span></div>
                        @endforelse
                    </div>
                </section>

                <aside class="community-home-side">
                    <section class="community-home-section community-home-threads">
                        <div class="community-home-heading"><div><span class="profile-panel-kicker">Conversaciones</span><h2>Temas recientes</h2></div><a href="{{ route('forums.index') }}">Ver foros</a></div>
                        <div class="community-thread-list">
                            @forelse($recentThreads as $thread)
                                <a href="{{ route('forum.threads.show', $thread) }}">
                                    <strong>{{ $thread->title }}</strong>
                                    <small>{{ $thread->forum->name }}@if($thread->replies_count) · {{ number_format($thread->replies_count) }} {{ $thread->replies_count === 1 ? 'respuesta' : 'respuestas' }}@endif</small>
                                </a>
                            @empty
                                <div class="community-home-empty"><strong>Aún no hay temas</strong><span>La primera conversación puede empezar hoy.</span></div>
                            @endforelse
                        </div>
                    </section>

                    <section class="community-home-history" aria-labelledby="community-history-title">
                        <span class="community-home-history-mark" aria-hidden="true">✦</span>
                        <div>
                            <span class="profile-panel-kicker">Archivo vivo</span>
                            <h2 id="community-history-title">Una comunidad desde 2007</h2>
                            <p>Estamos recuperando una pequeña parte de la historia del Mundo Yuri original.</p>
                        </div>
                        <a class="profile-btn profile-btn-soft" href="{{ route('legacy-profiles.index') }}">Miembros históricos</a>
                    </section>
                </aside>
            </div>
        </div>
    </main>
    <x-footer />
</body>
</html>
