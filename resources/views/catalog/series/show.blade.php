@extends('layouts.portal')

@section('head')

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $canonicalUrl = route('catalog.series.show', $series->slug);
        $seoTitle = $series->seoPageTitle();
        $seoDescription = $series->seoMetaDescription();
        $alternateTitles = $series->seoAlternateTitles();
        $seoImages = collect([
            $series->bannerMediaType() === 'image' ? $series->bannerMediaUrl() : null,
            $series->coverMediaType() === 'image' ? $series->coverMediaUrl() : null,
        ])->filter()->unique()->values();
        $seoImage = $seoImages->first();
        $contentSchemaId = $canonicalUrl.'#title';
        $breadcrumbSchemaId = $canonicalUrl.'#breadcrumb';
        $contentSchema = array_filter([
            '@type' => $series->content_type === 'movie' ? 'Movie' : 'TVSeries',
            '@id' => $contentSchemaId,
            'name' => $series->title,
            'alternateName' => $alternateTitles ?: null,
            'description' => $series->description,
            'url' => $canonicalUrl,
            'mainEntityOfPage' => ['@id' => $canonicalUrl.'#webpage'],
            'image' => $seoImages->all() ?: null,
            'genre' => array_values(array_unique(array_filter([
                $series->genre?->name,
                ...$series->tags->pluck('name')->all(),
                $series->catalog_section === 'anime' ? 'Yuri' : 'Girls Love',
            ]))),
            'keywords' => $series->tags->pluck('name')->join(', ') ?: null,
            'actor' => $series->artists->map(fn ($artist) => [
                '@type' => 'Person',
                'name' => $artist->name,
            ])->values()->all() ?: null,
            'countryOfOrigin' => $series->country_of_origin ? ['@type' => 'Country', 'name' => $series->country_of_origin] : null,
            'datePublished' => $series->release_year ? $series->release_year.'-01-01' : $series->published_at?->toDateString(),
            'duration' => $series->duration_minutes ? 'PT'.$series->duration_minutes.'M' : null,
            'numberOfEpisodes' => $series->content_type === 'series' ? $series->episodes->count() : null,
            'numberOfSeasons' => $series->content_type === 'series' ? $series->total_seasons : null,
            'inLanguage' => 'es-MX',
            'isAccessibleForFree' => true,
            'potentialAction' => $series->episodes->isNotEmpty() ? [
                '@type' => 'WatchAction',
                'target' => route('public.episodes.show', $series->episodes->first()->slug),
            ] : null,
            'episode' => $series->content_type === 'series' ? $series->episodes->map(fn ($episode) => array_filter([
                '@type' => 'TVEpisode',
                '@id' => route('public.episodes.show', $episode->slug).'#episode',
                'name' => $episode->title,
                'url' => route('public.episodes.show', $episode->slug),
                'episodeNumber' => $episode->episode_number,
                'seasonNumber' => $episode->season_number,
                'datePublished' => $episode->release_date?->toDateString() ?: $episode->published_at?->toDateString(),
                'partOfSeries' => ['@id' => $contentSchemaId],
            ], fn ($value) => $value !== null && $value !== ''))->values()->all() : null,
        ], fn ($value) => $value !== null && $value !== [] && $value !== '');
        $seriesSeoSchema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    '@id' => route('home').'#website',
                    'url' => route('home'),
                    'name' => 'Mundo Yuri',
                    'inLanguage' => 'es-MX',
                ],
                array_filter([
                    '@type' => 'WebPage',
                    '@id' => $canonicalUrl.'#webpage',
                    'url' => $canonicalUrl,
                    'name' => $seoTitle,
                    'description' => $seoDescription,
                    'inLanguage' => 'es-MX',
                    'isPartOf' => ['@id' => route('home').'#website'],
                    'breadcrumb' => ['@id' => $breadcrumbSchemaId],
                    'mainEntity' => ['@id' => $contentSchemaId],
                    'primaryImageOfPage' => $seoImage ? ['@type' => 'ImageObject', 'url' => $seoImage] : null,
                ], fn ($value) => $value !== null),
                [
                    '@type' => 'BreadcrumbList',
                    '@id' => $breadcrumbSchemaId,
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => route('home')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Series GL, yuri y anime', 'item' => route('catalog.series.index')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $series->title, 'item' => $canonicalUrl],
                    ],
                ],
                $contentSchema,
            ],
        ];
    @endphp
    <x-seo
        :title="$seoTitle"
        :description="$seoDescription"
        :canonical="$canonicalUrl"
        :image="$seoImage"
        :image-alt="'Imagen oficial de '.$series->title"
        :type="$series->content_type === 'movie' ? 'video.movie' : 'video.tv_show'"
        :schema="$seriesSeoSchema"
    />
    @if($series->bannerMediaType() === 'image' && $series->bannerMediaUrl())
        <link rel="preload" as="image" href="{{ $series->bannerMediaUrl() }}" fetchpriority="high">
    @endif
    <x-portal-favicon />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('assets/css/episodios.css') }}?v={{ filemtime(public_path('assets/css/episodios.css')) }}">


@endsection

@section('body')

<x-navbar />

    @php
        $comments = $series->comments;
        $avatarClasses = ['', 'av2', 'av3'];
        $firstEpisode = $series->episodes->first();
    @endphp

    <div class="ep-layout">
        <main class="ep-main">
            <nav class="ep-breadcrumb" aria-label="Migas de pan">
                <a href="{{ route('home') }}">Inicio</a>
                <span>›</span>
                <a href="{{ route('catalog.series.index') }}">Series GL, yuri y anime</a>
                <span>›</span>
                <span style="color:var(--text)">{{ $series->title }}</span>
            </nav>

            <section class="player-wrap" style="margin-bottom:20px;">
                <x-media-preview
                    :src="$series->bannerMediaUrl() ?: 'https://picsum.photos/1200/675?series='.$series->id"
                    :type="$series->bannerMediaUrl() ? $series->bannerMediaType() : 'image'"
                    :alt="'Banner de '.$series->title.', '.$series->seoContentLabel().' subtitulada en español'"
                    class="player-poster"
                    :autoplay="true"
                />
                <div class="player-overlay-ui">
                    <div class="player-top-bar">
                        <div class="player-title-badge">{{ ucfirst($series->content_type) }} · {{ ucfirst($series->status) }}</div>
                    </div>
                    <div class="player-center">
                        @if($firstEpisode)
                            <a href="{{ route('public.episodes.show', $firstEpisode->slug) }}" class="player-play-btn">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="#fff">
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </a>
                        @endif
                    </div>
                    <div class="player-bottom">
                        <div class="player-controls-row">
                            <div class="player-ctrl-left">
                                <span class="player-time">{{ $series->genre->name ?? 'Sin género' }}</span>
                            </div>
                            <div class="player-ctrl-right">
                                <span class="player-time">{{ $series->release_year ?: 'S/F' }} · {{ $series->country_of_origin ?: 'GL' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="ep-title-section">
                <p class="series-seo-kicker">{{ ucfirst($series->seoContentLabel()) }} subtitulada en español</p>
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                    <h1>{{ $series->title }}</h1>
                    <x-favorite-series-button :series="$series" />
                </div>
                @if($alternateTitles)
                    <p class="series-alternate-titles"><strong>También conocida como:</strong> {{ implode(' · ', $alternateTitles) }}</p>
                @endif
                <section class="series-synopsis" aria-labelledby="series-synopsis-title">
                    <h2 id="series-synopsis-title">Sinopsis de {{ $series->title }}</h2>
                    <p>{{ $series->description ?: 'Sin descripción disponible.' }}</p>
                </section>
                @if($series->tags->isNotEmpty())
                    <div class="catalog-detail-tags" aria-label="Etiquetas de {{ $series->title }}">
                        @foreach($series->tags as $tag)
                            <a href="{{ route('catalog.series.index', ['tag' => $tag->slug]) }}">{{ $tag->name }}</a>
                        @endforeach
                    </div>
                @endif
                @if($series->artists->isNotEmpty() || $series->duos->isNotEmpty())
                    <div class="catalog-detail-people">
                        @if($series->artists->isNotEmpty())<span><strong>Reparto:</strong> {{ $series->artists->pluck('name')->join(', ') }}</span>@endif
                        @if($series->duos->isNotEmpty())<span><strong>Duplas:</strong> {{ $series->duos->pluck('name')->join(', ') }}</span>@endif
                    </div>
                @endif
                <div class="ep-meta-row mt-3">
                    <div class="ep-meta-item">Duración: {{ $series->duration_minutes ? $series->duration_minutes.' min' : 'N/D' }}</div>
                    <div class="ep-meta-dot"></div>
                    <div class="ep-meta-item">Temporadas: {{ $series->total_seasons ?: 'N/D' }}</div>
                    <div class="ep-meta-dot"></div>
                    <div class="ep-meta-item">Episodios: {{ $series->episodes->count() }}</div>
                </div>
            </div>

            <section class="ep-list-section" aria-labelledby="series-episodes-title">
                <div class="ep-list-header">
                    <h2 class="ep-list-title" id="series-episodes-title">Episodios de {{ $series->title }}</h2>
                    <span class="series-episode-count">{{ $series->episodes->count() }} {{ $series->episodes->count() === 1 ? 'episodio' : 'episodios' }}</span>
                </div>
                <div class="ep-list">
                    @forelse($series->episodes as $episode)
                        <a href="{{ route('public.episodes.show', $episode->slug) }}" class="ep-list-item">
                            <div class="ep-list-thumb">
                                <x-media-preview
                                    :src="$episode->previewMediaUrl('140/90')"
                                    :type="$episode->previewMediaType()"
                                    :alt="'Imagen del episodio '.$episode->episode_number.' de '.$series->title"
                                    class="ep-thumb-media"
                                    :hover-play="$episode->previewMediaType() === 'video'"
                                />
                                <div class="ep-thumb-num">E{{ $episode->episode_number }}</div>
                            </div>
                            <div class="ep-list-info">
                                <div class="ep-list-ep-title">S{{ $episode->season_number }}E{{ $episode->episode_number }} · {{ $episode->title }}</div>
                                <div class="ep-list-date">{{ optional($episode->release_date)->format('d/m/Y') ?: 'Sin fecha' }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="alert alert-dark mb-0" style="background:var(--dark-card);border-color:rgba(244,63,142,.16);color:var(--muted);">
                            No hay episodios aprobados para esta serie.
                        </div>
                    @endforelse
                </div>
            </section>

            @if($relatedSeries->isNotEmpty())
                <section class="series-related" aria-labelledby="related-series-title">
                    <div class="series-related-heading">
                        <div>
                            <p class="series-seo-kicker">También te puede gustar</p>
                            <h2 id="related-series-title">Más historias como {{ $series->title }}</h2>
                        </div>
                        <a href="{{ route('catalog.series.index') }}">Ver catálogo</a>
                    </div>
                    <div class="series-related-grid">
                        @foreach($relatedSeries as $related)
                            <a href="{{ route('catalog.series.show', $related->slug) }}" class="series-related-item">
                                <div class="series-related-poster">
                                    <x-media-preview
                                        :src="$related->coverMediaUrl() ?: 'https://picsum.photos/300/420?series-'.$related->id"
                                        :type="$related->coverMediaUrl() ? $related->coverMediaType() : 'image'"
                                        :alt="'Portada de '.$related->title"
                                        loading="lazy"
                                    />
                                </div>
                                <h3>{{ $related->title }}</h3>
                                <p>{{ $related->genre?->name ?: ucfirst($related->seoContentLabel()) }} · {{ $related->release_year ?: 'Estreno disponible' }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @include('catalog.partials.threaded-comments', [
                'comments' => $comments,
                'targetType' => 'series',
                'targetId' => $series->id,
                'avatarClasses' => $avatarClasses,
            ])
        </main>

        <aside class="ep-sidebar">
            <x-catalog.sidebar-tabs :streakers="$streakers" :marathoners="$marathoners" :recent-episodes="$recentEpisodes" />

            <div class="server-section mt-3">
                <div class="server-header">
                    <span class="server-header-title">Acciones</span>
                </div>
                @auth
                    <a href="{{ route('submissions.create') }}" class="cf-submit" style="text-decoration:none;justify-content:center;width:100%;">Subir contenido</a>
                @else
                    <a href="{{ route('login') }}" class="cf-submit" style="text-decoration:none;justify-content:center;width:100%;">Inicia sesión para aportar</a>
                @endauth
                <a href="{{ route('catalog.series.index') }}" class="ep-nav-btn mt-2" style="text-decoration:none;">
                    <div>
                        <div class="ep-nav-label">Regresar</div>
                        <div class="ep-nav-title">Volver al catálogo</div>
                    </div>
                </a>
            </div>
        </aside>
    </div>

    <x-footer />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });
    </script>
    @include('partials.hover-media-script')
@endsection
