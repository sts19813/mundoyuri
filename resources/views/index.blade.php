<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mundo GL</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>

<body>

    <x-navbar />

    @php
        $featuredSeries = $featuredSeries ?? collect();
        $latestEpisodes = $latestEpisodes ?? collect();
        $seriesCount = $seriesCount ?? $featuredSeries->count();
    @endphp

    <!-- ══ HERO ══ -->
    <section class="hero">
        <video class="hero-video" autoplay muted loop playsinline>
            <source src="/assets/video/BG.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-grain"></div>

        <div class="hero-content container-xl px-4 pt-5">
            <div class="hero-tag">
                <span class="brand-heart" style="width:14px;height:14px;"></span>
                Contenido GL · Actualizado diario
            </div>
            <h1>Descubre <em>nuevas historias</em> que te harán sentir</h1>
            <p class="hero-desc">Series, doramas y películas GL de todo el mundo. Subtituladas con amor, actualizadas
                cada día.</p>
            <div class="hero-actions">
                <a href="{{ route('legacy.episodios') }}" class="btn-rose">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                    Explorar ahora
                </a>
                <a href="{{ route('legacy.episodios') }}" class="btn-ghost">Ver novedades</a>
            </div>
        </div>
    </section>

    <!-- ══ CARRUSEL DESTACADO ══ -->
    <section>
        <div class="container-xl px-4">
            <div class="section-header">
                <h2 class="section-title">Doramas destacados</h2>
                <a href="{{ route('catalog.series.index') }}" class="section-link">Ver todo →</a>
            </div>

            <!-- Controles -->
            <div style="display:flex; gap:10px; margin-bottom:20px;">
                <button onclick="scrollRail(-1)"
                    style="background:var(--dark-card);border:1px solid rgba(244,63,142,0.2);color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:1rem;">‹</button>
                <button onclick="scrollRail(1)"
                    style="background:var(--dark-card);border:1px solid rgba(244,63,142,0.2);color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:1rem;">›</button>
            </div>

            <div class="featured-rail" id="featuredRail">
                @forelse($featuredSeries as $series)
                    <a href="{{ route('catalog.series.show', $series->slug) }}" class="featured-card">
                        <x-media-preview
                            :src="$series->bannerMediaUrl() ?: 'https://picsum.photos/800/400?'.$series->id"
                            :type="$series->bannerMediaUrl() ? $series->bannerMediaType() : 'image'"
                            :alt="$series->title"
                            class="featured-card-media"
                            :hover-play="$series->bannerMediaType() === 'video'"
                        />
                        <div class="featured-card-overlay"></div>
                        @if($series->status === 'ongoing')
                            <div class="live-dot"></div>
                        @endif
                        <div class="featured-card-body">
                            <span class="featured-card-badge">{{ ucfirst($series->content_type) }}</span>
                            <h5>{{ $series->title }}</h5>
                            <small>{{ $series->release_year ?: 'S/F' }} · {{ $series->status === 'completed' ? 'Completada' : 'En curso' }}</small>
                        </div>
                    </a>
                @empty
                    <div class="featured-card">
                        <x-media-preview src="https://picsum.photos/800/400?empty-featured" type="image" alt="Sin contenido" class="featured-card-media" />
                        <div class="featured-card-overlay"></div>
                        <div class="featured-card-body">
                            <span class="featured-card-badge">Catálogo</span>
                            <h5>Aún no hay series publicadas</h5>
                            <small>Vuelve más tarde</small>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ══ ÚLTIMOS EPISODIOS ══ -->
    <section class="episodes-section">
        <div class="container-xl px-4">
            <div class="section-header">
                <h2 class="section-title">Últimos episodios</h2>
                <a href="{{ route('legacy.episodios') }}" class="section-link">Ver todo →</a>
            </div>
            <div class="row g-3">
                @forelse($latestEpisodes->take(4) as $episode)
                    <div class="col-6 col-md-3">
                        <a href="{{ route('public.episodes.show', $episode->slug) }}" class="episode-card">
                            <div class="episode-thumb">
                                <x-media-preview
                                    :src="$episode->series?->bannerMediaUrl() ?: $episode->previewMediaUrl('640/360')"
                                    :type="$episode->series?->bannerMediaUrl() ? $episode->series->bannerMediaType() : $episode->previewMediaType()"
                                    :alt="$episode->title"
                                    class="episode-thumb-media"
                                    :hover-play="($episode->series?->bannerMediaUrl() ? $episode->series->bannerMediaType() : $episode->previewMediaType()) === 'video'"
                                />
                                @if(optional($episode->published_at)->gte(now()->subDays(7)))
                                    <span class="ep-badge-new">Nuevo</span>
                                @endif
                                <span class="ep-live"></span>
                                <div class="ep-play-btn">
                                    <div class="ep-play-icon">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="episode-info">
                                <h6>Episodio {{ $episode->episode_number }}</h6>
                                <small>{{ $episode->series->title ?? 'Serie desconocida' }}</small>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="episode-card p-4">Aún no hay episodios publicados.</div>
                    </div>
                @endforelse

            </div>
        </div>
    </section>

    <!-- ══ CATÁLOGO ══ -->
    <section class="catalog-section">
        <div class="container-xl px-4">
            <div class="section-header">
                <h2 class="section-title">Series GL</h2>
                <div style="display:flex;align-items:center;gap:16px;">
                    <span style="color:var(--muted);font-size:0.85rem;">{{ $seriesCount }} series</span>
                    <a href="{{ route('catalog.series.index') }}" class="section-link">Ver todo →</a>
                </div>
            </div>
            <div class="row g-3">
                @forelse($featuredSeries as $series)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="{{ route('catalog.series.show', $series->slug) }}" class="catalog-card">
                            <div class="catalog-poster">
                                <x-media-preview
                                    :src="$series->coverMediaUrl() ?: 'https://picsum.photos/300/420?series-'.$series->id"
                                    :type="$series->coverMediaUrl() ? $series->coverMediaType() : 'image'"
                                    :alt="$series->title"
                                    class="catalog-poster-media"
                                    :hover-play="$series->coverMediaType() === 'video'"
                                />
                                <div class="catalog-poster-overlay">
                                    <div class="cat-play">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="catalog-info">
                                <h6>{{ $series->title }}</h6>
                                <small>{{ optional($series->published_at)->format('d/m/Y') ?: 'Sin fecha' }}</small>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="catalog-card p-4">Aún no hay series publicadas.</div>
                    </div>
                @endforelse

            </div>
        </div>
    </section>

    <x-footer />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 40);
        });

        // Rail scroll
        function scrollRail(dir) {
            const rail = document.getElementById('featuredRail');
            rail.scrollBy({ left: dir * 320, behavior: 'smooth' });
        }


        // Toggle menu en móvil
        document.getElementById('navToggler').addEventListener('click', function () {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        });

        // Cerrar menú cuando se hace clic en un enlace
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', function () {
                const navLinks = document.getElementById('navLinks');
                navLinks.classList.remove('active');
            });
        });

    </script>
    @include('partials.hover-media-script')
</body>

</html>
