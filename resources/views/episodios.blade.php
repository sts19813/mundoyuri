@extends('layouts.portal')

@section('body_attributes')
    class="episode-page-body"
@endsection

@section('head')

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $episodeCanonical = $episode ? route('public.episodes.show', $episode->slug) : route('legacy.episodios');
        $episodeSeoTitle = $episode ? $episode->seoPageTitle() : 'Últimos episodios GL';
        $episodeSeoDescription = $episode
            ? $episode->seoMetaDescription()
            : 'Mira los episodios más recientes de series y doramas Girls Love en Mundo Yuri.';
        $episodeSeoImage = $episode ? $episode->imageUrl('1200/630') : null;
        $episodeSchema = null;

        if ($episode) {
            $episodeEntityId = $episodeCanonical.'#episode';
            $videoEntityId = $episodeCanonical.'#video';
            $breadcrumbEntityId = $episodeCanonical.'#breadcrumb';
            $seoSource = $episode->sources
                ->sortBy('sort_order')
                ->sortByDesc('is_primary')
                ->first();
            $videoContentUrl = $seoSource?->player_type === 'video' ? $seoSource->direct_video_url : null;
            $videoEmbedUrl = $seoSource?->player_type === 'iframe' ? $seoSource->playable_url : null;
            $uploadDate = $episode->release_date?->copy()->startOfDay()->toAtomString()
                ?: $episode->published_at?->toAtomString();
            $videoObject = $seoSource ? array_filter([
                '@type' => 'VideoObject',
                '@id' => $videoEntityId,
                'name' => $series->title.' episodio '.$episode->episode_number,
                'description' => $episodeSeoDescription,
                'thumbnailUrl' => [$episodeSeoImage],
                'uploadDate' => $uploadDate,
                'duration' => $episode->duration_minutes ? 'PT'.$episode->duration_minutes.'M' : null,
                'contentUrl' => $videoContentUrl,
                'embedUrl' => $videoEmbedUrl,
                'url' => $episodeCanonical,
                'inLanguage' => 'es-MX',
                'isFamilyFriendly' => true,
                'potentialAction' => [
                    '@type' => 'WatchAction',
                    'target' => $episodeCanonical,
                ],
            ], fn ($value) => $value !== null && $value !== '') : null;
            $tvEpisode = array_filter([
                '@type' => 'TVEpisode',
                '@id' => $episodeEntityId,
                'name' => $series->title.' episodio '.$episode->episode_number,
                'alternateName' => $episode->seoAlternateNames(),
                'description' => $episodeSeoDescription,
                'url' => $episodeCanonical,
                'episodeNumber' => $episode->episode_number,
                'seasonNumber' => $episode->season_number,
                'datePublished' => $episode->release_date?->toDateString() ?: $episode->published_at?->toDateString(),
                'image' => $episodeSeoImage,
                'inLanguage' => 'es-MX',
                'isAccessibleForFree' => true,
                'partOfSeries' => [
                    '@type' => 'TVSeries',
                    '@id' => route('catalog.series.show', $series->slug).'#title',
                    'name' => $series->title,
                    'url' => route('catalog.series.show', $series->slug),
                ],
                'associatedMedia' => $videoObject ? ['@id' => $videoEntityId] : null,
            ], fn ($value) => $value !== null && $value !== [] && $value !== '');
            $episodeGraph = [
                [
                    '@type' => 'WebSite',
                    '@id' => route('home').'#website',
                    'url' => route('home'),
                    'name' => 'Mundo Yuri',
                    'inLanguage' => 'es-MX',
                ],
                [
                    '@type' => 'WebPage',
                    '@id' => $episodeCanonical.'#webpage',
                    'url' => $episodeCanonical,
                    'name' => $episodeSeoTitle,
                    'description' => $episodeSeoDescription,
                    'inLanguage' => 'es-MX',
                    'isPartOf' => ['@id' => route('home').'#website'],
                    'breadcrumb' => ['@id' => $breadcrumbEntityId],
                    'mainEntity' => ['@id' => $episodeEntityId],
                    'primaryImageOfPage' => ['@type' => 'ImageObject', 'url' => $episodeSeoImage],
                ],
                [
                    '@type' => 'BreadcrumbList',
                    '@id' => $breadcrumbEntityId,
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => route('home')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => $series->title, 'item' => route('catalog.series.show', $series->slug)],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => 'Episodio '.$episode->episode_number, 'item' => $episodeCanonical],
                    ],
                ],
                $tvEpisode,
            ];

            if ($videoObject) {
                $episodeGraph[] = $videoObject;
            }

            $episodeSchema = ['@context' => 'https://schema.org', '@graph' => $episodeGraph];
        }
    @endphp
    <x-seo
        :title="$episodeSeoTitle"
        :description="$episodeSeoDescription"
        :canonical="$episodeCanonical"
        :image="$episodeSeoImage"
        :image-alt="$episode ? 'Imagen de '.$series->title.' episodio '.$episode->episode_number : null"
        :type="$episode ? 'video.episode' : 'website'"
        :schema="$episodeSchema"
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

        @php
            $primaryCastUrl = $primarySource?->provider === 'cloudflare_hls'
                ? $primarySource->direct_video_url
                : null;
        @endphp

        <div class="ep-layout">
            <main class="ep-main">
                <nav class="ep-breadcrumb" aria-label="Migas de pan">
                    <a href="{{ route('home') }}">Inicio</a>
                    <span>›</span>
                    <a href="{{ route('catalog.series.index') }}">Series GL, yuri y anime</a>
                    <span>›</span>
                    <a href="{{ route('catalog.series.show', $series->slug) }}">{{ $series->title }}</a>
                    <span>›</span>
                    <span style="color:var(--text)">Temporada {{ $episode->season_number }} · Episodio {{ $episode->episode_number }}</span>
                </nav>

                <div class="player-wrap">
                    @if($primarySource)
                        <iframe id="episodePlayer" class="player-embed" src="{{ $primarySource->player_type === 'iframe' ? $primarySource->playable_url : '' }}"
                            title="{{ $series->title }} - Episodio {{ $episode->episode_number }}"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                            loading="lazy"
                            @if($primarySource->player_type !== 'iframe') style="display:none;" @endif></iframe>
                        <video id="episodeVideoPlayer" class="player-embed" controls playsinline preload="metadata" x-webkit-airplay="allow"
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
                            :alt="'Imagen de '.$series->title.' episodio '.$episode->episode_number"
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
                        <div class="player-actions">
                            <button type="button" class="player-remote-action" id="episodeRemotePlaybackButton" hidden aria-label="Transmitir a una TV" title="Transmitir a una TV">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="13" rx="2" />
                                    <path d="M8 21h8M12 17v4M16.5 8.5a3.5 3.5 0 0 1 0 5M19 6a7 7 0 0 1 0 10" />
                                </svg>
                                <span>Transmitir</span>
                            </button>
                            <button type="button" class="player-remote-action" id="episodeAirplayButton" hidden aria-label="Transmitir con AirPlay" title="Transmitir con AirPlay">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="13" rx="2" />
                                    <path d="m8 21 4-4 4 4" />
                                </svg>
                                <span>AirPlay</span>
                            </button>
                            <div class="player-favorite-action">
                                <x-favorite-series-button :series="$series" compact />
                            </div>
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
                                <button type="button" class="btn btn-sm {{ $part->is_primary || ($loop->first && !$partSources->contains(fn($item) => $item->is_primary)) ? 'btn-primary' : 'btn-light-primary' }} source-switcher" data-source-id="{{ $part->id }}" data-video-url="{{ $part->playable_url }}" data-cast-url="{{ $part->provider === 'cloudflare_hls' ? $part->direct_video_url : '' }}" data-provider="PARTE {{ $part->sort_order ?: $loop->iteration }}" data-provider-key="{{ $part->provider }}" data-trackable="{{ in_array($part->provider, $trackableProviders, true) ? '1' : '0' }}" data-player-type="{{ $part->player_type }}">
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
                                    data-cast-url="{{ $source->provider === 'cloudflare_hls' ? $source->direct_video_url : '' }}"
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
                            <h1>{{ $series->title }} episodio {{ $episode->episode_number }}</h1>
                            <p class="ep-episode-label">
                                Temporada {{ $episode->season_number }}
                                @if($showEpisodeDisplayTitle)
                                    <span>{{ $episodeDisplayTitle }}</span>
                                @endif
                            </p>
                            @if(filled($episode->description))
                                <p class="ep-seo-description">{{ $episode->description }}</p>
                            @endif
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

                <section class="ep-list-section" aria-labelledby="season-episodes-title">
                    <div class="ep-list-header">
                        <h2 class="ep-list-title" id="season-episodes-title">Más episodios de {{ $series->title }} · Temporada {{ $episode->season_number }}</h2>
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
                </section>

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
                <x-catalog.sidebar-tabs :marathoners="$marathoners" :recent-episodes="$recentEpisodes" />
            </aside>

        </div>

        <x-footer />
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1.6.14/dist/hls.min.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <script>
        window.__onGCastApiAvailable = (isAvailable) => {
            window.dispatchEvent(new CustomEvent('mundo-yuri-cast-ready', { detail: { isAvailable } }));
        };
    </script>
    <script src="https://www.gstatic.com/cv/js/sender/v1/cast_sender.js?loadCastFramework=1" async defer></script>
    <script>
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });

        const playerFrame = document.getElementById('episodePlayer');
        const playerVideo = document.getElementById('episodeVideoPlayer');
        const remotePlaybackButton = document.getElementById('episodeRemotePlaybackButton');
        const airplayButton = document.getElementById('episodeAirplayButton');
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
        let activeCastUrl = @json($primaryCastUrl);
        let activePlayerType = @json($primarySource?->player_type);
        let activeProviderKey = @json($primarySource?->provider);
        let castSdkReady = false;
        let castDeviceAvailable = false;
        let airplayAvailable = false;
        let remotePlaybackAvailable = false;
        const subtitleTracks = @json($episodeSubtitleTracks ?? []);
        const castMetadata = {
            title: @json($series->title),
            subtitle: @json('Temporada '.$episode->season_number.' · Episodio '.$episode->episode_number),
            image: @json($episode->previewMediaUrl('1200/675')),
        };

        const canTransmitActiveSource = () => activePlayerType === 'video'
            && activeProviderKey === 'cloudflare_hls'
            && Boolean(activeCastUrl);

        const setRemoteButtonVisibility = () => {
            const canTransmit = canTransmitActiveSource();

            if (remotePlaybackButton) {
                // Keep the Cast entry point visible as soon as the browser has
                // the sender SDK. Discovery can take a few seconds on Google TV.
                remotePlaybackButton.hidden = !canTransmit || !(castSdkReady || remotePlaybackAvailable);
            }

            if (airplayButton) {
                airplayButton.hidden = !canTransmit || !airplayAvailable;
            }
        };

        const updateRemoteSource = (providerKey, playerType, castUrl = '') => {
            activeProviderKey = providerKey;
            activePlayerType = playerType;
            activeCastUrl = providerKey === 'cloudflare_hls' && playerType === 'video' ? castUrl : '';
            setRemoteButtonVisibility();
        };

        const playerCurrentTime = () => Math.max(0, Number(playerVideo?.currentTime || 0));

        function configureCastSdk() {
            if (!window.cast?.framework || !window.chrome?.cast || castSdkReady) {
                return;
            }

            castSdkReady = true;
            const castContext = window.cast.framework.CastContext.getInstance();
            castContext.setOptions({
                receiverApplicationId: window.chrome.cast.media.DEFAULT_MEDIA_RECEIVER_APP_ID,
                autoJoinPolicy: window.chrome.cast.AutoJoinPolicy.ORIGIN_SCOPED,
            });
            castContext.addEventListener(window.cast.framework.CastContextEventType.CAST_STATE_CHANGED, (event) => {
                castDeviceAvailable = event.castState !== window.cast.framework.CastState.NO_DEVICES_AVAILABLE;
                setRemoteButtonVisibility();
            });
            castDeviceAvailable = castContext.getCastState() !== window.cast.framework.CastState.NO_DEVICES_AVAILABLE;
            setRemoteButtonVisibility();
        }

        async function loadCastMedia() {
            if (!canTransmitActiveSource() || !window.cast?.framework || !window.chrome?.cast) {
                return;
            }

            const session = window.cast.framework.CastContext.getInstance().getCurrentSession();
            if (!session) {
                return;
            }

            const mediaInfo = new window.chrome.cast.media.MediaInfo(activeCastUrl, 'application/x-mpegURL');
            const metadata = new window.chrome.cast.media.GenericMediaMetadata();
            metadata.title = castMetadata.title;
            metadata.subtitle = castMetadata.subtitle;
            if (castMetadata.image) {
                metadata.images = [new window.chrome.cast.Image(castMetadata.image)];
            }
            mediaInfo.metadata = metadata;

            const request = new window.chrome.cast.media.LoadRequest(mediaInfo);
            request.currentTime = playerCurrentTime();
            request.autoplay = !playerVideo?.paused;
            await session.loadMedia(request);
            directPlayer?.pause();
        }

        async function startRemotePlayback() {
            if (!canTransmitActiveSource()) {
                return;
            }

            try {
                if (castSdkReady) {
                    const castContext = window.cast.framework.CastContext.getInstance();
                    if (!castContext.getCurrentSession()) {
                        await castContext.requestSession();
                    }
                    await loadCastMedia();
                    return;
                }

                if (playerVideo?.remote?.prompt) {
                    await playerVideo.remote.prompt();
                }
            } catch (error) {
                // Closing the native device picker is an expected user action.
                console.info('La transmisión a la TV se canceló o no estuvo disponible.', error);
            }
        }

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

        window.addEventListener('mundo-yuri-cast-ready', (event) => {
            if (event.detail?.isAvailable) {
                configureCastSdk();
            }
        });
        configureCastSdk();

        if (playerVideo?.remote?.watchAvailability) {
            playerVideo.remote.watchAvailability((available) => {
                remotePlaybackAvailable = Boolean(available);
                setRemoteButtonVisibility();
            }).catch(() => {
                remotePlaybackAvailable = false;
                setRemoteButtonVisibility();
            });
        }

        if (playerVideo?.webkitShowPlaybackTargetPicker) {
            playerVideo.addEventListener('webkitplaybacktargetavailabilitychanged', (event) => {
                airplayAvailable = event.availability === 'available';
                setRemoteButtonVisibility();
            });
        }

        remotePlaybackButton?.addEventListener('click', startRemotePlayback);
        airplayButton?.addEventListener('click', () => {
            if (canTransmitActiveSource() && playerVideo?.webkitShowPlaybackTargetPicker) {
                playerVideo.webkitShowPlaybackTargetPicker();
            }
        });
        setRemoteButtonVisibility();

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

        function switchEpisodePlayer(type, url, providerKey = '', sourceId = '', trackable = '0', castUrl = '') {
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
                        updateRemoteSource(providerKey, type, castUrl);

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

                updateRemoteSource(providerKey, type, castUrl);

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
            updateRemoteSource(providerKey, type);
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
                    const castUrl = button.getAttribute('data-cast-url') || '';
                    const activeSourceLabel = document.querySelector('[data-active-source-label] span');

                    switchEpisodePlayer(playerType, nextUrl, providerKey, sourceId, trackable, castUrl);

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
