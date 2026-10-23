@extends('layouts.portal')

@section('head')

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $seoType = $series->catalog_section === 'anime' ? 'anime yuri' : 'serie GL';
        $seoDescription = \Illuminate\Support\Str::limit(
            $series->description ?: 'Mira '.$series->title.', una '.$seoType.' con episodios subtitulados en español en Mundo Yuri.',
            155
        );
        $seriesSeoSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'TVSeries',
            'name' => $series->title,
            'description' => $seoDescription,
            'url' => route('catalog.series.show', $series->slug),
            'image' => $series->coverMediaType() === 'image' ? $series->coverMediaUrl() : null,
            'genre' => array_values(array_unique(array_filter([
                $series->genre?->name,
                ...$series->tags->pluck('name')->all(),
                $series->catalog_section === 'anime' ? 'Yuri' : 'Girls Love',
            ]))),
            'numberOfEpisodes' => $series->episodes->count(),
            'numberOfSeasons' => $series->total_seasons,
            'datePublished' => $series->published_at?->toDateString(),
            'inLanguage' => 'es-MX',
            'countryOfOrigin' => $series->country_of_origin ? ['@type' => 'Country', 'name' => $series->country_of_origin] : null,
        ];
        $seriesSeoSchema = array_filter($seriesSeoSchema, fn ($value) => $value !== null && $value !== []);
    @endphp
    <x-seo
        :title="$series->title.' - '.$seoType.' subtitulada en español'"
        :description="$seoDescription"
        :canonical="route('catalog.series.show', $series->slug)"
        :image="$series->coverMediaType() === 'image' ? $series->coverMediaUrl() : null"
        type="video.tv_show"
        :schema="$seriesSeoSchema"
    />
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
            <div class="ep-breadcrumb">
                <a href="{{ route('home') }}">Inicio</a>
                <span>›</span>
                <a href="{{ route('catalog.series.index') }}">Series GL</a>
                <span>›</span>
                <span style="color:var(--text)">{{ $series->title }}</span>
            </div>

            <section class="player-wrap" style="margin-bottom:20px;">
                <x-media-preview
                    :src="$series->bannerMediaUrl() ?: 'https://picsum.photos/1200/675?series='.$series->id"
                    :type="$series->bannerMediaUrl() ? $series->bannerMediaType() : 'image'"
                    :alt="$series->title"
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
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                    <h1>{{ $series->title }}</h1>
                    <x-favorite-series-button :series="$series" />
                </div>
                <p style="color:var(--muted);margin-top:10px;line-height:1.7;">{{ $series->description ?: 'Sin descripción disponible.' }}</p>
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

            <div class="ep-list-section">
                <div class="ep-list-header">
                    <span class="ep-list-title">Episodios disponibles</span>
                    <div class="ep-list-filter">
                        <button class="ep-filter-pill active">Publicados</button>
                    </div>
                </div>
                <div class="ep-list">
                    @forelse($series->episodes as $episode)
                        <a href="{{ route('public.episodes.show', $episode->slug) }}" class="ep-list-item">
                            <div class="ep-list-thumb">
                                <x-media-preview
                                    :src="$episode->previewMediaUrl('140/90')"
                                    :type="$episode->previewMediaType()"
                                    alt=""
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
            </div>

            @include('catalog.partials.threaded-comments', [
                'comments' => $comments,
                'targetType' => 'series',
                'targetId' => $series->id,
                'avatarClasses' => $avatarClasses,
            ])
        </main>

        <aside class="ep-sidebar">
            <x-catalog.sidebar-tabs :marathoners="$marathoners" :recent-episodes="$recentEpisodes" />

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
