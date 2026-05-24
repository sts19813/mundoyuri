<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $series->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
<x-navbar />

<section class="py-5 mt-5">
    <div class="container-xl px-4">
        <div class="card border-0 bg-dark text-white mb-4">
            <img src="{{ $series->banner_image ?: ($series->cover_image ?: 'https://picsum.photos/1400/500?series='.$series->id) }}" class="card-img" style="max-height:340px;object-fit:cover;opacity:0.4;" alt="{{ $series->title }}">
            <div class="card-img-overlay d-flex flex-column justify-content-end">
                <h1 class="display-6">{{ $series->title }}</h1>
                <p class="mb-2">{{ $series->genre->name }} · {{ $series->content_type === 'series' ? 'Serie' : 'Pelicula' }} · {{ ucfirst($series->status) }}</p>
                <p class="mb-0">{{ $series->description }}</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="mb-3">Informacion</h4>
                        <div class="row g-2">
                            <div class="col-md-6"><strong>Pais:</strong> {{ $series->country_of_origin ?: 'N/D' }}</div>
                            <div class="col-md-6"><strong>Ano:</strong> {{ $series->release_year ?: 'N/D' }}</div>
                            <div class="col-md-6"><strong>Duracion:</strong> {{ $series->duration_minutes ? $series->duration_minutes.' min' : 'N/D' }}</div>
                            <div class="col-md-6"><strong>Episodios:</strong> {{ $series->episodes->count() }}</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3">Episodios</h4>
                        @forelse($series->episodes as $episode)
                            <a href="{{ route('catalog.episodes.show', [$series->slug, $episode->slug]) }}" class="d-flex justify-content-between align-items-center text-decoration-none border rounded p-3 mb-2">
                                <div>
                                    <strong>S{{ $episode->season_number }}E{{ $episode->episode_number }}</strong> · {{ $episode->title }}
                                    <div class="text-muted small">{{ optional($episode->release_date)->format('d/m/Y') ?: 'Sin fecha' }}</div>
                                </div>
                                <span class="badge bg-primary">Ver</span>
                            </a>
                        @empty
                            <div class="alert alert-light mb-0">No hay episodios aprobados para esta serie.</div>
                        @endforelse
                    </div>
                </div>

                @include('catalog.partials.comments', ['comments' => $series->comments, 'targetType' => 'series', 'targetId' => $series->id])
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Acciones</h5>
                        @auth
                            <a href="{{ route('submissions.create') }}" class="btn btn-primary w-100 mb-2">Subir nuevo contenido</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary w-100 mb-2">Inicia sesion para aportar</a>
                        @endauth
                        <a href="{{ route('catalog.series.index') }}" class="btn btn-light w-100">Volver al catalogo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<x-footer />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
