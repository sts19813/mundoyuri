<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preguntas · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
<x-navbar />
<main class="portal-profile-page forum-page question-page">
    <div class="container-xl px-4 position-relative">
        <nav class="profile-breadcrumb"><a href="{{ route('home') }}">Inicio</a><span>›</span><a href="{{ route('community.index') }}">Comunidad</a><span>›</span><span>Preguntas</span></nav>
        <header class="forum-hero question-hero">
            <div>
                <span class="profile-eyebrow">Ayuda entre miembros</span>
                <h1>Preguntas de la <em>comunidad</em></h1>
                <p>Comparte lo que buscas y construyamos respuestas juntas.</p>
            </div>
            @auth <a href="{{ route('questions.create') }}" class="profile-btn profile-btn-primary">Hacer una pregunta</a> @endauth
        </header>

        <form method="GET" class="forum-search question-search">
            <input name="q" type="search" value="{{ $search }}" placeholder="Buscar preguntas">
            <button type="submit">Buscar</button>
        </form>
        <nav class="question-sorts" aria-label="Ordenar preguntas">
            @foreach(['recent' => 'Recientes', 'unanswered' => 'Sin resolver', 'popular' => 'Populares'] as $value => $label)
                <a class="{{ $sort === $value ? 'is-active' : '' }}" href="{{ route('questions.index', array_filter(['sort' => $value, 'q' => $search ?: null])) }}">{{ $label }}</a>
            @endforeach
        </nav>

        @if(session('success'))<div class="legacy-profile-notice mt-3"><strong>Listo</strong><span>{{ session('success') }}</span></div>@endif
        @if(session('error'))<div class="legacy-profile-notice mt-3"><strong>Atención</strong><span>{{ session('error') }}</span></div>@endif

        <section class="question-list">
            @forelse($questions as $question)
                <article class="question-row">
                    <div class="question-row-main">
                        <h2><a href="{{ route('questions.show', $question) }}">{{ $question->title }}</a></h2>
                        <p>Por @if($question->author)<a href="{{ $question->author->publicProfileUrl() }}">{{ $question->author->displayName() }}</a>@else{{ $question->authorName() }}@endif · {{ $question->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="question-row-meta">
                        <span>{{ number_format($question->replies_count) }} {{ $question->replies_count === 1 ? 'respuesta' : 'respuestas' }}</span>
                        @if($question->isResolved())<span class="is-resolved">✓ Resuelta</span>@endif
                    </div>
                </article>
            @empty
                <div class="profile-panel forum-empty"><h2>Aún no hay preguntas</h2><p>La primera duda puede abrir una gran conversación.</p><a href="{{ auth()->check() ? route('questions.create') : route('login') }}" class="profile-btn profile-btn-primary">Hacer una pregunta</a></div>
            @endforelse
        </section>
        {{ $questions->links() }}
    </div>
</main>
<x-footer />
</body>
</html>
