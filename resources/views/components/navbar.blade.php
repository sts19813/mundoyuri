<!-- ══ NAVBAR (same as index) ══ -->
<nav class="gl-nav scrolled" id="navbar">
    <div class="nav-inner">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-heart"></span>
            Mundo GL
        </a>
        <ul class="nav-links" id="navLinks">
            <li><a href="{{ route('home') }}">Inicio</a></li>
            <li><a href="{{ route('catalog.series.index', ['type' => 'series']) }}">Series</a></li>
            <li><a href="{{ route('catalog.series.index', ['type' => 'movie']) }}">Peliculas</a></li>
            <li><a href="{{ route('catalog.genres.index') }}">Generos</a></li>
            @auth
                <li><a href="{{ route('submissions.create') }}">Subir contenido</a></li>
            @endauth
        </ul>
        <form action="{{ route('catalog.series.index') }}" method="GET">
            <input type="text" name="q" class="nav-search" placeholder="Buscar series..." value="{{ request('q') }}">
        </form>
        <button class="nav-toggler" id="navToggler" aria-label="Toggle menu">&#9776;</button>
    </div>
</nav>
