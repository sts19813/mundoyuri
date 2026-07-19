<!-- ══ FOOTER ══ -->
<footer class="gl-footer">
    <div class="container-xl px-4">
        <div class="row g-4 mb-2">
            <div class="col-md-4">
                <a href="{{ route('home') }}" class="brand">
                    <span class="brand-heart"></span>
                    Mundo Yuri
                </a>
                <p class="footer-desc">Tu espacio para descubrir historias GL de todo el mundo. Series, doramas y
                    películas actualizadas cada día.</p>
            </div>
            <div class="col-6 col-md-2 offset-md-2">
                <div class="footer-heading">Navegar</div>
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}">Inicio</a></li>
                    <li><a href="{{ route('catalog.series.index', ['type' => 'series']) }}">Series</a></li>
                    <li><a href="{{ route('catalog.series.index', ['type' => 'movie']) }}">Peliculas</a></li>
                    <li><a href="{{ route('catalog.genres.index') }}">Generos</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-2">
                <div class="footer-heading">Géneros</div>
                <ul class="footer-links">
                    @php
                        $footerGenres = \Illuminate\Support\Facades\Schema::hasTable('genres')
                            ? \App\Models\Genre::query()->where('is_active', true)->orderBy('name')->take(4)->get()
                            : collect();
                    @endphp

                    @forelse($footerGenres as $footerGenre)
                        <li><a href="{{ route('catalog.genres.show', $footerGenre->slug) }}">{{ $footerGenre->name }}</a></li>
                    @empty
                        <li><a href="{{ route('catalog.genres.index') }}">Romance</a></li>
                        <li><a href="{{ route('catalog.genres.index') }}">Drama</a></li>
                        <li><a href="{{ route('catalog.genres.index') }}">Comedia</a></li>
                        <li><a href="{{ route('catalog.genres.index') }}">Accion</a></li>
                    @endforelse
                </ul>
            </div>
            <div class="col-6 col-md-2">
                <div class="footer-heading">Info</div>
                <ul class="footer-links">
                    <li><a href="{{ route('about') }}">Quiénes somos</a></li>
                    <li><a href="{{ route('catalog.series.index') }}">Catalogo</a></li>
                    @auth
                        <li><a href="{{ route('submissions.create') }}">Aportar contenido</a></li>
                    @else
                        <li><a href="{{ route('register') }}">Crear cuenta</a></li>
                    @endauth
                    <li><a href="{{ route('login') }}">Acceso</a></li>
                    @if(auth()->user()?->shouldEnterAdminPanel())
                        <li><a href="{{ route('admin.dashboard') }}">Panel admin</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <hr class="footer-divider">
        <p class="footer-copy">© {{ date('Y') }} Mundo Yuri · Ninguno de los derechos reservados</p>
        <p class="footer-copy">Hecho con ❤️ por
            <a href="https://github.com/sts19813" target="_blank" rel="noopener noreferrer">
                sts19813
            </a>
        </p>
    </div>
</footer>
