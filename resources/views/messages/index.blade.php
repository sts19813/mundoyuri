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
<body class="messenger-body">
    <x-navbar />

    <main class="messenger-page">
        <div class="messenger-ambient messenger-ambient-one"></div>
        <div class="messenger-ambient messenger-ambient-two"></div>
        <div class="container-xl messenger-container">
            @if(session('success'))
                <div class="portal-alert portal-alert-success" role="status">{{ session('success') }}</div>
            @endif

            <section class="messenger-shell messenger-shell-empty">
                @include('messages._sidebar')

                <div class="messenger-welcome">
                    <div class="messenger-welcome-icon" aria-hidden="true">
                        <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="m8 12 2.5 2L16 9"/></svg>
                    </div>
                    <h2>Tus mensajes</h2>
                    <p>Selecciona una conversación para continuar el chat.</p>
                    <a class="profile-btn profile-btn-primary" href="{{ route('community.members') }}">Nueva conversación</a>
                </div>
            </section>
        </div>
    </main>

</body>
</html>
