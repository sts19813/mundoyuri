@extends('layouts.portal')

@section('head')

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $featuredSeries = $featuredSeries ?? collect();
        $latestEpisodes = $latestEpisodes ?? collect();
        $mixedSeries = $mixedSeries ?? collect();
        $seriesCount = $seriesCount ?? 0;
        $section = $section ?? null;
        $isMixedHome = $isMixedHome ?? false;
        $sectionName = $section?->name ?? 'Anime';
        $sectionLabel = $section?->label ?: $sectionName;
        $sectionUrl = $isMixedHome ? route('home') : ($section?->slug ? route('catalog.sections.show', $section->slug) : route('home'));
        $catalogUrl = route('catalog.series.index', ['section' => $section?->slug]);
        $continueWatching = $continueWatching ?? collect();
        $homeSeoDescription = $isMixedHome
            ? 'Mira series GL, yuri y anime gratis con subtítulos en español. Descubre episodios, estrenos y recomendaciones Girls Love en Mundo Yuri.'
            : ($section?->hero_description ?: 'Explora el catálogo de Mundo Yuri.');
        $homeSeoSchema = $isMixedHome ? [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    '@id' => route('home').'#website',
                    'name' => 'Mundo Yuri',
                    'url' => route('home'),
                    'inLanguage' => 'es-MX',
                ],
                [
                    '@type' => 'CollectionPage',
                    '@id' => route('home').'#webpage',
                    'name' => 'Series GL, yuri y anime con subtítulos en español',
                    'description' => $homeSeoDescription,
                    'url' => route('home'),
                    'inLanguage' => 'es-MX',
                    'isPartOf' => ['@id' => route('home').'#website'],
                    'about' => ['Girls Love', 'Yuri', 'Anime', 'Series GL'],
                ],
            ],
        ] : null;
        $heroDesktopImageUrl = $section?->heroDesktopImageUrl() ?: '/assets/img/wallpaper-login.webp';
        $heroMobileImageUrl = $section?->heroMobileImageUrl() ?: $heroDesktopImageUrl;
        $heroPrimaryUrl = $section?->heroButtonUrl($section->hero_primary_url, $catalogUrl) ?? $catalogUrl;
        $heroSecondaryUrl = $section?->heroButtonUrl($section->hero_secondary_url, '#novedades') ?? '#novedades';
    @endphp
    <x-seo title="{{ $isMixedHome ? 'Series GL, Yuri y Anime Gratis Sub Español' : 'Mundo Yuri: '.$sectionName }}" :description="$homeSeoDescription" :canonical="$sectionUrl" :schema="$homeSeoSchema" />
    <x-portal-favicon />
    <link rel="preload" href="{{ asset('assets/fonts/dm-sans-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('assets/fonts/playfair-display-latin-700.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" as="image" href="{{ $heroDesktopImageUrl }}" media="(min-width: 769px)" fetchpriority="high">
    <link rel="preload" as="image" href="{{ $heroMobileImageUrl }}" media="(max-width: 768px)" fetchpriority="high">
    <link rel="stylesheet" href="{{ asset('assets/css/portal-fonts.css') }}?v={{ filemtime(public_path('assets/css/portal-fonts.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/home-grid.css') }}?v={{ filemtime(public_path('assets/css/home-grid.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">


@endsection

@section('body')

<x-navbar :transparent="true" />

    <section @class(['hero', 'hero-video-on-mobile' => $section?->hero_video_on_mobile])>
        <picture class="hero-cover-media" aria-hidden="true">
            <source media="(max-width: 768px)" srcset="{{ $heroMobileImageUrl }}">
            <img class="hero-cover-image" src="{{ $heroDesktopImageUrl }}" alt="" loading="eager" fetchpriority="high" decoding="async">
        </picture>
        @if($section?->heroVideoEmbedUrl())
            <iframe id="heroYoutubeVideo" class="hero-video" @if($section->hero_video_on_mobile) src="{{ $section->heroVideoEmbedUrl() }}" @else data-desktop-src="{{ $section->heroVideoEmbedUrl() }}" @endif title="Video de fondo de {{ $sectionName }}" allow="autoplay; encrypted-media" referrerpolicy="strict-origin-when-cross-origin" tabindex="-1" aria-hidden="true"></iframe>
        @elseif($section?->hasDirectVideo())
            <video class="hero-video is-ready" autoplay muted loop playsinline aria-hidden="true"><source src="{{ $section->hero_video_url }}"></video>
        @endif
        <div class="hero-overlay"></div><div class="hero-grain"></div>
        <div class="hero-content container-xl px-4 pt-5">
            <div class="hero-tag"><span class="brand-heart" style="width:14px;height:14px;"></span>{{ $section?->hero_eyebrow ?: $sectionName }}</div>
            <h1>{{ $section?->hero_title ?: 'Historias para descubrir, sentir y compartir' }}</h1>
            @if($section?->hero_description)<p class="hero-desc">{{ $section->hero_description }}</p>@endif
            @if(($section?->hero_primary_enabled ?? true) || ($section?->hero_secondary_enabled ?? true))
                <div class="hero-actions">
                    @if($section?->hero_primary_enabled ?? true)
                        <a href="{{ $heroPrimaryUrl }}" class="btn-rose"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z" /></svg>{{ $section?->hero_primary_label ?: 'Explorar catálogo' }}</a>
                    @endif
                    @if($section?->hero_secondary_enabled ?? true)
                        <a href="{{ $heroSecondaryUrl }}" class="btn-ghost">{{ $section?->hero_secondary_label ?: 'Ver novedades' }}</a>
                    @endif
                </div>
            @endif
        </div>
    </section>

    @if($section?->heroVideoEmbedUrl() && ! $section->hero_video_on_mobile)
        <script>
            (() => {
                if (window.matchMedia('(max-width: 768px)').matches) return;
                const video = document.querySelector('#heroYoutubeVideo[data-desktop-src]');
                if (video) video.src = video.dataset.desktopSrc;
            })();
        </script>
    @endif

    @include('partials.home.continue-watching-section', ['continueWatching' => $continueWatching])

    @if($isMixedHome)
        @include('partials.home.episodes-section', [
            'sectionId' => 'novedades',
            'title' => 'Últimos episodios',
            'label' => 'Anime y Series GL',
            'episodes' => $latestEpisodes,
            'catalogUrl' => route('catalog.series.index'),
        ])
        @include('partials.home.featured-section', [
            'sectionId' => 'destacados',
            'title' => 'Destacados',
            'label' => 'Anime y Series GL',
            'series' => $featuredSeries,
            'catalogUrl' => route('catalog.series.index'),
        ])
        @include('partials.home.catalog-section', [
            'title' => 'Títulos',
            'label' => 'Anime y Series GL',
            'series' => $mixedSeries,
        ])
    @else
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
    @endif

    <x-footer />
    <script>
        window.addEventListener('scroll', () => document.getElementById('navbar')?.classList.toggle('scrolled', window.scrollY > 40));
        const heroYoutubeVideo = document.getElementById('heroYoutubeVideo');
        heroYoutubeVideo?.addEventListener('load', () => window.setTimeout(() => heroYoutubeVideo.classList.add('is-ready'), 3000));
    </script>
    @include('partials.hover-media-script')
@endsection
