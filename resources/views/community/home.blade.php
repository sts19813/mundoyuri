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
                    <a class="profile-btn profile-btn-primary" href="{{ route('forums.index') }}">Entrar a los foros</a>
                    <a class="profile-btn profile-btn-soft" href="{{ route('community.members') }}">Conocer miembros</a>
                </div>
            </header>

            <section class="community-home-stats" aria-label="Estadísticas de comunidad">
                @foreach(['members' => 'miembros', 'threads' => 'temas', 'messages' => 'mensajes', 'questions' => 'preguntas', 'answers' => 'respuestas'] as $key => $label)
                    <div><strong>{{ number_format($stats[$key]) }}</strong><span>{{ $label }}</span></div>
                @endforeach
            </section>

            <div class="community-home-layout">
                <div class="community-home-main">
                    <section class="community-home-section">
                        <div class="community-home-heading"><div><span class="profile-panel-kicker">Conversaciones</span><h2>Temas recientes</h2></div><a href="{{ route('forums.index') }}">Ver foros</a></div>
                        <div class="community-content-list">
                            @forelse($recentThreads as $thread)
                                <article class="community-content-row">
                                    <span class="community-content-mark">{{ $thread->is_pinned ? '★' : '◌' }}</span>
                                    <div><h3><a href="{{ route('forum.threads.show', $thread) }}">{{ $thread->title }}</a></h3><p>En {{ $thread->forum->name }} · {{ $thread->authorName() }} · {{ $thread->last_post_at?->diffForHumans() }}</p></div>
                                    <span class="community-content-count">{{ number_format($thread->replies_count) }}<small>respuestas</small></span>
                                </article>
                            @empty
                                <div class="community-home-empty"><strong>Aún no hay temas</strong><span>La primera conversación puede empezar hoy.</span></div>
                            @endforelse
                        </div>
                    </section>

                    <section class="community-home-section">
                        <div class="community-home-heading"><div><span class="profile-panel-kicker">Preguntas</span><h2>Respuestas que buscamos juntas</h2></div><a href="{{ route('questions.index') }}">Ver preguntas</a></div>
                        <div class="community-question-columns">
                            <div>
                                <h3 class="community-list-label">Recientes</h3>
                                @forelse($recentQuestions as $question)
                                    <a class="community-question-link" href="{{ route('questions.show', $question) }}"><span>{{ $question->title }}</span><small>{{ number_format($question->replies_count) }} respuestas · {{ $question->last_post_at?->diffForHumans() }}</small></a>
                                @empty <p class="community-home-muted">Todavía no hay preguntas.</p> @endforelse
                            </div>
                            <div>
                                <h3 class="community-list-label">Sin resolver</h3>
                                @forelse($unresolvedQuestions as $question)
                                    <a class="community-question-link" href="{{ route('questions.show', $question) }}"><span>{{ $question->title }}</span><small>{{ $question->last_post_at?->diffForHumans() }}</small></a>
                                @empty <p class="community-home-muted">Todas las preguntas recientes tienen respuesta.</p> @endforelse
                            </div>
                        </div>
                    </section>

                    <section class="community-home-section">
                        <div class="community-home-heading"><div><span class="profile-panel-kicker">Ahora mismo</span><h2>Actividad reciente</h2></div></div>
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
                </div>

                <aside class="community-home-side">
                    <section class="community-home-section community-home-section-compact">
                        <div class="community-home-heading"><div><span class="profile-panel-kicker">Para descubrir</span><h2>Temas populares</h2></div></div>
                        <div class="community-popular-list">
                            @forelse($popularThreads as $thread)
                                <a href="{{ route('forum.threads.show', $thread) }}"><span>{{ $thread->title }}</span><small>{{ number_format($thread->replies_count) }} respuestas · {{ number_format($thread->views_count) }} vistas</small></a>
                            @empty <p class="community-home-muted">Aún no hay conversaciones destacadas.</p> @endforelse
                        </div>
                    </section>

                    <section class="community-home-section community-home-section-compact">
                        <div class="community-home-heading"><div><span class="profile-panel-kicker">La comunidad hoy</span><h2>Miembros activos</h2></div><a href="{{ route('community.members', ['filter' => 'active']) }}">Ver todos</a></div>
                        <div class="community-member-mini-list">
                            @forelse($activeMembers as $member)
                                @php($rank = $rankResolver->resolve($member))
                                <a href="{{ $member->publicProfileUrl() }}" class="community-member-mini">
                                    @if($member->hasProfileAvatar())<img src="{{ $member->avatarUrl() }}" alt="">@else<span>{{ $member->initials() }}</span>@endif
                                    <div><strong>{{ $member->displayName() }}</strong><small>{{ number_format($member->community_message_count) }} publicaciones</small><x-community.rank :rank="$rank" /></div>
                                </a>
                            @empty <p class="community-home-muted">Pronto veremos a las primeras personas por aquí.</p> @endforelse
                        </div>
                    </section>

                    <section class="community-home-section community-home-section-compact">
                        <div class="community-home-heading"><div><span class="profile-panel-kicker">Acaban de llegar</span><h2>Miembros nuevos</h2></div></div>
                        <div class="community-member-chip-list">
                            @forelse($newMembers as $member)
                                <a href="{{ $member->publicProfileUrl() }}">{{ $member->displayName() }}@if($member->alias)<small>{{ '@'.$member->alias }}</small>@endif</a>
                            @empty <p class="community-home-muted">Aún no hay perfiles para mostrar.</p> @endforelse
                        </div>
                    </section>

                    <section class="community-home-section community-home-section-compact community-home-legacy">
                        <div class="community-home-heading"><div><span class="profile-panel-kicker">Archivo vivo</span><h2>Miembros históricos</h2></div><a href="{{ route('legacy-profiles.index') }}">Explorar</a></div>
                        <div class="community-legacy-list">
                            @forelse($legacyMembers as $member)
                                <a href="{{ route('legacy-profiles.show', $member) }}"><span>✦</span><div><strong>{{ $member->nickname }}</strong><small>{{ $member->legacy_joined_at?->format('Y') ? 'Miembro desde '.$member->legacy_joined_at->format('Y') : 'Perfil histórico' }}</small></div></a>
                            @empty <p class="community-home-muted">El archivo histórico se irá incorporando poco a poco.</p> @endforelse
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </main>
    <x-footer />
</body>
</html>
