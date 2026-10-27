<!-- ══ FOOTER ══ -->
<footer class="gl-footer">
    <div class="gl-footer__inner">
        <a href="{{ route('home') }}" class="gl-footer__brand" aria-label="Ir al inicio de Mundo Yuri">
            <x-season-logo />
        </a>

        <nav class="gl-footer__nav" aria-label="Explorar el catálogo">
            <h2>Explora</h2>
            <a href="{{ route('catalog.series.index', ['section' => 'series-gl']) }}">Series GL</a>
            <a href="{{ route('catalog.series.index', ['section' => 'anime']) }}">Anime yuri</a>
            <a href="{{ route('catalog.series.index', ['type' => 'movie']) }}">Películas</a>
            <a href="{{ route('legacy.episodios') }}">Últimos episodios</a>
        </nav>

        <nav class="gl-footer__nav" aria-label="Comunidad">
            <h2>Comunidad</h2>
            <a href="{{ route('community.index') }}">Comunidad</a>
            <a href="{{ route('forums.index') }}">Foros</a>
            <a href="{{ route('questions.index') }}">Preguntas</a>
            <a href="{{ route('catalog.series.index') }}">Catálogo completo</a>
        </nav>
    </div>

    <div class="gl-footer__bottom">
        <div>
            <span>© {{ now()->year }} Mundo Yuri</span>
            <span aria-hidden="true">·</span>
            <span>Historias GL, yuri y anime</span>
        </div>
    </div>
</footer>

<x-neko-assistant />
