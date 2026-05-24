@extends('layouts.app')

@section('title', 'Subir contenido')

@section('content')
    <div class="d-flex flex-column gap-5">
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

        <form class="card" method="POST" action="{{ route('submissions.store') }}">
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
                        <label class="form-label">URL banner</label>
                        <input class="form-control form-control-solid" type="url" name="banner_image" value="{{ old('banner_image') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">URL portada</label>
                        <input class="form-control form-control-solid" type="url" name="cover_image" value="{{ old('cover_image') }}">
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
                            <option value="youtube">YouTube</option>
                            <option value="vimeo">Vimeo</option>
                            <option value="byse">BYSE</option>
                            <option value="voe">VOE</option>
                            <option value="ok">OK</option>
                            <option value="netu">NETU</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">URL de reproducción</label>
                        <input class="form-control form-control-solid" type="url" name="source_url" value="{{ old('source_url') }}">
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
                <a href="{{ route('dashboard') }}" class="btn btn-light">Cancelar</a>
                <button class="btn btn-primary" type="submit">Enviar a validación</button>
            </div>
        </form>
    </div>
@endsection
