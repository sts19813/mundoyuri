<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $episode ? ($series->title.' · '.$episode->title.' - Series GL') : 'Episodios - Series GL' }}</title>
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

    @if(!$episode)
        <section class="py-5 mt-5">
            <div class="container-xl px-4">
                <div class="alert alert-warning mb-0">Aún no hay episodios publicados.</div>
            </div>
        </section>
        <x-footer />
    @else
        @php
            $primarySource = $episode->sources->firstWhere('is_primary', true) ?: $episode->sources->first();
            $shareUrl = route('public.episodes.show', $episode->slug);
            $releaseDate = $episode->release_date ?: $episode->published_at;
            $avatarClasses = ['', 'av2', 'av3'];
            $comments = $episode->comments;
        @endphp

        <!-- ══ MAIN LAYOUT ══ -->
        <div class="ep-layout">

            <!-- ═══ COLUMNA PRINCIPAL ═══ -->
            <main class="ep-main">

                <!-- breadcrumb -->
                <div class="ep-breadcrumb">
                    <a href="{{ route('home') }}">Inicio</a>
                    <span>›</span>
                    <a href="{{ route('catalog.series.index') }}">Series GL</a>
                    <span>›</span>
                    <a href="{{ route('catalog.series.show', $series->slug) }}">{{ $series->title }}</a>
                    <span>›</span>
                    <span style="color:var(--text)">Temporada {{ $episode->season_number }} · Episodio {{ $episode->episode_number }}</span>
                </div>

                <!-- ── PLAYER ── -->
                <div class="player-wrap">
                    @if($primarySource)
                        <iframe id="episodePlayer" class="player-embed" src="{{ $primarySource->playable_url }}"
                            title="{{ $series->title }} - Episodio {{ $episode->episode_number }}"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                            loading="lazy"></iframe>
                    @else
                        <x-media-preview
                            :src="$episode->thumbnail_image ?: ($series->bannerMediaUrl() ?: 'https://picsum.photos/1200/675?'.$episode->id)"
                            :type="$episode->thumbnail_image ? 'image' : ($series->bannerMediaUrl() ? $series->bannerMediaType() : 'image')"
                            :alt="$series->title"
                            class="player-poster"
                            :autoplay="!$episode->thumbnail_image && $series->bannerMediaType() === 'video'"
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

                <!-- ── SERVERS ── -->
                <div class="server-section">
                    <div class="server-header">
                        <span class="server-header-title">Fuentes de vídeo</span>
                        <div class="server-views">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            {{ $episode->sources->count() }} fuentes
                        </div>
                    </div>
                    <div class="server-list">
                        @forelse($episode->sources as $source)
                            <button type="button"
                                class="server-item source-switcher {{ $source->is_primary ? 'active' : '' }}"
                                data-video-url="{{ $source->playable_url }}"
                                data-provider="{{ strtoupper($source->provider) }}">
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
                                    <div class="server-name">Sin fuentes</div>
                                    <div class="server-meta">Este episodio aún no tiene enlaces disponibles.</div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- ── EPISODE TITLE + META ── -->
                <div class="ep-title-section">
                    <h1>{{ $series->title }} · <em>Temporada {{ $episode->season_number }} Episodio {{ $episode->episode_number }}</em></h1>
                    <div class="ep-meta-row">
                        <div class="ep-meta-item">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            {{ $releaseDate ? $releaseDate->format('d/m/Y') : 'Sin fecha' }}
                        </div>
                        <div class="ep-meta-dot"></div>
                        <div class="ep-meta-item">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            {{ $episode->duration_minutes ? $episode->duration_minutes.' min' : 'Sin duración' }}
                        </div>
                        <div class="ep-meta-dot"></div>
                        <div class="ep-meta-item">{{ $series->country_of_origin ?: 'País no definido' }}</div>
                    </div>
                </div>

                <!-- ── NAV PREV/NEXT ── -->
                <div class="ep-nav">
                    @if($previousEpisode)
                        <a href="{{ route('public.episodes.show', $previousEpisode->slug) }}" class="ep-nav-btn prev">
                            <div class="ep-nav-arrow">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
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
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
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
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
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
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
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

                <!-- ── SHARE ── -->
                <div class="share-section">
                    <span class="share-label">Compartir</span>
                    <span class="share-count">{{ $comments->count() }}</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="share-btn share-fb">Facebook</a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($series->title.' - Episodio '.$episode->episode_number) }}" target="_blank" rel="noopener" class="share-btn share-tw">Twitter</a>
                    <a href="https://wa.me/?text={{ urlencode($series->title.' - '.$shareUrl) }}" target="_blank" rel="noopener" class="share-btn share-wa">WhatsApp</a>
                </div>

                <!-- ── EPISODE LIST ── -->
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
                                    <img src="{{ $item->thumbnail_image ?: 'https://picsum.photos/140/90?'.$item->id }}" alt="">
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

                <!-- ── DOWNLOAD LINKS ── -->
                <div class="links-section">
                    <div class="links-header">
                        <span class="links-title">Enlaces</span>
                        <span class="links-badge">Descarga</span>
                    </div>
                    <div class="links-table-head">
                        <span>Opciones</span>
                        <span>Idioma</span>
                    </div>
                    @forelse($episode->sources as $source)
                        <div class="links-row">
                            <a href="{{ $source->playable_url }}" target="_blank" rel="noopener" class="links-row-option">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="8 12 12 16 16 12" />
                                    <line x1="12" y1="8" x2="12" y2="16" />
                                </svg>
                                {{ strtoupper($source->provider) }}
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

                <!-- ── COMMENTS ── -->
                <div class="comments-section">
                    <div class="comments-header">
                        <div class="comments-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                        </div>
                        <span class="comments-title">Comentarios</span>
                        <span class="comments-count">{{ $comments->count() }}</span>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @foreach($comments as $comment)
                        @php
                            $avatar = mb_strtoupper(mb_substr($comment->display_alias, 0, 1));
                            $avatarClass = $avatarClasses[$loop->index % count($avatarClasses)];
                        @endphp
                        <div class="comment-item">
                            <div class="comment-avatar {{ $avatarClass }}">{{ $avatar }}</div>
                            <div class="comment-body">
                                <div class="comment-meta">
                                    <span class="comment-user">{{ $comment->display_alias }}</span>
                                    <span class="comment-date">{{ $comment->created_at->format('d M Y') }}</span>
                                </div>
                                <p class="comment-text">{{ $comment->body }}</p>

                                @foreach($comment->replies as $reply)
                                    <div class="comment-reply">
                                        <div style="display:flex;gap:10px;align-items:flex-start;">
                                            <div class="comment-avatar av2" style="width:30px;height:30px;font-size:0.75rem;">
                                                {{ mb_strtoupper(mb_substr($reply->display_alias, 0, 1)) }}
                                            </div>
                                            <div style="flex:1;">
                                                <div class="comment-meta">
                                                    <span class="comment-user">{{ $reply->display_alias }}</span>
                                                    <span class="comment-date">{{ $reply->created_at->format('d M Y') }}</span>
                                                </div>
                                                <p class="comment-text" style="margin-bottom:4px;">{{ $reply->body }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <!-- comment form -->
                    <div class="comment-form">
                        <form method="POST" action="{{ route('comments.store') }}">
                            @csrf
                            <input type="hidden" name="target_type" value="episode">
                            <input type="hidden" name="target_id" value="{{ $episode->id }}">

                            <div class="comment-form-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                                Deja un comentario
                            </div>

                            <textarea class="cf-textarea" name="body" placeholder="Tu comentario…">{{ old('body') }}</textarea>
                            @error('body')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            <div class="cf-fields">
                                @guest
                                    <div class="cf-field">
                                        <label>Alias <span>*</span></label>
                                        <input type="text" name="alias" class="cf-input" placeholder="Tu alias" value="{{ old('alias') }}">
                                        @error('alias')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <div class="cf-field">
                                        <label>Comentarás como</label>
                                        <input type="text" class="cf-input" value="{{ auth()->user()->alias ?: auth()->user()->name }}" disabled>
                                    </div>
                                @endguest

                                <div class="cf-field">
                                    <label>Correo electrónico</label>
                                    <input type="email" class="cf-input" placeholder="No será publicado" disabled>
                                </div>
                            </div>

                            <div class="cf-check-row">
                                <input type="checkbox" class="cf-check" id="saveInfo" disabled>
                                <label for="saveInfo">Guarda mi nombre y correo para la próxima vez que comente</label>
                            </div>
                            <button class="cf-submit" type="submit">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13" />
                                    <polygon points="22 2 15 22 11 13 2 9 22 2" />
                                </svg>
                                Publicar comentario
                            </button>
                        </form>
                    </div>
                </div>

                <!-- disclaimer -->
                <div class="ep-disclaimer">
                    Este sitio no almacena archivos ni videos. Todo el contenido proviene de fuentes externas y se encuentra
                    alojado en sitios de terceros.
                </div>

            </main>

            <!-- ═══ SIDEBAR ═══ -->
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
                                    <img src="{{ $item->thumbnail_image ?: 'https://picsum.photos/200/130?'.$item->id }}" alt="">
                                    <div class="sidebar-thumb-overlay"><svg width="16" height="16" viewBox="0 0 24 24"
                                            fill="white">
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
    <script>
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });

        const playerFrame = document.getElementById('episodePlayer');
        const activeSourceLabel = document.getElementById('activeSourceLabel');
        const sourceButtons = document.querySelectorAll('.source-switcher');

        if (playerFrame && sourceButtons.length > 0) {
            sourceButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const nextUrl = button.getAttribute('data-video-url');
                    const provider = button.getAttribute('data-provider');

                    if (!nextUrl) {
                        return;
                    }

                    playerFrame.src = nextUrl;

                    sourceButtons.forEach((item) => item.classList.remove('active'));
                    button.classList.add('active');

                    if (activeSourceLabel) {
                        activeSourceLabel.textContent = provider || 'FUENTE';
                    }
                });
            });
        }
    </script>
</body>

</html>
