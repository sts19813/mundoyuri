<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
<x-navbar />

<section class="py-5 mt-5">
    <div class="container-xl px-4">
        <h1 class="section-title mb-4">Generos</h1>
        <div class="row g-3">
            @forelse($genres as $genre)
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="{{ route('catalog.genres.show', $genre->slug) }}" class="btn btn-dark w-100 h-100 d-flex flex-column justify-content-center">
                        <span>{{ $genre->name }}</span>
                        <span class="badge bg-secondary mt-2">{{ $genre->series_count }} titulos</span>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-light">No hay generos activos.</div></div>
            @endforelse
        </div>
        <div class="mt-4">{{ $genres->links() }}</div>
    </div>
</section>

<x-footer />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
