<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $genre->name }} · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
<x-navbar />

<section class="py-5 mt-5">
    <div class="container-xl px-4">
        <h1 class="section-title mb-1">Genero: {{ $genre->name }}</h1>
        <p class="text-muted mb-4">{{ $genre->description ?: 'Sin descripcion.' }}</p>

        <div class="row g-3">
            @forelse($series as $item)
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="{{ route('catalog.series.show', $item->slug) }}" class="text-decoration-none">
                        <div class="catalog-card h-100">
                            <div class="catalog-poster">
                                <x-media-preview
                                    :src="$item->coverMediaUrl() ?: 'https://picsum.photos/300/420?genre='.$item->id"
                                    :type="$item->coverMediaUrl() ? $item->coverMediaType() : 'image'"
                                    :alt="$item->title"
                                    class="catalog-poster-media"
                                    :hover-play="$item->coverMediaType() === 'video'"
                                />
                            </div>
                            <div class="catalog-info">
                                <h6>{{ $item->title }}</h6>
                                <small>{{ $item->content_type === 'series' ? 'Serie' : 'Pelicula' }}</small>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-light">Aun no hay titulos aprobados para este genero.</div></div>
            @endforelse
        </div>

        <div class="mt-4">{{ $series->links() }}</div>
    </div>
</section>

<x-footer />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@include('partials.hover-media-script')
</body>
</html>
