<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>{{ $thread->title }} · Preguntas · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500,600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
<x-navbar />
<main class="portal-profile-page forum-page question-page"><div class="container-xl px-4 position-relative">
    <nav class="profile-breadcrumb"><a href="{{ route('questions.index') }}">Preguntas</a><span>›</span><span>{{ $thread->title }}</span></nav>
    <header class="forum-thread-header question-thread-header"><div><span class="profile-eyebrow">{{ $thread->isResolved() ? 'Pregunta resuelta' : 'Pregunta abierta' }}</span><h1>{{ $thread->title }}</h1><div class="question-tags">@foreach($thread->questionTags as $questionTag)<a href="{{ route('questions.index', ['tag' => $questionTag->slug]) }}">{{ $questionTag->name }}</a>@endforeach</div><p>{{ number_format($thread->views_count) }} vistas · {{ number_format($thread->replies_count) }} respuestas</p></div><div class="forum-thread-header-actions"><a href="{{ route('questions.index') }}" class="profile-btn profile-btn-soft">Ver preguntas</a>@auth <x-community.report-form :reportable="$thread" />@endauth</div></header>
    @if(session('success'))<div class="legacy-profile-notice mt-3"><strong>Listo</strong><span>{{ session('success') }}</span></div>@endif
    @if(session('error'))<div class="legacy-profile-notice mt-3"><strong>Atención</strong><span>{{ session('error') }}</span></div>@endif
    <section class="forum-posts">@php($previousUserId = null)@foreach($posts as $post)<x-forum.post :post="$post" :previous-user-id="$previousUserId" :question="$thread" :is-accepted="$thread->accepted_answer_post_id === $post->id" />@php($previousUserId = $post->user_id)@endforeach</section>
    {{ $posts->links() }}
    @auth
        @can('reply', $thread)<section class="forum-reply-section"><h2>Tu respuesta</h2><x-forum.composer :action="route('questions.answers.store', $thread)" submit="Publicar respuesta" /></section>@else <section class="profile-panel forum-readonly"><p>Esta pregunta está cerrada para nuevas respuestas.</p></section>@endcan
    @else <section class="profile-panel forum-readonly"><p><a href="{{ route('login') }}">Inicia sesión</a> para aportar una respuesta.</p></section>@endauth
</div></main>
<x-footer />
</body>
</html>
