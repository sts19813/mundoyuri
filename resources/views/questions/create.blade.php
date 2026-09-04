<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Nueva pregunta · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
<x-navbar />
<main class="portal-profile-page forum-page question-page"><div class="container-xl px-4">
    <nav class="profile-breadcrumb"><a href="{{ route('questions.index') }}">Preguntas</a><span>›</span><span>Nueva pregunta</span></nav>
    <header class="forum-forum-header"><div><span class="profile-eyebrow">Comunidad</span><h1>Haz una pregunta</h1><p>Da contexto para que otras miembros puedan ayudarte mejor.</p></div></header>
    <form method="POST" action="{{ route('questions.store') }}" class="forum-composer profile-panel">
        @csrf
        <div class="profile-field"><label for="question-title">Título</label><input id="question-title" name="title" maxlength="180" required value="{{ old('title') }}" placeholder="Resume tu duda con claridad" @error('title') aria-invalid="true" aria-describedby="question-title-error" @enderror>@error('title')<small id="question-title-error" class="profile-field-error">{{ $message }}</small>@enderror</div>
        <div class="profile-field"><label for="question-body">Descripción</label><textarea id="question-body" name="body" rows="8" maxlength="12000" required placeholder="Explica lo que sabes, has probado o buscas." @error('body') aria-invalid="true" aria-describedby="question-body-error" @enderror>{{ old('body') }}</textarea><small class="forum-composer-help">Texto plano: no se permite HTML. Máximo 12 000 caracteres.</small>@error('body')<small id="question-body-error" class="profile-field-error">{{ $message }}</small>@enderror</div>
        <button type="submit" class="profile-btn profile-btn-primary">Publicar pregunta</button>
    </form>
</div></main>
<x-footer />
</body>
</html>
