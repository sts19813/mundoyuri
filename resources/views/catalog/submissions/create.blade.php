<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir contenido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
<x-navbar />

<section class="py-5 mt-5">
    <div class="container-xl px-4">
        <h1 class="section-title mb-3">Subir contenido</h1>
        <div class="alert alert-info">Todo aporte entra en estado <strong>pendiente</strong> hasta ser validado por el equipo.</div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="card" method="POST" action="{{ route('submissions.store') }}">
            @csrf
            <div class="card-body">
                <h4 class="mb-3">Datos de la serie o pelicula</h4>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Genero</label>
                        <select class="form-select" name="genre_id" required>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->id }}">{{ $genre->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="content_type" required>
                            <option value="series">Serie</option>
                            <option value="movie">Pelicula</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="status" required>
                            <option value="ongoing">En emision</option>
                            <option value="completed">Completada</option>
                            <option value="upcoming">Proximamente</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Titulo</label>
                        <input class="form-control" name="title" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripcion</label>
                        <textarea class="form-control" name="description" rows="4" required>{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pais de origen</label>
                        <input class="form-control" name="country_of_origin" value="{{ old('country_of_origin') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ano de lanzamiento</label>
                        <input class="form-control" type="number" name="release_year" value="{{ old('release_year') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Duracion (min)</label>
                        <input class="form-control" type="number" name="duration_minutes" value="{{ old('duration_minutes') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">URL banner</label>
                        <input class="form-control" type="url" name="banner_image" value="{{ old('banner_image') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">URL portada</label>
                        <input class="form-control" type="url" name="cover_image" value="{{ old('cover_image') }}">
                    </div>
                </div>

                <hr class="my-4">
                <h4 class="mb-3">Primer episodio (opcional)</h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Titulo del episodio</label>
                        <input class="form-control" name="episode_title" value="{{ old('episode_title') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Temporada</label>
                        <input class="form-control" type="number" name="season_number" value="{{ old('season_number', 1) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Numero episodio</label>
                        <input class="form-control" type="number" name="episode_number" value="{{ old('episode_number', 1) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha publicacion</label>
                        <input class="form-control" type="date" name="episode_release_date" value="{{ old('episode_release_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Duracion episodio (min)</label>
                        <input class="form-control" type="number" name="episode_duration_minutes" value="{{ old('episode_duration_minutes') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Proveedor video</label>
                        <select class="form-select" name="source_provider">
                            <option value="">Selecciona una fuente</option>
                            <option value="youtube">YouTube</option>
                            <option value="vimeo">Vimeo</option>
                            <option value="byse">BYSE</option>
                            <option value="voe">VOE</option>
                            <option value="ok">OK</option>
                            <option value="netu">NETU</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">URL de reproduccion</label>
                        <input class="form-control" type="url" name="source_url" value="{{ old('source_url') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Etiqueta de fuente</label>
                        <input class="form-control" name="source_label" value="{{ old('source_label') }}" placeholder="Ej. HD Sub ES">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripcion episodio</label>
                        <textarea class="form-control" rows="3" name="episode_description">{{ old('episode_description') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('home') }}" class="btn btn-light">Cancelar</a>
                <button class="btn btn-primary" type="submit">Enviar a validacion</button>
            </div>
        </form>
    </div>
</section>

<x-footer />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
