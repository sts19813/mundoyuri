<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $series->title }} · {{ $episode->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
<x-navbar />

@php
    $primary = $episode->sources->firstWhere('is_primary', true) ?: $episode->sources->first();
@endphp

<section class="py-5 mt-5">
    <div class="container-xl px-4">
        <div class="mb-3 text-muted">
            <a href="{{ route('home') }}">Inicio</a> / <a href="{{ route('catalog.series.show', $series->slug) }}">{{ $series->title }}</a> / Episodio {{ $episode->episode_number }}
        </div>

        <h1 class="section-title">{{ $series->title }} · S{{ $episode->season_number }}E{{ $episode->episode_number }} · {{ $episode->title }}</h1>
        <p class="text-muted">{{ $episode->description ?: 'Sin descripcion disponible.' }}</p>

        <div class="card mb-4">
            <div class="card-body">
                @if($primary)
                    <div class="ratio ratio-16x9 mb-3">
                        <iframe src="{{ $primary->video_url }}" title="Video" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">Este episodio aun no tiene fuente de video disponible.</div>
                @endif

                <h5 class="mt-3">Fuentes disponibles</h5>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($episode->sources as $source)
                        <a class="btn {{ $source->is_primary ? 'btn-primary' : 'btn-light' }}" href="{{ $source->video_url }}" target="_blank" rel="noopener">
                            {{ strtoupper($source->provider) }}{{ $source->label ? ' · '.$source->label : '' }}
                        </a>
                    @empty
                        <span class="text-muted">Sin fuentes registradas.</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="mb-3">Lista de episodios</h4>
                <div class="list-group">
                    @foreach($episodes as $item)
                        <a href="{{ route('catalog.episodes.show', [$series->slug, $item->slug]) }}" class="list-group-item list-group-item-action {{ $item->id === $episode->id ? 'active' : '' }}">
                            S{{ $item->season_number }}E{{ $item->episode_number }} · {{ $item->title }}
                            <small class="d-block {{ $item->id === $episode->id ? 'text-white-50' : 'text-muted' }}">{{ optional($item->release_date)->format('d/m/Y') ?: 'Sin fecha' }}</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        @include('catalog.partials.comments', ['comments' => $episode->comments, 'targetType' => 'episode', 'targetId' => $episode->id])
    </div>
</section>

<x-footer />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
