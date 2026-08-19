<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $featuredSeries = $featuredSeries ?? collect();
        $latestEpisodes = $latestEpisodes ?? collect();
        $seriesCount = $seriesCount ?? 0;
        $section = $section ?? null;
        $sectionName = $section?->name ?? 'Anime';
        $sectionLabel = $section?->label ?: $sectionName;
        $sectionUrl = $section?->slug ? route('catalog.sections.show', $section->slug) : route('home');
        $catalogUrl = route('catalog.series.index', ['section' => $section?->slug]);
        $homeSeoDescription = $section?->hero_description ?: 'Explora el catálogo de Mundo Yuri.';
    @endphp
    <x-seo title="Mundo Yuri: {{ $sectionName }}" :description="$homeSeoDescription" :canonical="$sectionUrl" />
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar :transparent="true" />

    <section class="hero">
        @if($section?->heroVideoEmbedUrl())
            <iframe id="heroYoutubeVideo" class="hero-video" src="{{ $section->heroVideoEmbedUrl() }}" title="Video de fondo de {{ $sectionName }}" allow="autoplay; encrypted-media" referrerpolicy="strict-origin-when-cross-origin" tabindex="-1" aria-hidden="true"></iframe>
        @elseif($section?->hasDirectVideo())
            <video class="hero-video is-ready" autoplay muted loop playsinline aria-hidden="true"><source src="{{ $section->hero_video_url }}"></video>
        @endif
        <div class="hero-overlay"></div><div class="hero-grain"></div>
        <div class="hero-content container-xl px-4 pt-5">
            <div class="hero-tag"><span class="brand-heart" style="width:14px;height:14px;"></span>{{ $section?->hero_eyebrow ?: $sectionName }}</div>
            <h1>{{ $section?->hero_title ?: 'Historias para descubrir, sentir y compartir' }}</h1>
            @if($section?->hero_description)<p class="hero-desc">{{ $section->hero_description }}</p>@endif
            <div class="hero-actions">
                <a href="{{ $catalogUrl }}" class="btn-rose"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z" /></svg>{{ $section?->hero_primary_label ?: 'Explorar catálogo' }}</a>
                <a href="#novedades" class="btn-ghost">{{ $section?->hero_secondary_label ?: 'Ver novedades' }}</a>
            </div>
        </div>
    </section>

    <section class="episodes-section" id="novedades">
        <div class="container-xl px-4">
            <div class="section-header"><h2 class="section-title">Últimos episodios</h2><a href="{{ route('legacy.episodios') }}" class="section-link">Ver todo →</a></div>
            <div class="row g-3">
                @forelse($latestEpisodes->take(4) as $episode)
                    <div class="col-6 col-md-3"><a href="{{ route('public.episodes.show', $episode->slug) }}" class="episode-card"><div class="episode-thumb"><x-media-preview :src="$episode->series?->bannerMediaUrl() ?: $episode->previewMediaUrl('640/360')" :type="$episode->series?->bannerMediaUrl() ? $episode->series->bannerMediaType() : $episode->previewMediaType()" :alt="$episode->title" class="episode-thumb-media" /><span class="ep-live"></span><div class="ep-play-btn"><div class="ep-play-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><path d="M8 5v14l11-7z" /></svg></div></div></div><div class="episode-info"><h6>Episodio {{ $episode->episode_number }}</h6><small>{{ $episode->series->title ?? 'Serie desconocida' }}</small></div></a></div>
                @empty
                    <div class="col-12"><div class="episode-card p-4">Aún no hay episodios publicados de {{ $sectionName }}.</div></div>
                @endforelse
            </div>
        </div>
    </section>

    <section>
        <div class="container-xl px-4">
            <div class="section-header"><h2 class="section-title">Destacados de {{ $sectionName }}</h2><a href="{{ $catalogUrl }}" class="section-link">Ver catálogo →</a></div>
            <div class="featured-rail" id="featuredRail">
                @forelse($featuredSeries as $item)
                    <a href="{{ route('catalog.series.show', $item->slug) }}" class="featured-card"><x-media-preview :src="$item->bannerMediaUrl() ?: 'https://picsum.photos/800/400?'.$item->id" :type="$item->bannerMediaUrl() ? $item->bannerMediaType() : 'image'" :alt="$item->title" class="featured-card-media" :hover-play="$item->bannerMediaType() === 'video'" /><div class="featured-card-overlay"></div><div class="featured-card-body"><span class="featured-card-badge">{{ $sectionLabel }} · {{ $item->content_type === 'movie' ? 'Película' : 'Serie' }}</span><h5>{{ $item->title }}</h5><small>{{ $item->release_year ?: 'S/F' }} · {{ $item->status === 'completed' ? 'Completada' : 'En curso' }}</small></div></a>
                @empty
                    <div class="featured-card"><x-media-preview src="https://picsum.photos/800/400?empty-featured" type="image" alt="Sin contenido" class="featured-card-media" /><div class="featured-card-overlay"></div><div class="featured-card-body"><span class="featured-card-badge">{{ $sectionLabel }}</span><h5>Próximamente</h5><small>Esta sección aún no tiene títulos publicados.</small></div></div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="catalog-section">
        <div class="container-xl px-4">
            <div class="section-header"><h2 class="section-title">{{ $sectionName }}</h2><div style="display:flex;align-items:center;gap:16px;"><span style="color:var(--muted);font-size:.85rem;">{{ $seriesCount }} {{ $seriesCount === 1 ? 'título' : 'títulos' }}</span><a href="{{ $catalogUrl }}" class="section-link">Ver todo →</a></div></div>
            <div class="row g-3">
                @forelse($featuredSeries as $item)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2"><a href="{{ route('catalog.series.show', $item->slug) }}" class="catalog-card"><div class="catalog-poster"><x-media-preview :src="$item->coverMediaUrl() ?: 'https://picsum.photos/300/420?series-'.$item->id" :type="$item->coverMediaUrl() ? $item->coverMediaType() : 'image'" :alt="$item->title" class="catalog-poster-media" :hover-play="$item->coverMediaType() === 'video'" /></div><div class="catalog-info"><h6>{{ $item->title }}</h6><small>{{ $sectionLabel }} · {{ $item->content_type === 'movie' ? 'Película' : 'Serie' }}</small></div></a></div>
                @empty
                    <div class="col-12"><div class="catalog-card p-4">Aún no hay títulos publicados de {{ $sectionName }}.</div></div>
                @endforelse
            </div>
        </div>
    </section>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', () => document.getElementById('navbar')?.classList.toggle('scrolled', window.scrollY > 40));
        const heroYoutubeVideo = document.getElementById('heroYoutubeVideo');
        heroYoutubeVideo?.addEventListener('load', () => window.setTimeout(() => heroYoutubeVideo.classList.add('is-ready'), 3000));
    </script>
    @include('partials.hover-media-script')
</body>
</html>
