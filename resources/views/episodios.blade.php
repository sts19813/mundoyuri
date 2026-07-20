<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-seo
        :title="$episode ? $series->title.' T'.$episode->season_number.' E'.$episode->episode_number.': '.$episode->title : 'Últimos episodios GL'"
        :description="$episode ? \Illuminate\Support\Str::limit($episode->description ?: 'Mira '.$episode->title.' de '.$series->title.' online en Mundo Yuri.', 155) : 'Mira los episodios más recientes de series y doramas Girls’ Love en Mundo Yuri.'"
        :canonical="$episode ? route('public.episodes.show', $episode->slug) : route('legacy.episodios')"
        :image="$episode ? $episode->imageUrl('1200/630') : null"
        :type="$episode ? 'video.episode' : 'website'"
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
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css">
</head>

<body>

    <x-navbar />

    @if(!$episode)
        <section class="py-5 mt-5">
            <div class="container-xl px-4">
                <div class="alert alert-warning mb-0">Aún no hay episodios publicados.</div>
            </div>
        </section>
        <x-footer />
    @else
        @php
            $partSources = $episode->sources->where('source_type', 'part')->sortBy('sort_order')->values();
            $fullSources = $episode->sources->where('source_type', '!=', 'part')->values();
            $primarySource = $partSources->firstWhere('is_primary', true) ?: $partSources->first() ?: $fullSources->firstWhere('is_primary', true) ?: $fullSources->first();
            $shareUrl = route('public.episodes.show', $episode->slug);
            $releaseDate = $episode->release_date ?: $episode->published_at;
            $avatarClasses = ['', 'av2', 'av3'];
            $comments = $episode->comments;
        @endphp

        <div class="ep-layout">
            <main class="ep-main">
                <div class="ep-breadcrumb">
                    <a href="{{ route('home') }}">Inicio</a>
                    <span>›</span>
                    <a href="{{ route('catalog.series.index') }}">Series GL</a>
                    <span>›</span>
                    <a href="{{ route('catalog.series.show', $series->slug) }}">{{ $series->title }}</a>
                    <span>›</span>
                    <span style="color:var(--text)">Temporada {{ $episode->season_number }} · Episodio {{ $episode->episode_number }}</span>
                </div>

                <div class="player-wrap">
                    @if($primarySource)
                        <iframe id="episodePlayer" class="player-embed" src="{{ $primarySource->player_type === 'iframe' ? $primarySource->playable_url : '' }}"
                            title="{{ $series->title }} - Episodio {{ $episode->episode_number }}"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                            loading="lazy"
                            @if($primarySource->player_type !== 'iframe') style="display:none;" @endif></iframe>
                        <video id="episodeVideoPlayer" class="player-embed" controls playsinline preload="metadata"
                            data-provider="{{ $primarySource->provider }}"
                            style="background:#000; @if($primarySource->player_type !== 'video') display:none; @endif">
                            <source src="{{ $primarySource->player_type === 'video' ? $primarySource->playable_url : '' }}">
                        </video>
                    @else
                        <x-media-preview
                            :src="$episode->previewMediaUrl('1200/675')"
                            :type="$episode->previewMediaType()"
                            :alt="$series->title"
                            class="player-poster"
                            :autoplay="$episode->previewMediaType() === 'video'"
                        />
                    @endif

                    <div class="player-source-indicator">
                        <span class="player-title-badge">
                            T{{ $episode->season_number }} · E{{ $episode->episode_number }}
                        </span>
                        @if($primarySource)
                            <span class="player-source-chip" id="activeSourceLabel">{{ strtoupper($primarySource->provider) }}</span>
                        @endif
                    </div>
                </div>

                @if($partSources->isNotEmpty())
                    <div class="server-section">
                        <div class="server-header">
                            <span class="server-header-title">Partes del episodio</span>
                            <div class="server-views">{{ $partSources->count() }} partes</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($partSources as $part)
                                <button type="button" class="btn btn-sm {{ $part->is_primary || ($loop->first && !$partSources->contains(fn($item) => $item->is_primary)) ? 'btn-primary' : 'btn-light-primary' }} source-switcher" data-video-url="{{ $part->playable_url }}" data-provider="PARTE {{ $part->sort_order ?: $loop->iteration }}" data-provider-key="{{ $part->provider }}" data-player-type="{{ $part->player_type }}">
                                    {{ $part->label ?: 'Parte '.($part->sort_order ?: $loop->iteration) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="server-section">
                    <div class="server-header">
                        <span class="server-header-title">Fuentes de vídeo</span>
                        <div class="server-views">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            {{ $fullSources->count() }} fuentes
                        </div>
                    </div>
                    <div class="server-list">
                        @forelse($fullSources as $source)
                            <button type="button"
                                class="server-item source-switcher {{ $source->is_primary ? 'active' : '' }}"
                                data-video-url="{{ $source->playable_url }}"
                                data-provider="{{ strtoupper($source->provider) }}"
                                data-provider-key="{{ $source->provider }}"
                                data-quality="{{ preg_match('/\b(360|480|720|1080|1440|2160)p\b/i', (string) $source->label, $qualityMatch) ? $qualityMatch[1] : '' }}"
                                data-player-type="{{ $source->player_type }}">
                                <div class="server-icon">⚡</div>
                                <div class="server-info">
                                    <div class="server-name">{{ strtoupper($source->provider) }}</div>
                                    <div class="server-meta">{{ $source->label ?: 'Audio original · Sub Español' }}</div>
                                </div>
                                <span class="server-badge {{ $source->is_primary ? 'badge-clean' : 'badge-ads' }}">{{ $source->is_primary ? 'Principal' : 'Alterno' }}</span>
                            </button>
                        @empty
                            <div class="server-item">
                                <div class="server-info">
                                    <div class="server-name">{{ $partSources->isNotEmpty() ? 'Sin fuentes completas' : 'Sin fuentes' }}</div>
                                    <div class="server-meta">{{ $partSources->isNotEmpty() ? 'Este episodio se reproduce por partes.' : 'Este episodio aún no tiene enlaces disponibles.' }}</div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="ep-title-section">
                    <h1>{{ $series->title }} · <em>Temporada {{ $episode->season_number }} Episodio {{ $episode->episode_number }}</em></h1>
                    <div class="ep-meta-row">
                        <div class="ep-meta-item">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            {{ $releaseDate ? $releaseDate->format('d/m/Y') : 'Sin fecha' }}
                        </div>
                        <div class="ep-meta-dot"></div>
                        <div class="ep-meta-item">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            {{ $episode->duration_minutes ? $episode->duration_minutes.' min' : 'Sin duración' }}
                        </div>
                        <div class="ep-meta-dot"></div>
                        <div class="ep-meta-item" title="Vistas">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            {{ number_format($episode->views_count) }} {{ $episode->views_count === 1 ? 'vista' : 'vistas' }}
                        </div>
                        <div class="ep-meta-dot"></div>
                        <div class="ep-meta-item">{{ $series->country_of_origin ?: 'País no definido' }}</div>
                    </div>
                </div>

                <div class="ep-nav">
                    @if($previousEpisode)
                        <a href="{{ route('public.episodes.show', $previousEpisode->slug) }}" class="ep-nav-btn prev">
                            <div class="ep-nav-arrow">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="15 18 9 12 15 6" />
                                </svg>
                            </div>
                            <div>
                                <div class="ep-nav-label">Anterior</div>
                                <div class="ep-nav-title">Episodio {{ $previousEpisode->episode_number }}</div>
                            </div>
                        </a>
                    @else
                        <a href="#" class="ep-nav-btn prev">
                            <div class="ep-nav-arrow">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="15 18 9 12 15 6" />
                                </svg>
                            </div>
                            <div>
                                <div class="ep-nav-label">Anterior</div>
                                <div class="ep-nav-title">— Sin episodio —</div>
                            </div>
                        </a>
                    @endif

                    @if($nextEpisode)
                        <a href="{{ route('public.episodes.show', $nextEpisode->slug) }}" class="ep-nav-btn next">
                            <div class="ep-nav-arrow">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="9 18 15 12 9 6" />
                                </svg>
                            </div>
                            <div>
                                <div class="ep-nav-label">Siguiente</div>
                                <div class="ep-nav-title">Episodio {{ $nextEpisode->episode_number }}</div>
                            </div>
                        </a>
                    @else
                        <a href="#" class="ep-nav-btn next">
                            <div class="ep-nav-arrow">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="9 18 15 12 9 6" />
                                </svg>
                            </div>
                            <div>
                                <div class="ep-nav-label">Siguiente</div>
                                <div class="ep-nav-title">— Sin episodio —</div>
                            </div>
                        </a>
                    @endif
                </div>

                <div class="share-section">
                    <span class="share-label">Compartir</span>
                    <span class="share-count">{{ $comments->count() }}</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="share-btn share-fb">Facebook</a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($series->title.' - Episodio '.$episode->episode_number) }}" target="_blank" rel="noopener" class="share-btn share-tw">Twitter</a>
                    <a href="https://wa.me/?text={{ urlencode($series->title.' - '.$shareUrl) }}" target="_blank" rel="noopener" class="share-btn share-wa">WhatsApp</a>
                </div>

                <div class="ep-list-section">
                    <div class="ep-list-header">
                        <span class="ep-list-title">Episodios · Temporada {{ $episode->season_number }}</span>
                        <div class="ep-list-filter">
                            <button class="ep-filter-pill active">Todos</button>
                            <button class="ep-filter-pill">Recientes</button>
                        </div>
                    </div>
                    <div class="ep-list">
                        @foreach($seriesEpisodes as $item)
                            <a href="{{ route('public.episodes.show', $item->slug) }}" class="ep-list-item {{ $item->id === $episode->id ? 'current' : '' }}">
                                <div class="ep-list-thumb">
                                    <img src="{{ $item->imageUrl('140/90') }}" alt="">
                                    <div class="ep-thumb-num">E{{ $item->episode_number }}</div>
                                </div>
                                <div class="ep-list-info">
                                    <div class="ep-list-ep-title">Episodio {{ $item->episode_number }}</div>
                                    <div class="ep-list-date">{{ optional($item->release_date)->format('d/m/Y') ?: 'Sin fecha' }}</div>
                                </div>
                                @if($item->id === $episode->id)
                                    <div class="ep-list-playing">
                                        <div class="playing-bar"></div>
                                        <div class="playing-bar"></div>
                                        <div class="playing-bar"></div>
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="links-section">
                    <div class="links-header">
                        <span class="links-title">Enlaces</span>
                        <span class="links-badge">Descarga</span>
                    </div>
                    <div class="links-table-head">
                        <span>Opciones</span>
                        <span>Idioma</span>
                    </div>
                    @forelse($fullSources->isNotEmpty() ? $fullSources : $partSources as $source)
                        <div class="links-row">
                            <a href="{{ $source->playable_url }}" target="_blank" rel="noopener" class="links-row-option">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="8 12 12 16 16 12" />
                                    <line x1="12" y1="8" x2="12" y2="16" />
                                </svg>
                                {{ $source->source_type === 'part' ? 'PARTE '.($source->sort_order ?: $loop->iteration) : strtoupper($source->provider) }}
                            </a>
                            <span class="links-row-lang">{{ $source->label ?: 'Sub Español' }}</span>
                        </div>
                    @empty
                        <div class="links-row">
                            <span class="links-row-option">Sin enlaces disponibles</span>
                            <span class="links-row-lang">-</span>
                        </div>
                    @endforelse
                </div>

                @include('catalog.partials.threaded-comments', [
                    'comments' => $comments,
                    'targetType' => 'episode',
                    'targetId' => $episode->id,
                    'avatarClasses' => $avatarClasses,
                ])

                <div class="ep-disclaimer">
                    Este sitio no almacena archivos ni videos. Todo el contenido proviene de fuentes externas y se encuentra alojado en sitios de terceros.
                </div>

            </main>

            <aside class="ep-sidebar">
                <div class="sidebar-section">
                    <div class="sidebar-header">
                        <span class="sidebar-title">Lo más visto</span>
                        <span class="sidebar-fire">🔥</span>
                    </div>
                    <div class="sidebar-list">
                        @forelse($recentEpisodes as $item)
                            <a href="{{ route('public.episodes.show', $item->slug) }}" class="sidebar-item">
                                <span class="sidebar-rank {{ $loop->iteration <= 3 ? 'top3' : '' }}">{{ $loop->iteration }}</span>
                                <div class="sidebar-thumb">
                                    <img src="{{ $item->imageUrl('200/130') }}" alt="">
                                    <div class="sidebar-thumb-overlay"><svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                                            <path d="M8 5v14l11-7z" />
                                        </svg></div>
                                </div>
                                <div class="sidebar-info">
                                    <div class="sidebar-series">{{ $item->series->title ?? 'Serie' }}</div>
                                    <div class="sidebar-year">{{ $item->series->release_year ?: 'S/F' }} · {{ $item->series->country_of_origin ?: 'GL' }}</div>
                                </div>
                            </a>
                        @empty
                            <div class="sidebar-item">Sin episodios recientes.</div>
                        @endforelse
                    </div>
                </div>
            </aside>

        </div>

        <x-footer />
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <script>
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });

        const playerFrame = document.getElementById('episodePlayer');
        const playerVideo = document.getElementById('episodeVideoPlayer');
        const activeSourceLabel = document.getElementById('activeSourceLabel');
        const sourceButtons = document.querySelectorAll('.source-switcher');
        const backblazeQualityButtons = Array.from(sourceButtons).filter((button) =>
            button.dataset.providerKey === 'backblaze_b2' && button.dataset.quality
        );
        let directPlayer = null;

        if (playerVideo && window.Plyr) {
            directPlayer = new Plyr(playerVideo, {
                controls: ['play-large', 'rewind', 'play', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'pip', 'fullscreen'],
                settings: ['quality', 'speed'],
                seekTime: 10,
                speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] },
                quality: { default: 1080, options: [2160, 1440, 1080, 720, 480, 360] },
                i18n: {
                    restart: 'Reiniciar', rewind: 'Retroceder {seektime}s', play: 'Reproducir', pause: 'Pausar',
                    fastForward: 'Adelantar {seektime}s', seek: 'Buscar', played: 'Reproducido', buffered: 'Cargado',
                    currentTime: 'Tiempo actual', duration: 'Duración', volume: 'Volumen', mute: 'Silenciar',
                    unmute: 'Activar sonido', enableCaptions: 'Activar subtítulos', disableCaptions: 'Desactivar subtítulos',
                    enterFullscreen: 'Pantalla completa', exitFullscreen: 'Salir de pantalla completa', frameTitle: 'Reproductor de {title}',
                    captions: 'Subtítulos', settings: 'Ajustes', pip: 'Imagen en imagen', menuBack: 'Volver al menú anterior',
                    speed: 'Velocidad', normal: 'Normal', quality: 'Calidad', loop: 'Repetir'
                }
            });
            directPlayer.elements.container.style.display = @json($primarySource?->player_type === 'video' ? '' : 'none');
        }

        function backblazeSources(fallbackUrl) {
            const sources = backblazeQualityButtons.map((button) => ({
                src: button.dataset.videoUrl,
                type: 'video/mp4',
                size: Number(button.dataset.quality)
            }));

            return sources.length > 1 ? sources : [{ src: fallbackUrl, type: 'video/mp4' }];
        }

        @if($primarySource?->provider === 'backblaze_b2')
            if (directPlayer && backblazeQualityButtons.length > 1) {
                directPlayer.source = { type: 'video', sources: backblazeSources(@json($primarySource->playable_url)) };
            }
        @endif

        function switchEpisodePlayer(type, url, providerKey = '') {
            if (!url) {
                return;
            }

            if (type === 'video') {
                if (playerFrame) {
                    playerFrame.src = '';
                    playerFrame.style.display = 'none';
                }
                if (playerVideo) {
                    const sources = providerKey === 'backblaze_b2' ? backblazeSources(url) : [{ src: url, type: 'video/mp4' }];
                    if (directPlayer) {
                        directPlayer.source = { type: 'video', sources };
                        directPlayer.elements.container.style.display = '';
                    } else {
                        playerVideo.pause();
                        playerVideo.src = url;
                        playerVideo.load();
                        playerVideo.style.display = 'block';
                    }
                }

                return;
            }

            if (playerVideo) {
                directPlayer ? directPlayer.pause() : playerVideo.pause();
                playerVideo.removeAttribute('src');
                playerVideo.load();
                playerVideo.style.display = 'none';
                if (directPlayer) {
                    directPlayer.elements.container.style.display = 'none';
                }
            }
            if (playerFrame) {
                playerFrame.src = url;
                playerFrame.style.display = 'block';
            }
        }

        if (sourceButtons.length > 0) {
            sourceButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const nextUrl = button.getAttribute('data-video-url');
                    const provider = button.getAttribute('data-provider');
                    const playerType = button.getAttribute('data-player-type') || 'iframe';
                    const providerKey = button.getAttribute('data-provider-key') || '';

                    switchEpisodePlayer(playerType, nextUrl, providerKey);

                    sourceButtons.forEach((item) => {
                        item.classList.remove('active');
                        item.classList.remove('btn-primary');
                        item.classList.add('btn-light-primary');
                    });

                    button.classList.add('active');
                    button.classList.remove('btn-light-primary');
                    button.classList.add('btn-primary');

                    if (activeSourceLabel) {
                        activeSourceLabel.textContent = provider || 'FUENTE';
                    }
                });
            });
        }
    </script>
</body>

</html>
