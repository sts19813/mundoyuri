<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-seo
        title="Catálogo de contenido GL"
        description="Explora el catálogo de series, películas y doramas Girls' Love aprobados por la comunidad de Mundo Yuri."
        :canonical="route('catalog.series.index')"
    />
    <x-portal-favicon />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
<x-navbar />

<section class="hero">
    <video class="hero-video" autoplay muted loop playsinline>
        <source src="/assets/video/BG.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-grain"></div>
    <div class="hero-content container-xl px-4 pt-5">
        <div class="hero-tag">Catalogo validado por moderacion</div>
        <h1>Series y peliculas <em>GL</em> con aportes de la comunidad</h1>
        <p class="hero-desc">Todo contenido nuevo entra en revision antes de publicarse.</p>
        <div class="hero-actions">
            <a href="{{ route('catalog.series.index') }}" class="btn-rose">Explorar catalogo</a>
            @auth
                <a href="{{ route('submissions.create') }}" class="btn-ghost">Subir contenido</a>
            @else
                <a href="{{ route('register') }}" class="btn-ghost">Crear cuenta</a>
            @endauth
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container-xl px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">Destacadas</h2>
            <a href="{{ route('catalog.series.index') }}" class="section-link">Ver catalogo</a>
        </div>
        <div class="row g-3">
            @forelse($featuredSeries as $item)
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="{{ route('catalog.series.show', $item->slug) }}" class="text-decoration-none">
                        <div class="catalog-card">
                            <div class="catalog-poster">
                                <x-media-preview
                                    :src="$item->coverMediaUrl() ?: 'https://picsum.photos/300/420?random='.$item->id"
                                    :type="$item->coverMediaUrl() ? $item->coverMediaType() : 'image'"
                                    :alt="$item->title"
                                    class="catalog-poster-media"
                                    :hover-play="$item->coverMediaType() === 'video'"
                                />
                            </div>
                            <div class="catalog-info">
                                <h6>{{ $item->title }}</h6>
                                <small>{{ $item->genre->name }} · {{ $item->content_type === 'series' ? 'Serie' : 'Pelicula' }}</small>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-light">No hay contenido destacado aprobado.</div></div>
            @endforelse
        </div>
    </div>
</section>

<section class="episodes-section">
    <div class="container-xl px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">Ultimos episodios</h2>
            <a href="{{ route('catalog.series.index') }}" class="section-link">Ir a series</a>
        </div>
        <div class="row g-3">
            @forelse($latestEpisodes as $episode)
                <div class="col-6 col-md-3">
                    <a href="{{ route('catalog.episodes.show', [$episode->series->slug, $episode->slug]) }}" class="text-decoration-none">
                        <div class="episode-card">
                            <div class="episode-thumb">
                                <img src="{{ $episode->imageUrl('400/250') }}" alt="{{ $episode->title }}">
                            </div>
                            <div class="episode-info">
                                <h6>S{{ $episode->season_number }}E{{ $episode->episode_number }} · {{ $episode->title }}</h6>
                                <small>{{ $episode->series->title }}</small>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-light">No hay episodios aprobados todavia.</div></div>
            @endforelse
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container-xl px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">Generos</h2>
            <a href="{{ route('catalog.genres.index') }}" class="section-link">Ver todos</a>
        </div>
        <div class="row g-3">
            @foreach($genres as $genre)
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="{{ route('catalog.genres.show', $genre->slug) }}" class="btn btn-dark w-100 py-3">
                        {{ $genre->name }}
                        <span class="badge bg-secondary ms-2">{{ $genre->series_count }}</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<x-footer />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.addEventListener('scroll', () => {
    const nav = document.getElementById('navbar');
    if (nav) nav.classList.toggle('scrolled', window.scrollY > 10);
});
const toggler = document.getElementById('navToggler');
if (toggler) {
    toggler.addEventListener('click', () => document.getElementById('navLinks')?.classList.toggle('active'));
}
</script>
@include('partials.hover-media-script')
</body>
</html>
