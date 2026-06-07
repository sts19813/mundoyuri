<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $series->title }} - Series GL</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('assets/css/episodios.css') }}?v={{ filemtime(public_path('assets/css/episodios.css')) }}">
</head>

<body>
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
                <h1>{{ $series->title }}</h1>
                <p style="color:var(--muted);margin-top:10px;line-height:1.7;">{{ $series->description ?: 'Sin descripción disponible.' }}</p>
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
                                    :src="$episode->thumbnail_image ?: ($series->coverMediaUrl() ?: 'https://picsum.photos/140/90?'.$episode->id)"
                                    :type="$episode->thumbnail_image ? 'image' : ($series->coverMediaUrl() ? $series->coverMediaType() : 'image')"
                                    alt=""
                                    class="ep-thumb-media"
                                    :hover-play="!$episode->thumbnail_image && $series->coverMediaType() === 'video'"
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
            <div class="sidebar-section">
                <div class="sidebar-header">
                    <span class="sidebar-title">Nuevos capítulos</span>
                    <span class="sidebar-fire">🔥</span>
                </div>
                <div class="sidebar-list">
                    @forelse($recentEpisodes as $item)
                        <a href="{{ route('public.episodes.show', $item->slug) }}" class="sidebar-item">
                            <span class="sidebar-rank {{ $loop->iteration <= 3 ? 'top3' : '' }}">{{ $loop->iteration }}</span>
                            <div class="sidebar-thumb">
                                <img src="{{ $item->thumbnail_image ?: 'https://picsum.photos/200/130?'.$item->id }}" alt="">
                                <div class="sidebar-thumb-overlay"><svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                                        <path d="M8 5v14l11-7z" />
                                    </svg></div>
                            </div>
                            <div class="sidebar-info">
                                <div class="sidebar-series">{{ $item->series->title ?? 'Serie' }}</div>
                                <div class="sidebar-year">E{{ $item->episode_number }} · {{ optional($item->published_at)->format('d/m/Y') ?: 'Sin fecha' }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="sidebar-item">Sin episodios recientes.</div>
                    @endforelse
                </div>
            </div>

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
</body>

</html>
