<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Water T1 E1 - Series GL</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet"
        href="{{ asset('assets/css/episodios.css') }}?v={{ filemtime(public_path('assets/css/episodios.css')) }}">
    <link rel="stylesheet" href="assets/style.css">

  
</head>

<body>

    <x-navbar />

    <!-- ══ MAIN LAYOUT ══ -->
    <div class="ep-layout">

        <!-- ═══ COLUMNA PRINCIPAL ═══ -->
        <main class="ep-main">

            <!-- breadcrumb -->
            <div class="ep-breadcrumb">
                <a href="#">Inicio</a>
                <span>›</span>
                <a href="#">Series GL</a>
                <span>›</span>
                <a href="#">The Water</a>
                <span>›</span>
                <span style="color:var(--text)">Temporada 1 · Episodio 1</span>
            </div>

            <!-- ── PLAYER ── -->
            <div class="player-wrap">
                <img src="https://picsum.photos/1200/675?27" alt="The Water" class="player-poster">
                <div class="player-overlay-ui">

                    <!-- top -->
                    <div class="player-top-bar">
                        <div class="player-title-badge">
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="currentColor">
                                <circle cx="4" cy="4" r="4" />
                            </svg>
                            T1 · Episodio 1
                        </div>
                        <div class="player-lang-flags">
                            <div class="flag-circle">🇹🇭</div>
                            <div class="flag-circle">🇲🇽</div>
                        </div>
                    </div>

                    <!-- center -->
                    <div class="player-center">
                        <button class="player-skip-btn" title="Retroceder 10s">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="1 4 1 10 7 10" />
                                <path d="M3.51 15a9 9 0 1 0 .49-4.95" /><text x="8" y="17"
                                    style="font-size:5px;fill:white;stroke:none">10</text>
                            </svg>
                        </button>
                        <button class="player-play-btn">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="#fff">
                                <path d="M8 5v14l11-7z" />
                            </svg>
                        </button>
                        <button class="player-skip-btn" title="Adelantar 10s">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="23 4 23 10 17 10" />
                                <path d="M20.49 15a9 9 0 1 1-.49-4.95" />
                            </svg>
                        </button>
                    </div>

                    <!-- bottom -->
                    <div class="player-bottom">
                        <div class="player-progress-bar">
                            <div class="player-progress-fill"></div>
                        </div>
                        <div class="player-controls-row">
                            <div class="player-ctrl-left">
                                <button class="player-ctrl-btn">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </button>
                                <button class="player-ctrl-btn">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white"
                                        stroke-width="2">
                                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" />
                                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14" />
                                        <path d="M15.54 8.46a5 5 0 0 1 0 7.07" />
                                    </svg>
                                </button>
                                <div class="player-vol-bar">
                                    <div class="player-vol-fill"></div>
                                </div>
                                <span class="player-time">8:24 / 28:50</span>
                            </div>
                            <div class="player-ctrl-right">
                                <button class="player-ctrl-btn"
                                    style="font-size:11px;color:rgba(255,255,255,0.7);font-weight:500;">1×</button>
                                <button class="player-ctrl-btn">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white"
                                        stroke-width="2">
                                        <rect x="2" y="2" width="20" height="20" rx="2.18" />
                                        <line x1="7" y1="2" x2="7" y2="22" />
                                        <line x1="17" y1="2" x2="17" y2="22" />
                                        <line x1="2" y1="12" x2="22" y2="12" />
                                    </svg>
                                </button>
                                <button class="player-ctrl-btn">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white"
                                        stroke-width="2">
                                        <path
                                            d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
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
                        4,243 vistas
                    </div>
                </div>
                <div class="server-list">
                    <div class="server-item active">
                        <div class="server-icon">⚡</div>
                        <div class="server-info">
                            <div class="server-name">BYSE</div>
                            <div class="server-meta">Audio Tailandés · Sub Español</div>
                        </div>
                        <span class="server-badge badge-ads">Con anuncios</span>
                    </div>
                    <div class="server-item">
                        <div class="server-icon">⚡</div>
                        <div class="server-info">
                            <div class="server-name">VOE</div>
                            <div class="server-meta">Audio Tailandés · Sub Español</div>
                        </div>
                        <span class="server-badge badge-ads">Con publicidad</span>
                    </div>
                    <div class="server-item">
                        <div class="server-icon">⚡</div>
                        <div class="server-info">
                            <div class="server-name">OK</div>
                            <div class="server-meta">Audio Tailandés · Sub Español</div>
                        </div>
                        <span class="server-badge badge-clean">Sin publicidad</span>
                    </div>
                    <div class="server-item">
                        <div class="server-icon">⚡</div>
                        <div class="server-info">
                            <div class="server-name">NETU</div>
                            <div class="server-meta">Audio Tailandés · Sub Español</div>
                        </div>
                        <span class="server-badge badge-clean">Sin publicidad</span>
                    </div>
                </div>
            </div>

            <!-- ── EPISODE TITLE + META ── -->
            <div class="ep-title-section">
                <h1>The Water · <em>Temporada 1 Episodio 1</em></h1>
                <div class="ep-meta-row">
                    <div class="ep-meta-item">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        Mar. 21, 2026
                    </div>
                    <div class="ep-meta-dot"></div>
                    <div class="ep-meta-item">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        28 min
                    </div>
                    <div class="ep-meta-dot"></div>
                    <div class="ep-meta-item" style="color: var(--rose);">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <polygon
                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                        4.8 / 5
                    </div>
                    <div class="ep-meta-dot"></div>
                    <div class="ep-meta-item">🇹🇭 Tailandia</div>
                </div>
            </div>

            <!-- ── NAV PREV/NEXT ── -->
            <div class="ep-nav">
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
                <a href="#" class="ep-nav-btn next">
                    <div class="ep-nav-arrow">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </div>
                    <div>
                        <div class="ep-nav-label">Siguiente</div>
                        <div class="ep-nav-title">Episodio 2 · Mar. 28</div>
                    </div>
                </a>
            </div>

            <!-- ── SHARE ── -->
            <div class="share-section">
                <span class="share-label">Compartir</span>
                <span class="share-count">17</span>
                <a href="#" class="share-btn share-fb">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="white">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                    </svg>
                    Facebook
                </a>
                <a href="#" class="share-btn share-tw">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="white">
                        <path
                            d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z" />
                    </svg>
                    Twitter
                </a>
                <a href="#" class="share-btn share-pin">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="white">
                        <path
                            d="M12 2C6.477 2 2 6.477 2 12c0 4.236 2.636 7.855 6.356 9.312-.088-.791-.167-2.005.035-2.868.181-.78 1.172-4.97 1.172-4.97s-.299-.598-.299-1.482c0-1.388.806-2.428 1.808-2.428.852 0 1.265.64 1.265 1.408 0 .858-.546 2.14-.828 3.33-.236.995.498 1.806 1.476 1.806 1.772 0 3.138-1.868 3.138-4.564 0-2.387-1.716-4.056-4.163-4.056-2.836 0-4.499 2.127-4.499 4.326 0 .856.33 1.773.741 2.274.081.099.093.186.069.286-.076.315-.244.995-.277 1.134-.044.183-.146.222-.337.134-1.249-.581-2.03-2.407-2.03-3.874 0-3.154 2.292-6.052 6.608-6.052 3.469 0 6.165 2.473 6.165 5.776 0 3.447-2.173 6.22-5.19 6.22-1.013 0-1.966-.527-2.292-1.148l-.623 2.378c-.226.869-.835 1.958-1.244 2.621.937.29 1.931.446 2.962.446 5.523 0 10-4.477 10-10S17.523 2 12 2z" />
                    </svg>
                    Pinterest
                </a>
                <a href="#" class="share-btn share-wa">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="white">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z" />
                    </svg>
                    WhatsApp
                </a>
            </div>

            <!-- ── EPISODE LIST ── -->
            <div class="ep-list-section">
                <div class="ep-list-header">
                    <span class="ep-list-title">Episodios · Temporada 1</span>
                    <div class="ep-list-filter">
                        <button class="ep-filter-pill active">Todos</button>
                        <button class="ep-filter-pill">Recientes</button>
                    </div>
                </div>
                <div class="ep-list">

                    <a href="#" class="ep-list-item current">
                        <div class="ep-list-thumb">
                            <img src="https://picsum.photos/140/90?71" alt="">
                            <div class="ep-thumb-num">E1</div>
                        </div>
                        <div class="ep-list-info">
                            <div class="ep-list-ep-title">Episodio 1</div>
                            <div class="ep-list-date">Mar. 21, 2026</div>
                        </div>
                        <div class="ep-list-playing">
                            <div class="playing-bar"></div>
                            <div class="playing-bar"></div>
                            <div class="playing-bar"></div>
                        </div>
                    </a>

                    <a href="#" class="ep-list-item">
                        <div class="ep-list-thumb"><img src="https://picsum.photos/140/90?72" alt="">
                            <div class="ep-thumb-num">E2</div>
                        </div>
                        <div class="ep-list-info">
                            <div class="ep-list-ep-title">Episodio 2</div>
                            <div class="ep-list-date">Mar. 28, 2026</div>
                        </div>
                    </a>

                    <a href="#" class="ep-list-item">
                        <div class="ep-list-thumb"><img src="https://picsum.photos/140/90?73" alt="">
                            <div class="ep-thumb-num">E3</div>
                        </div>
                        <div class="ep-list-info">
                            <div class="ep-list-ep-title">Episodio 3</div>
                            <div class="ep-list-date">Abr. 04, 2026</div>
                        </div>
                    </a>

                    <a href="#" class="ep-list-item">
                        <div class="ep-list-thumb"><img src="https://picsum.photos/140/90?74" alt="">
                            <div class="ep-thumb-num">E4</div>
                        </div>
                        <div class="ep-list-info">
                            <div class="ep-list-ep-title">Episodio 4</div>
                            <div class="ep-list-date">Abr. 11, 2026</div>
                        </div>
                    </a>

                    <a href="#" class="ep-list-item">
                        <div class="ep-list-thumb"><img src="https://picsum.photos/140/90?75" alt="">
                            <div class="ep-thumb-num">E5</div>
                        </div>
                        <div class="ep-list-info">
                            <div class="ep-list-ep-title">Episodio 5</div>
                            <div class="ep-list-date">Abr. 18, 2026</div>
                        </div>
                    </a>

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
                <div class="links-row">
                    <a href="#" class="links-row-option">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="8 12 12 16 16 12" />
                            <line x1="12" y1="8" x2="12" y2="16" />
                        </svg>
                        Descarga
                    </a>
                    <span class="links-row-lang">Sub Español</span>
                </div>
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
                    <span class="comments-count">4</span>
                </div>

                <!-- comment 1 -->
                <div class="comment-item">
                    <div class="comment-avatar">M</div>
                    <div class="comment-body">
                        <div class="comment-meta">
                            <span class="comment-user">Monica</span>
                            <span class="comment-date">22 Mar 2026</span>
                        </div>
                        <p class="comment-text">Me gusta mas su secretaria para pareja que la que metieron, tienen mucha
                            más química y la dinámica entre ellas es adorable 💕</p>
                        <div class="comment-actions">
                            <button class="comment-action-btn liked">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                </svg>
                                24
                            </button>
                            <button class="comment-action-btn">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                                Responder
                            </button>
                        </div>

                        <!-- reply -->
                        <div class="comment-reply">
                            <div style="display:flex;gap:10px;align-items:flex-start;">
                                <div class="comment-avatar av2" style="width:30px;height:30px;font-size:0.75rem;">S
                                </div>
                                <div style="flex:1;">
                                    <div class="comment-meta">
                                        <span class="comment-user">SakuraCL</span>
                                        <span class="comment-date">23 Mar 2026</span>
                                    </div>
                                    <p class="comment-text" style="margin-bottom:4px;">Totalmente de acuerdo!! La
                                        secretaria es lo mejor de la serie hasta ahora ✨</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- comment 2 -->
                <div class="comment-item">
                    <div class="comment-avatar av2">J</div>
                    <div class="comment-body">
                        <div class="comment-meta">
                            <span class="comment-user">JiyeonGL</span>
                            <span class="comment-date">24 Mar 2026</span>
                        </div>
                        <p class="comment-text">Increíble primer episodio, la fotografía es preciosa y la OST me llegó
                            al corazón 😭🎶 Ya quiero ver el siguiente!</p>
                        <div class="comment-actions">
                            <button class="comment-action-btn">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path
                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                </svg>
                                11
                            </button>
                            <button class="comment-action-btn">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                                Responder
                            </button>
                        </div>
                    </div>
                </div>

                <!-- comment 3 -->
                <div class="comment-item">
                    <div class="comment-avatar av3">V</div>
                    <div class="comment-body">
                        <div class="comment-meta">
                            <span class="comment-user">ValeriaMX</span>
                            <span class="comment-date">01 Abr 2026</span>
                        </div>
                        <p class="comment-text">Llevaba meses esperando esta serie y no me decepcionó. Las actrices
                            tienen una química natural y la dirección es muy buena. 10/10 recomendada 🌊</p>
                        <div class="comment-actions">
                            <button class="comment-action-btn">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path
                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                </svg>
                                8
                            </button>
                            <button class="comment-action-btn">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                                Responder
                            </button>
                        </div>
                    </div>
                </div>

                <!-- comment form -->
                <div class="comment-form">
                    <div class="comment-form-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        </svg>
                        Deja un comentario
                    </div>
                    <textarea class="cf-textarea" placeholder="Tu comentario…"></textarea>
                    <div class="cf-fields">
                        <div class="cf-field">
                            <label>Nombre <span>*</span></label>
                            <input type="text" class="cf-input" placeholder="Tu nombre">
                        </div>
                        <div class="cf-field">
                            <label>Correo electrónico <span>*</span></label>
                            <input type="email" class="cf-input" placeholder="No será publicado">
                        </div>
                    </div>
                    <div class="cf-check-row">
                        <input type="checkbox" class="cf-check" id="saveInfo">
                        <label for="saveInfo">Guarda mi nombre y correo para la próxima vez que comente</label>
                    </div>
                    <button class="cf-submit">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13" />
                            <polygon points="22 2 15 22 11 13 2 9 22 2" />
                        </svg>
                        Publicar comentario
                    </button>
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

                    <a href="#" class="sidebar-item">
                        <span class="sidebar-rank top3">1</span>
                        <div class="sidebar-thumb">
                            <img src="https://picsum.photos/200/130?41" alt="">
                            <div class="sidebar-thumb-overlay"><svg width="16" height="16" viewBox="0 0 24 24"
                                    fill="white">
                                    <path d="M8 5v14l11-7z" />
                                </svg></div>
                        </div>
                        <div class="sidebar-info">
                            <div class="sidebar-series">Love Design</div>
                            <div class="sidebar-year">2025 · Tailandia</div>
                        </div>
                    </a>

                    <a href="#" class="sidebar-item">
                        <span class="sidebar-rank top3">2</span>
                        <div class="sidebar-thumb">
                            <img src="https://picsum.photos/200/130?42" alt="">
                            <div class="sidebar-thumb-overlay"><svg width="16" height="16" viewBox="0 0 24 24"
                                    fill="white">
                                    <path d="M8 5v14l11-7z" />
                                </svg></div>
                        </div>
                        <div class="sidebar-info">
                            <div class="sidebar-series">Harmony Secret</div>
                            <div class="sidebar-year">2025 · Tailandia</div>
                        </div>
                    </a>

                    <a href="#" class="sidebar-item">
                        <span class="sidebar-rank top3">3</span>
                        <div class="sidebar-thumb">
                            <img src="https://picsum.photos/200/130?43" alt="">
                            <div class="sidebar-thumb-overlay"><svg width="16" height="16" viewBox="0 0 24 24"
                                    fill="white">
                                    <path d="M8 5v14l11-7z" />
                                </svg></div>
                        </div>
                        <div class="sidebar-info">
                            <div class="sidebar-series">ClaireBell</div>
                            <div class="sidebar-year">2025 · Tailandia</div>
                        </div>
                    </a>

                    <a href="#" class="sidebar-item">
                        <span class="sidebar-rank">4</span>
                        <div class="sidebar-thumb">
                            <img src="https://picsum.photos/200/130?44" alt="">
                            <div class="sidebar-thumb-overlay"><svg width="16" height="16" viewBox="0 0 24 24"
                                    fill="white">
                                    <path d="M8 5v14l11-7z" />
                                </svg></div>
                        </div>
                        <div class="sidebar-info">
                            <div class="sidebar-series">Queendom</div>
                            <div class="sidebar-year">2025 · Corea</div>
                        </div>
                    </a>

                    <a href="#" class="sidebar-item">
                        <span class="sidebar-rank">5</span>
                        <div class="sidebar-thumb">
                            <img src="https://picsum.photos/200/130?45" alt="">
                            <div class="sidebar-thumb-overlay"><svg width="16" height="16" viewBox="0 0 24 24"
                                    fill="white">
                                    <path d="M8 5v14l11-7z" />
                                </svg></div>
                        </div>
                        <div class="sidebar-info">
                            <div class="sidebar-series">Somewhere Somehow</div>
                            <div class="sidebar-year">2025 · Tailandia</div>
                        </div>
                    </a>

                    <a href="#" class="sidebar-item">
                        <span class="sidebar-rank">6</span>
                        <div class="sidebar-thumb">
                            <img src="https://picsum.photos/200/130?46" alt="">
                            <div class="sidebar-thumb-overlay"><svg width="16" height="16" viewBox="0 0 24 24"
                                    fill="white">
                                    <path d="M8 5v14l11-7z" />
                                </svg></div>
                        </div>
                        <div class="sidebar-info">
                            <div class="sidebar-series">My Only Sunshine</div>
                            <div class="sidebar-year">2026 · Tailandia</div>
                        </div>
                    </a>

                    <a href="#" class="sidebar-item">
                        <span class="sidebar-rank">7</span>
                        <div class="sidebar-thumb">
                            <img src="https://picsum.photos/200/130?47" alt="">
                            <div class="sidebar-thumb-overlay"><svg width="16" height="16" viewBox="0 0 24 24"
                                    fill="white">
                                    <path d="M8 5v14l11-7z" />
                                </svg></div>
                        </div>
                        <div class="sidebar-info">
                            <div class="sidebar-series">Scandal Love</div>
                            <div class="sidebar-year">2026 · Tailandia</div>
                        </div>
                    </a>

                    <a href="#" class="sidebar-item">
                        <span class="sidebar-rank">8</span>
                        <div class="sidebar-thumb">
                            <img src="https://picsum.photos/200/130?48" alt="">
                            <div class="sidebar-thumb-overlay"><svg width="16" height="16" viewBox="0 0 24 24"
                                    fill="white">
                                    <path d="M8 5v14l11-7z" />
                                </svg></div>
                        </div>
                        <div class="sidebar-info">
                            <div class="sidebar-series">Girl Rules</div>
                            <div class="sidebar-year">2026 · Tailandia</div>
                        </div>
                    </a>

                </div>
            </div>
        </aside>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });
    </script>
</body>

</html>