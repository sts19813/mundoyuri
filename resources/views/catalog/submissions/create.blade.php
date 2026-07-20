<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Subir contenido · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <style>
        .submission-page { min-height: 100vh; padding: 120px 0 72px; background: var(--dark); color: #fff; }
        .submission-page .card { border: 1px solid rgba(244,63,142,.15); background: var(--dark-card); color: #fff; }
        .submission-page .text-muted, .submission-page .form-text { color: var(--muted) !important; }
        .submission-page .form-control, .submission-page .form-select { border-color: rgba(255,255,255,.12); background-color: rgba(255,255,255,.06); color: #fff; }
        .submission-page .form-control:focus, .submission-page .form-select:focus { border-color: var(--rose); box-shadow: 0 0 0 .2rem rgba(244,63,142,.12); }
        .submission-page .form-select option { color: #111; }
        .submission-page .btn-primary { border-color: var(--rose); background: var(--rose); }
        .submission-page .card-footer { border-color: rgba(255,255,255,.08); }
    </style>
</head>
<body>
    <x-navbar />
    <main class="submission-page">
        <div class="container-xl px-4 d-flex flex-column gap-5">
        <div class="card">
            <div class="card-body">
                <h1 class="fs-2hx fw-bold mb-2">Subir contenido</h1>
                <p class="text-muted mb-0">Todo aporte entra en estado <strong>pendiente</strong> hasta ser validado por el equipo.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="card" method="POST" action="{{ route('submissions.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <h4 class="mb-3">Datos de la serie o película</h4>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label required">Género</label>
                        <select class="form-select form-select-solid" name="genre_id" required>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->id }}">{{ $genre->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Tipo</label>
                        <select class="form-select form-select-solid" name="content_type" required>
                            <option value="series">Serie</option>
                            <option value="movie">Película</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Estado</label>
                        <select class="form-select form-select-solid" name="status" required>
                            <option value="ongoing">En emisión</option>
                            <option value="completed">Completada</option>
                            <option value="upcoming">Próximamente</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label required">Título</label>
                        <input class="form-control form-control-solid" name="title" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label required">Descripción</label>
                        <textarea class="form-control form-control-solid" name="description" rows="4" required>{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">País de origen</label>
                        <input class="form-control form-control-solid" name="country_of_origin" value="{{ old('country_of_origin') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Año de lanzamiento</label>
                        <input class="form-control form-control-solid" type="number" name="release_year" value="{{ old('release_year') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Duración (min)</label>
                        <input class="form-control form-control-solid" type="number" name="duration_minutes" value="{{ old('duration_minutes') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Archivo banner</label>
                        <input class="form-control form-control-solid" type="file" name="banner_image" accept="image/*,video/*">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Archivo portada</label>
                        <input class="form-control form-control-solid" type="file" name="cover_image" accept="image/*,video/*">
                        <div class="form-text">La portada es la carátula que se muestra en portada, series y catálogo.</div>
                    </div>
                </div>

                <div class="separator my-8"></div>

                <h4 class="mb-3">Primer episodio (opcional)</h4>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Título del episodio</label>
                        <input class="form-control form-control-solid" name="episode_title" value="{{ old('episode_title') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Temporada</label>
                        <input class="form-control form-control-solid" type="number" name="season_number" value="{{ old('season_number', 1) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Número episodio</label>
                        <input class="form-control form-control-solid" type="number" name="episode_number" value="{{ old('episode_number', 1) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha publicación</label>
                        <input class="form-control form-control-solid" type="date" name="episode_release_date" value="{{ old('episode_release_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Duración episodio (min)</label>
                        <input class="form-control form-control-solid" type="number" name="episode_duration_minutes" value="{{ old('episode_duration_minutes') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Proveedor video</label>
                        <select class="form-select form-select-solid" name="source_provider">
                            <option value="">Selecciona una fuente</option>
                            @foreach($sourceProviders as $providerKey => $providerConfig)
                                <option value="{{ $providerKey }}" @selected(old('source_provider') === $providerKey)>{{ $providerConfig['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">URL de reproducción</label>
                        <input class="form-control form-control-solid" type="text" name="source_url" value="{{ old('source_url') }}">
                        <div class="form-text">Si eliges Bunny Stream, puedes pegar el Video ID, embed URL o play URL.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Etiqueta de fuente</label>
                        <input class="form-control form-control-solid" name="source_label" value="{{ old('source_label') }}" placeholder="Ej. HD Sub ES">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción episodio</label>
                        <textarea class="form-control form-control-solid" rows="3" name="episode_description">{{ old('episode_description') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-3">
                <a href="{{ route('home') }}" class="btn btn-light">Cancelar</a>
                <button class="btn btn-primary" type="submit">Enviar a validación</button>
            </div>
        </form>
        </div>
    </main>
    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
