@extends('layouts.portal')

@section('body_attributes')
    class="episode-page-body"
@endsection

@section('head')

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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


@endsection

@section('body')

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
            $avatarClasses = ['', 'av2', 'av3'];
            $comments = $episode->comments;
            $trackableProviders = config('watch_progress.providers', []);
            $requestedResumeSeconds = max(0, (int) request()->query('t', 0));
            $storedResumeSeconds = $episodeProgress?->completed ? 0 : (int) ($episodeProgress?->position_seconds ?? 0);
            $resumeSeconds = $requestedResumeSeconds ?: $storedResumeSeconds;
            $resumeSourceId = (int) request()->query('source', $episodeProgress?->episode_source_id ?? ($primarySource?->id ?? 0));
            $watchProgressEnabled = $watchProgressEnabled ?? false;
            $episodeDisplayTitle = trim((string) $episode->title);
            $showEpisodeDisplayTitle = filled($episodeDisplayTitle)
                && strcasecmp($episodeDisplayTitle, $series->title) !== 0
                && ! in_array(mb_strtolower($episodeDisplayTitle), [
                    'episodio '.$episode->episode_number,
                    'episode '.$episode->episode_number,
                ], true);
            $subtitleLanguageLabels = [
                'es' => 'Español',
                'spa' => 'Español',
                'en' => 'English',
                'eng' => 'English',
            ];
            $subtitleFiles = collect(glob(public_path('subtitles/episodes/'.$episode->slug.'.*.vtt')) ?: []);
            $fallbackSubtitleFile = public_path('subtitles/episodes/'.$episode->slug.'.vtt');

            if ($subtitleFiles->isEmpty() && file_exists($fallbackSubtitleFile)) {
                $subtitleFiles = collect([$fallbackSubtitleFile]);
            }

            $episodeSubtitleTracks = $subtitleFiles
                ->map(function (string $path) use ($episode, $subtitleLanguageLabels) {
                    $basename = basename($path);
                    $language = 'es';

                    if (preg_match('/^'.preg_quote($episode->slug, '/').'\.([a-z]{2,3})\.vtt$/i', $basename, $matches) === 1) {
                        $language = strtolower($matches[1]);
                    }

                    return [
                        'src' => asset('subtitles/episodes/'.$basename).'?v='.filemtime($path),
                        'srclang' => $language === 'spa' ? 'es' : $language,
                        'label' => $subtitleLanguageLabels[$language] ?? strtoupper($language),
                        'default' => in_array($language, ['es', 'spa'], true),
                    ];
                })
                ->values();
            $publicSourceLabels = [
                'cloudflare_hls' => [
                    'name' => 'video.mundoyuri',
                    'meta' => 'Premium 👑',
                ],
                'byse' => [
                    'name' => 'Con publicidad',
                    'meta' => 'Siempre ayuda a la página',
                ],
                'backblaze_b2' => [
                    'name' => 'mundoyuri.com premium',
                    'meta' => 'Premium 👑',
                ],
            ];
            $publicSourceLabel = function ($source) use ($publicSourceLabels) {
                if (! $source) {
                    return [
                        'name' => 'FUENTE',
                        'meta' => 'Audio original · Sub Español',
                    ];
                }

                return $publicSourceLabels[$source->provider] ?? [
                    'name' => strtoupper($source->provider),
                    'meta' => $source->label ?: 'Audio original · Sub Español',
                ];
            };
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
                            data-source-id="{{ $primarySource->id }}"
                            data-trackable="{{ in_array($primarySource->provider, $trackableProviders, true) ? '1' : '0' }}"
                            style="background:#000; @if($primarySource->player_type !== 'video') display:none; @endif">
                            @if($primarySource->player_type === 'video' && $primarySource->provider !== 'cloudflare_hls')
                                <source src="{{ $primarySource->playable_url }}" type="video/mp4">
                            @endif
                            @foreach($episodeSubtitleTracks as $track)
                                <track kind="captions" src="{{ $track['src'] }}" srclang="{{ $track['srclang'] }}" label="{{ $track['label'] }}" @if($track['default']) default @endif>
                            @endforeach
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
                        <span class="player-source-label" data-active-source-label>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M4 5h16v14H4z" />
                                <path d="m9 9 6 3-6 3V9Z" />
                            </svg>
                            <span>{{ $publicSourceLabel($primarySource)['name'] }}</span>
                        </span>
                        <div class="player-favorite-action">
                            <x-favorite-series-button :series="$series" compact />
                        </div>
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
                                <button type="button" class="btn btn-sm {{ $part->is_primary || ($loop->first && !$partSources->contains(fn($item) => $item->is_primary)) ? 'btn-primary' : 'btn-light-primary' }} source-switcher" data-source-id="{{ $part->id }}" data-video-url="{{ $part->playable_url }}" data-provider="PARTE {{ $part->sort_order ?: $loop->iteration }}" data-provider-key="{{ $part->provider }}" data-trackable="{{ in_array($part->provider, $trackableProviders, true) ? '1' : '0' }}" data-player-type="{{ $part->player_type }}">
                                    {{ $part->label ?: 'Parte '.($part->sort_order ?: $loop->iteration) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($fullSources->count() > 1)
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
                            @foreach($fullSources as $source)
                                @php($sourcePublicLabel = $publicSourceLabel($source))
                                <button type="button"
                                    class="server-item source-switcher {{ $source->is_primary ? 'active' : '' }}"
                                    data-source-id="{{ $source->id }}"
                                    data-video-url="{{ $source->playable_url }}"
                                    data-provider="{{ $sourcePublicLabel['name'] }}"
                                    data-provider-key="{{ $source->provider }}"
                                    data-trackable="{{ in_array($source->provider, $trackableProviders, true) ? '1' : '0' }}"
                                    data-quality="{{ preg_match('/\b(360|480|720|1080|1440|2160)p\b/i', (string) $source->label, $qualityMatch) ? $qualityMatch[1] : '' }}"
                                    data-player-type="{{ $source->player_type }}">
                                    <div class="server-icon">⚡</div>
                                    <div class="server-info">
                                        <div class="server-name">{{ $sourcePublicLabel['name'] }}</div>
                                        <div class="server-meta">{{ $sourcePublicLabel['meta'] }}</div>
                                    </div>
                                    <span class="server-badge {{ $source->is_primary ? 'badge-clean' : 'badge-ads' }}">{{ $source->is_primary ? 'Principal' : 'Alterno' }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="ep-title-section">
                    <div class="ep-heading-row">
                        <div class="ep-heading-copy">
                            <h1>{{ $series->title }}</h1>
                            <p class="ep-episode-label">
                                Temporada {{ $episode->season_number }} · Episodio {{ $episode->episode_number }}
                                @if($showEpisodeDisplayTitle)
                                    <span>{{ $episodeDisplayTitle }}</span>
                                @endif
                            </p>
                        </div>
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
                        <span class="ep-nav-btn prev is-disabled" aria-disabled="true">
                            <div class="ep-nav-arrow">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="15 18 9 12 15 6" />
                                </svg>
                            </div>
                            <div>
                                <div class="ep-nav-label">Anterior</div>
                                <div class="ep-nav-title">No disponible</div>
                            </div>
                        </span>
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
                        <span class="ep-nav-btn next is-disabled" aria-disabled="true">
                            <div class="ep-nav-arrow">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="9 18 15 12 9 6" />
                                </svg>
                            </div>
                            <div>
                                <div class="ep-nav-label">Siguiente</div>
                                <div class="ep-nav-title">No disponible</div>
                            </div>
                        </span>
                    @endif
                </div>

                <div class="ep-list-section">
                    <div class="ep-list-header">
                        <span class="ep-list-title">Episodios · Temporada {{ $episode->season_number }}</span>
                    </div>
                    <nav class="ep-number-grid" aria-label="Episodios de la temporada {{ $episode->season_number }}">
                        @foreach($seriesEpisodes as $item)
                            <a
                                href="{{ route('public.episodes.show', $item->slug) }}"
                                class="ep-number-btn {{ $item->id === $episode->id ? 'current' : '' }}"
                                aria-label="Ver episodio {{ $item->episode_number }}"
                                @if($item->id === $episode->id) aria-current="page" @endif
                            >
                                {{ $item->episode_number }}
                            </a>
                        @endforeach
                    </nav>
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
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1.6.14/dist/hls.min.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <script>
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });

        const playerFrame = document.getElementById('episodePlayer');
        const playerVideo = document.getElementById('episodeVideoPlayer');
        const sourceButtons = document.querySelectorAll('.source-switcher');
        const backblazeQualityButtons = Array.from(sourceButtons).filter((button) =>
            button.dataset.providerKey === 'backblaze_b2' && button.dataset.quality
        );
        const watchProgressConfig = {
            enabled: @json($watchProgressEnabled),
            endpoint: @json($watchProgressEnabled ? route('episodes.progress.store', $episode, false) : null),
            csrf: @json(csrf_token()),
            minimumSeconds: @json((int) config('watch_progress.minimum_seconds', 15)),
            resumeSeconds: @json($resumeSeconds),
            resumeSourceId: @json($resumeSourceId),
        };
        let directPlayer = null;
        let hlsPlayer = null;
        let hlsStallMonitor = null;
        let hlsLastPlaybackTime = 0;
        let hlsLastPlaybackCheckAt = 0;
        let hlsLastRecoveryAt = 0;
        let lastProgressSaveAt = 0;
        let pendingResumeSeconds = Number(watchProgressConfig.resumeSeconds || 0);
        let resumeApplied = false;
        const subtitleTracks = @json($episodeSubtitleTracks ?? []);

        const isMobileStandaloneApp = () => {
            const isMobile = window.matchMedia?.('(max-width: 767px) and (pointer: coarse)').matches;
            const isStandalone = window.matchMedia?.('(display-mode: standalone)').matches || window.navigator.standalone === true;

            return Boolean(isMobile && isStandalone);
        };

        const lockEpisodeLandscape = async () => {
            if (!isMobileStandaloneApp() || !window.screen?.orientation?.lock) {
                return;
            }

            try {
                await window.screen.orientation.lock('landscape');
            } catch (_) {
                // Some mobile browsers expose fullscreen without supporting orientation locks.
            }
        };

        const unlockEpisodeOrientation = () => {
            if (!isMobileStandaloneApp() || !window.screen?.orientation?.unlock) {
                return;
            }

            try {
                window.screen.orientation.unlock();
            } catch (_) {
                // Keep the player usable when the browser rejects an orientation change.
            }
        };

        const syncEpisodeOrientation = () => {
            const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement;

            if (isFullscreen) {
                lockEpisodeLandscape();
                return;
            }

            unlockEpisodeOrientation();
        };

        function destroyHlsPlayer() {
            stopHlsStallMonitor();
            if (hlsPlayer) {
                hlsPlayer.destroy();
                hlsPlayer = null;
            }
        }

        function stopHlsStallMonitor() {
            if (hlsStallMonitor) {
                window.clearInterval(hlsStallMonitor);
                hlsStallMonitor = null;
            }
        }

        function recoverHlsPlayback(reason = 'stall') {
            if (!hlsPlayer || !playerVideo || playerVideo.paused || playerVideo.ended) {
                return;
            }

            const now = Date.now();
            if (now - hlsLastRecoveryAt < 8000) {
                return;
            }
            hlsLastRecoveryAt = now;

            const resumeAt = Math.max((playerVideo.currentTime || 0) - 0.25, 0);
            try {
                hlsPlayer.stopLoad();
                hlsPlayer.startLoad(resumeAt);
                hlsPlayer.recoverMediaError();
            } catch (error) {
                try {
                    hlsPlayer.startLoad(resumeAt);
                } catch (startError) {
                    console.warn('No se pudo recuperar HLS', reason, startError);
                }
            }
        }

        function startHlsStallMonitor() {
            stopHlsStallMonitor();
            hlsLastPlaybackTime = playerVideo?.currentTime || 0;
            hlsLastPlaybackCheckAt = Date.now();
            hlsStallMonitor = window.setInterval(() => {
                if (!hlsPlayer || !playerVideo || playerVideo.paused || playerVideo.ended || playerVideo.seeking) {
                    hlsLastPlaybackTime = playerVideo?.currentTime || 0;
                    hlsLastPlaybackCheckAt = Date.now();
                    return;
                }

                const currentTime = playerVideo.currentTime || 0;
                const moved = Math.abs(currentTime - hlsLastPlaybackTime) > 0.15;
                if (moved) {
                    hlsLastPlaybackTime = currentTime;
                    hlsLastPlaybackCheckAt = Date.now();
                    return;
                }

                if (Date.now() - hlsLastPlaybackCheckAt > 7000) {
                    recoverHlsPlayback('playback-stall');
                    hlsLastPlaybackCheckAt = Date.now();
                }
            }, 2500);
        }

        if (playerVideo && window.Plyr) {
            directPlayer = new Plyr(playerVideo, {
                controls: ['play-large', 'rewind', 'play', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'captions', 'settings', 'pip', 'fullscreen'],
                settings: ['captions', 'quality', 'speed'],
                captions: { active: true, language: 'es', update: true },
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
            directPlayer.on('enterfullscreen', lockEpisodeLandscape);
            directPlayer.on('exitfullscreen', unlockEpisodeOrientation);
        }

        document.addEventListener('fullscreenchange', syncEpisodeOrientation);
        document.addEventListener('webkitfullscreenchange', syncEpisodeOrientation);
        playerVideo?.addEventListener('webkitbeginfullscreen', lockEpisodeLandscape);
        playerVideo?.addEventListener('webkitendfullscreen', unlockEpisodeOrientation);

        function backblazeSources(fallbackUrl) {
            const sources = backblazeQualityButtons.map((button) => ({
                src: button.dataset.videoUrl,
                type: 'video/mp4',
                size: Number(button.dataset.quality)
            }));

            return sources.length > 1 ? sources : [{ src: fallbackUrl, type: 'video/mp4' }];
        }

        function playerSource(sources) {
            return {
                type: 'video',
                sources,
                tracks: subtitleTracks.map((track) => ({
                    kind: 'captions',
                    label: track.label,
                    srclang: track.srclang,
                    src: track.src,
                    default: Boolean(track.default),
                })),
            };
        }

        @if($primarySource?->provider === 'backblaze_b2')
            if (directPlayer && backblazeQualityButtons.length > 1) {
                directPlayer.source = playerSource(backblazeSources(@json($primarySource->playable_url)));
            }
        @endif

        @if($primarySource?->provider === 'cloudflare_hls')
            if (playerVideo) {
                loadHlsSource(@json($primarySource->playable_url));
            }
        @endif

        function loadHlsSource(url) {
            if (!playerVideo) {
                return;
            }

            destroyHlsPlayer();
            directPlayer ? directPlayer.pause() : playerVideo.pause();
            playerVideo.removeAttribute('src');
            playerVideo.querySelectorAll('source').forEach((source) => source.remove());
            playerVideo.load();

            if (window.Hls && Hls.isSupported()) {
                hlsPlayer = new Hls({
                    manifestLoadingMaxRetry: 6,
                    manifestLoadingRetryDelay: 1000,
                    levelLoadingMaxRetry: 6,
                    levelLoadingRetryDelay: 1000,
                    fragLoadingMaxRetry: 8,
                    fragLoadingRetryDelay: 1000,
                    fragLoadingTimeOut: 20000,
                    appendErrorMaxRetry: 4,
                });
                hlsPlayer.on(Hls.Events.ERROR, (event, data) => {
                    if (!data.fatal && data.details !== Hls.ErrorDetails.BUFFER_STALLED_ERROR) {
                        return;
                    }

                    if (data.type === Hls.ErrorTypes.NETWORK_ERROR) {
                        hlsPlayer.startLoad();
                    } else if (data.type === Hls.ErrorTypes.MEDIA_ERROR) {
                        hlsPlayer.recoverMediaError();
                    } else {
                        recoverHlsPlayback(data.details || 'hls-error');
                    }
                });
                hlsPlayer.loadSource(url);
                hlsPlayer.attachMedia(playerVideo);
                startHlsStallMonitor();
            } else if (playerVideo.canPlayType('application/vnd.apple.mpegurl')) {
                playerVideo.src = url;
            } else if (directPlayer) {
                directPlayer.source = playerSource([{ src: url, type: 'application/x-mpegURL' }]);
            } else {
                playerVideo.src = url;
            }

            if (directPlayer) {
                directPlayer.elements.container.style.display = '';
            } else {
                playerVideo.style.display = 'block';
            }
        }

        function switchEpisodePlayer(type, url, providerKey = '', sourceId = '', trackable = '0') {
            if (!url) {
                return;
            }

            if (type === 'video') {
                if (playerFrame) {
                    playerFrame.src = '';
                    playerFrame.style.display = 'none';
                }
                if (playerVideo) {
                    playerVideo.dataset.provider = providerKey;
                    playerVideo.dataset.sourceId = sourceId;
                    playerVideo.dataset.trackable = trackable;

                    if (providerKey === 'cloudflare_hls') {
                        loadHlsSource(url);

                        return;
                    }

                    destroyHlsPlayer();
                    const sources = providerKey === 'backblaze_b2' ? backblazeSources(url) : [{ src: url, type: 'video/mp4' }];
                    if (directPlayer) {
                        directPlayer.source = playerSource(sources);
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
                destroyHlsPlayer();
                directPlayer ? directPlayer.pause() : playerVideo.pause();
                playerVideo.removeAttribute('src');
                playerVideo.load();
                playerVideo.style.display = 'none';
                playerVideo.dataset.provider = providerKey;
                playerVideo.dataset.sourceId = sourceId;
                playerVideo.dataset.trackable = '0';
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
                    const sourceId = button.getAttribute('data-source-id') || '';
                    const trackable = button.getAttribute('data-trackable') || '0';
                    const activeSourceLabel = document.querySelector('[data-active-source-label] span');

                    switchEpisodePlayer(playerType, nextUrl, providerKey, sourceId, trackable);

                    if (activeSourceLabel && provider) {
                        activeSourceLabel.textContent = provider;
                    }

                    sourceButtons.forEach((item) => {
                        item.classList.remove('active');
                        item.classList.remove('btn-primary');
                        item.classList.add('btn-light-primary');
                    });

                    button.classList.add('active');
                    button.classList.remove('btn-light-primary');
                    button.classList.add('btn-primary');

                });
            });
        }

        function currentVideoPayload(completed = false) {
            if (!playerVideo || !watchProgressConfig.enabled || !watchProgressConfig.endpoint) {
                return null;
            }

            if (playerVideo.dataset.trackable !== '1' || !playerVideo.dataset.sourceId) {
                return null;
            }

            const positionSeconds = Math.max(0, Math.floor(playerVideo.currentTime || 0));
            const durationSeconds = Number.isFinite(playerVideo.duration) && playerVideo.duration > 0
                ? Math.floor(playerVideo.duration)
                : null;

            if (!completed && positionSeconds < watchProgressConfig.minimumSeconds) {
                return null;
            }

            return {
                episode_source_id: Number(playerVideo.dataset.sourceId),
                position_seconds: completed && durationSeconds ? durationSeconds : positionSeconds,
                duration_seconds: durationSeconds,
                completed,
            };
        }

        function saveWatchProgress(force = false, completed = false) {
            const payload = currentVideoPayload(completed);

            if (!payload) {
                return;
            }

            const now = Date.now();
            if (!force && now - lastProgressSaveAt < 15000) {
                return;
            }
            lastProgressSaveAt = now;

            fetch(watchProgressConfig.endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': watchProgressConfig.csrf,
                },
                credentials: 'same-origin',
                keepalive: force,
                body: JSON.stringify(payload),
            }).catch(() => {});
        }

        function applyPendingResume() {
            if (!playerVideo || resumeApplied || pendingResumeSeconds <= 0) {
                return;
            }

            if (playerVideo.readyState < 1) {
                return;
            }

            const duration = Number.isFinite(playerVideo.duration) ? playerVideo.duration : 0;
            const targetSeconds = duration > 0
                ? Math.min(pendingResumeSeconds, Math.max(0, duration - 5))
                : pendingResumeSeconds;

            try {
                playerVideo.currentTime = targetSeconds;
                resumeApplied = true;
            } catch (error) {
                resumeApplied = false;
            }
        }

        function scheduleResume(seconds) {
            pendingResumeSeconds = Number(seconds || 0);
            resumeApplied = false;

            if (pendingResumeSeconds > 0) {
                window.setTimeout(applyPendingResume, 250);
                window.setTimeout(applyPendingResume, 900);
            }
        }

        function activateResumeSource() {
            if (!playerVideo || !watchProgressConfig.resumeSourceId) {
                scheduleResume(watchProgressConfig.resumeSeconds);

                return;
            }

            const targetSourceId = String(watchProgressConfig.resumeSourceId);
            const currentSourceId = String(playerVideo.dataset.sourceId || '');
            const targetButton = Array.from(sourceButtons).find((button) => button.dataset.sourceId === targetSourceId);

            if (targetButton && currentSourceId !== targetSourceId) {
                targetButton.click();
            }

            scheduleResume(watchProgressConfig.resumeSeconds);
        }

        if (playerVideo) {
            playerVideo.addEventListener('timeupdate', () => saveWatchProgress());
            playerVideo.addEventListener('pause', () => saveWatchProgress(true));
            playerVideo.addEventListener('ended', () => saveWatchProgress(true, true));
            playerVideo.addEventListener('loadedmetadata', applyPendingResume);
            playerVideo.addEventListener('canplay', applyPendingResume);
            window.addEventListener('pagehide', () => saveWatchProgress(true));
            window.setTimeout(activateResumeSource, 100);
        }
    </script>
@endsection
