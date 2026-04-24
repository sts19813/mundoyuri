<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mundo GL</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>

<body>

    <x-navbar />

    <!-- ══ HERO ══ -->
    <section class="hero">
        <video class="hero-video" autoplay muted loop playsinline>
            <source src="/assets/video/BG.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-grain"></div>

        <div class="hero-content container-xl px-4 pt-5">
            <div class="hero-tag">
                <span class="brand-heart" style="width:14px;height:14px;"></span>
                Contenido GL · Actualizado diario
            </div>
            <h1>Descubre <em>nuevas historias</em> que te harán sentir</h1>
            <p class="hero-desc">Series, doramas y películas GL de todo el mundo. Subtituladas con amor, actualizadas
                cada día.</p>
            <div class="hero-actions">
                <a href="/episodios" class="btn-rose">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                    Explorar ahora
                </a>
                <a href="#" class="btn-ghost">Ver novedades</a>
            </div>
            <div class="hero-stats">
                <div>
                    <div class="hero-stat-num">56+</div>
                    <div class="hero-stat-label">Series activas</div>
                </div>
                <div>
                    <div class="hero-stat-num">400+</div>
                    <div class="hero-stat-label">Episodios</div>
                </div>
                <div>
                    <div class="hero-stat-num">12</div>
                    <div class="hero-stat-label">Géneros</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ CARRUSEL DESTACADO ══ -->
    <section >
        <div class="container-xl px-4">
            <div class="section-header">
                <h2 class="section-title">Doramas destacados</h2>
                <a href="#" class="section-link">Ver todo →</a>
            </div>

            <!-- Controles -->
            <div style="display:flex; gap:10px; margin-bottom:20px;">
                <button onclick="scrollRail(-1)"
                    style="background:var(--dark-card);border:1px solid rgba(244,63,142,0.2);color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:1rem;">‹</button>
                <button onclick="scrollRail(1)"
                    style="background:var(--dark-card);border:1px solid rgba(244,63,142,0.2);color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:1rem;">›</button>
            </div>

            <div class="featured-rail" id="featuredRail">
                <!-- cards 1–9 -->
                <div class="featured-card">
                    <img src="https://picsum.photos/800/400?1" alt="">
                    <div class="featured-card-overlay"></div>
                    <div class="live-dot"></div>
                    <div class="featured-card-body">
                        <span class="featured-card-badge">Dorama</span>
                        <h5>The Secret of Us</h5>
                        <small>2024 · En curso</small>
                    </div>
                </div>
                <div class="featured-card">
                    <img src="https://picsum.photos/800/400?2" alt="">
                    <div class="featured-card-overlay"></div>
                    <div class="live-dot"></div>
                    <div class="featured-card-body">
                        <span class="featured-card-badge">Dorama</span>
                        <h5>Scandal Love</h5>
                        <small>2026 · En curso</small>
                    </div>
                </div>
                <div class="featured-card">
                    <img src="https://picsum.photos/800/400?3" alt="">
                    <div class="featured-card-overlay"></div>
                    <div class="featured-card-body">
                        <span class="featured-card-badge">Dorama</span>
                        <h5>Hometown Romance</h5>
                        <small>2026 · Completada</small>
                    </div>
                </div>
                <div class="featured-card">
                    <img src="https://picsum.photos/800/400?4" alt="">
                    <div class="featured-card-overlay"></div>
                    <div class="live-dot"></div>
                    <div class="featured-card-body">
                        <span class="featured-card-badge">Dorama</span>
                        <h5>Broken Love</h5>
                        <small>2026 · En curso</small>
                    </div>
                </div>
                <div class="featured-card">
                    <img src="https://picsum.photos/800/400?5" alt="">
                    <div class="featured-card-overlay"></div>
                    <div class="featured-card-body">
                        <span class="featured-card-badge">Dorama</span>
                        <h5>Shadow of Love</h5>
                        <small>2026 · Completada</small>
                    </div>
                </div>
                <div class="featured-card">
                    <img src="https://picsum.photos/800/400?6" alt="">
                    <div class="featured-card-overlay"></div>
                    <div class="live-dot"></div>
                    <div class="featured-card-body">
                        <span class="featured-card-badge">Dorama</span>
                        <h5>My Only Sunshine</h5>
                        <small>2026 · En curso</small>
                    </div>
                </div>
                <div class="featured-card">
                    <img src="https://picsum.photos/800/400?7" alt="">
                    <div class="featured-card-overlay"></div>
                    <div class="featured-card-body">
                        <span class="featured-card-badge">Dorama</span>
                        <h5>Girl Rules</h5>
                        <small>2026 · Completada</small>
                    </div>
                </div>
                <div class="featured-card">
                    <img src="https://picsum.photos/800/400?8" alt="">
                    <div class="featured-card-overlay"></div>
                    <div class="featured-card-body">
                        <span class="featured-card-badge">Dorama</span>
                        <h5>The Loyal Pin</h5>
                        <small>2024 · Completada</small>
                    </div>
                </div>
                <div class="featured-card">
                    <img src="https://picsum.photos/800/400?9" alt="">
                    <div class="featured-card-overlay"></div>
                    <div class="live-dot"></div>
                    <div class="featured-card-body">
                        <span class="featured-card-badge">Dorama</span>
                        <h5>Play Park</h5>
                        <small>2026 · En curso</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ ÚLTIMOS EPISODIOS ══ -->
    <section class="episodes-section">
        <div class="container-xl px-4">
            <div class="section-header">
                <h2 class="section-title">Últimos episodios</h2>
                <a href="#" class="section-link">Ver todo →</a>
            </div>
            <div class="row g-3">

                <div class="col-6 col-md-3">
                    <div class="episode-card">
                        <div class="episode-thumb">
                            <img src="https://picsum.photos/400/250?11" alt="">
                            <span class="ep-badge-new">Nuevo</span>
                            <span class="ep-live"></span>
                            <div class="ep-play-btn">
                                <div class="ep-play-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="episode-info">
                            <h6>Episodio 15</h6>
                            <small>Shadow of Love</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="episode-card">
                        <div class="episode-thumb">
                            <img src="https://picsum.photos/400/250?12" alt="">
                            <span class="ep-badge-new">Nuevo</span>
                            <span class="ep-live"></span>
                            <div class="ep-play-btn">
                                <div class="ep-play-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="episode-info">
                            <h6>Episodio 6</h6>
                            <small>Scandal Love</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="episode-card">
                        <div class="episode-thumb">
                            <img src="https://picsum.photos/400/250?13" alt="">
                            <span class="ep-badge-new">Nuevo</span>
                            <span class="ep-live"></span>
                            <div class="ep-play-btn">
                                <div class="ep-play-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="episode-info">
                            <h6>Episodio 8</h6>
                            <small>My Only Sunshine</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="episode-card">
                        <div class="episode-thumb">
                            <img src="https://picsum.photos/400/250?14" alt="">
                            <span class="ep-badge-new">Nuevo</span>
                            <span class="ep-live"></span>
                            <div class="ep-play-btn">
                                <div class="ep-play-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="episode-info">
                            <h6>Episodio 6</h6>
                            <small>Girl Rules</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ══ CATÁLOGO ══ -->
    <section class="catalog-section">
        <div class="container-xl px-4">
            <div class="section-header">
                <h2 class="section-title">Series GL</h2>
                <div style="display:flex;align-items:center;gap:16px;">
                    <span style="color:var(--muted);font-size:0.85rem;">56 series</span>
                    <a href="#" class="section-link">Ver todo →</a>
                </div>
            </div>
            <div class="row g-3">

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="catalog-card">
                        <div class="catalog-poster">
                            <img src="https://picsum.photos/300/420?21" alt="">
                            <div class="catalog-poster-overlay">
                                <div class="cat-play">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="catalog-info">
                            <h6>Fruit</h6><small>Mar. 27, 2026</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="catalog-card">
                        <div class="catalog-poster">
                            <img src="https://picsum.photos/300/420?22" alt="">
                            <div class="catalog-poster-overlay">
                                <div class="cat-play"><svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg></div>
                            </div>
                        </div>
                        <div class="catalog-info">
                            <h6>The Secret of Us</h6><small>Jun. 24, 2024</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="catalog-card">
                        <div class="catalog-poster">
                            <img src="https://picsum.photos/300/420?23" alt="">
                            <div class="catalog-poster-overlay">
                                <div class="cat-play"><svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg></div>
                            </div>
                        </div>
                        <div class="catalog-info">
                            <h6>Scandal Love</h6><small>Abr. 08, 2026</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="catalog-card">
                        <div class="catalog-poster">
                            <img src="https://picsum.photos/300/420?24" alt="">
                            <div class="catalog-poster-overlay">
                                <div class="cat-play"><svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg></div>
                            </div>
                        </div>
                        <div class="catalog-info">
                            <h6>Hometown Romance</h6><small>Abr. 03, 2026</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="catalog-card">
                        <div class="catalog-poster">
                            <img src="https://picsum.photos/300/420?25" alt="">
                            <div class="catalog-poster-overlay">
                                <div class="cat-play"><svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg></div>
                            </div>
                        </div>
                        <div class="catalog-info">
                            <h6>Broken Love</h6><small>Mar. 27, 2026</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="catalog-card">
                        <div class="catalog-poster">
                            <img src="https://picsum.photos/300/420?26" alt="">
                            <div class="catalog-poster-overlay">
                                <div class="cat-play"><svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg></div>
                            </div>
                        </div>
                        <div class="catalog-info">
                            <h6>Shadow of Love</h6><small>Mar. 24, 2026</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="catalog-card">
                        <div class="catalog-poster">
                            <img src="https://picsum.photos/300/420?27" alt="">
                            <div class="catalog-poster-overlay">
                                <div class="cat-play"><svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg></div>
                            </div>
                        </div>
                        <div class="catalog-info">
                            <h6>The Water</h6><small>Mar. 21, 2026</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="catalog-card">
                        <div class="catalog-poster">
                            <img src="https://picsum.photos/300/420?28" alt="">
                            <div class="catalog-poster-overlay">
                                <div class="cat-play"><svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg></div>
                            </div>
                        </div>
                        <div class="catalog-info">
                            <h6>The Loyal Pin</h6><small>Ago. 04, 2024</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="catalog-card">
                        <div class="catalog-poster">
                            <img src="https://picsum.photos/300/420?29" alt="">
                            <div class="catalog-poster-overlay">
                                <div class="cat-play"><svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg></div>
                            </div>
                        </div>
                        <div class="catalog-info">
                            <h6>Girl From Nowhere</h6><small>Mar. 04, 2026</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="catalog-card">
                        <div class="catalog-poster">
                            <img src="https://picsum.photos/300/420?30" alt="">
                            <div class="catalog-poster-overlay">
                                <div class="cat-play"><svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg></div>
                            </div>
                        </div>
                        <div class="catalog-info">
                            <h6>Girl Rules</h6><small>Mar. 09, 2026</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="catalog-card">
                        <div class="catalog-poster">
                            <img src="https://picsum.photos/300/420?31" alt="">
                            <div class="catalog-poster-overlay">
                                <div class="cat-play"><svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg></div>
                            </div>
                        </div>
                        <div class="catalog-info">
                            <h6>My Only Sunshine</h6><small>Feb. 25, 2026</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="catalog-card">
                        <div class="catalog-poster">
                            <img src="https://picsum.photos/300/420?32" alt="">
                            <div class="catalog-poster-overlay">
                                <div class="cat-play"><svg width="16" height="16" viewBox="0 0 24 24" fill="#fff">
                                        <path d="M8 5v14l11-7z" />
                                    </svg></div>
                            </div>
                        </div>
                        <div class="catalog-info">
                            <h6>Play Park</h6><small>Feb. 20, 2026</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ══ FOOTER ══ -->
    <footer class="gl-footer">
        <div class="container-xl px-4">
            <div class="row g-4 mb-2">
                <div class="col-md-4">
                    <div class="footer-brand">Series ♥ GL</div>
                    <p class="footer-desc">Tu espacio para descubrir historias GL de todo el mundo. Series, doramas y
                        películas actualizadas cada día.</p>
                </div>
                <div class="col-6 col-md-2 offset-md-2">
                    <div class="footer-heading">Navegar</div>
                    <ul class="footer-links">
                        <li><a href="#">Inicio</a></li>
                        <li><a href="#">Series</a></li>
                        <li><a href="#">Películas</a></li>
                        <li><a href="#">Géneros</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-2">
                    <div class="footer-heading">Géneros</div>
                    <ul class="footer-links">
                        <li><a href="#">Romance</a></li>
                        <li><a href="#">Drama</a></li>
                        <li><a href="#">Comedia</a></li>
                        <li><a href="#">Thriller</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-2">
                    <div class="footer-heading">Info</div>
                    <ul class="footer-links">
                        <li><a href="#">Acerca de</a></li>
                        <li><a href="#">Contacto</a></li>
                        <li><a href="#">Privacidad</a></li>
                        <li><a href="#">Términos</a></li>
                    </ul>
                </div>
            </div>
            <hr class="footer-divider">
            <p class="footer-copy">© {{ date('Y') }} Series GL · Ningún de los derechos reservados</p>
            <p class="footer-copy">Hecho con ❤️ en México por sts19813</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 40);
        });

        // Rail scroll
        function scrollRail(dir) {
            const rail = document.getElementById('featuredRail');
            rail.scrollBy({ left: dir * 320, behavior: 'smooth' });
        }
    </script>
</body>

</html>