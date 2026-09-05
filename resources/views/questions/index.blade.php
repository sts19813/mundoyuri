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
                <h1>Preguntas <em>Mundo Yuri</em></h1>
                <p>Encuentra esa historia GL, pide una recomendación o comparte lo que sabes.</p>
                <form method="GET" class="forum-search">
                    <label class="visually-hidden" for="question-search">Buscar preguntas</label>
                    <input id="question-search" name="q" type="search" value="{{ $search }}" placeholder="¿Qué estás buscando?">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <button type="submit">Buscar</button>
                </form>
            </div>
            @auth <a href="{{ route('questions.create') }}" class="profile-btn profile-btn-primary">Hacer una pregunta</a> @endauth
        </header>

        <nav class="question-sorts" aria-label="Ordenar preguntas">
            @foreach(['recent' => 'Recientes', 'unanswered' => 'Sin respuestas', 'popular' => 'Populares'] as $value => $label)
                <a class="{{ $sort === $value ? 'is-active' : '' }}" @if($sort === $value) aria-current="page" @endif href="{{ route('questions.index', array_filter(['sort' => $value, 'q' => $search ?: null])) }}">{{ $label }}</a>
            @endforeach
        </nav>

        @if(session('success'))<div class="legacy-profile-notice mt-3"><strong>Listo</strong><span>{{ session('success') }}</span></div>@endif
        @if(session('error'))<div class="legacy-profile-notice mt-3"><strong>Atención</strong><span>{{ session('error') }}</span></div>@endif

        <section class="forum-category question-directory" aria-label="Preguntas de la comunidad">
            <header><span aria-hidden="true">♡</span><div><h2>{{ $search !== '' ? 'Resultados de tu búsqueda' : 'Nos ayudamos entre todas' }}</h2><p>{{ number_format($questions->total()) }} {{ $questions->total() === 1 ? 'pregunta' : 'preguntas' }} · Anime, manga, series y comunidad</p></div></header>
            @forelse($questions as $question)
                <article class="question-row question-directory-row">
                    <span class="forum-row-icon" aria-hidden="true">{{ $question->isResolved() ? '✓' : '?' }}</span>
                    <div class="question-row-main">
                        <h2><a href="{{ route('questions.show', $question) }}">{{ $question->title }}</a></h2>
                        <p>Por @if($question->author)<a href="{{ $question->author->publicProfileUrl() }}">{{ $question->author->displayName() }}</a>@else{{ $question->authorName() }}@endif · {{ $question->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="question-row-meta">
                        <span>{{ number_format($question->replies_count) }} {{ $question->replies_count === 1 ? 'respuesta' : 'respuestas' }}</span>
                        @if($question->isResolved())<span class="is-resolved">✓ Resuelta</span>@else<span>{{ $question->replies_count ? 'Conversando' : '¿Puedes ayudar?' }}</span>@endif
                    </div>
                </article>
            @empty
                <div class="forum-empty"><h2>{{ $search !== '' ? 'No encontramos esa pregunta' : 'Este espacio está esperando tu pregunta' }}</h2><p>{{ $search !== '' ? 'Prueba con otras palabras o pregunta a la comunidad.' : '¿Una serie que no recuerdas? ¿Buscas tu próxima lectura? Pregunta aquí.' }}</p><a href="{{ auth()->check() ? route('questions.create') : route('login') }}" class="profile-btn profile-btn-primary">Hacer una pregunta</a></div>
            @endforelse
        </section>
        {{ $questions->links() }}
    </div>
</main>
<x-footer />
</body>
</html>
