<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-seo
        title="Catálogo de series y películas GL"
        description="Encuentra series, películas y doramas Girls' Love por título, tipo o género. Explora todo el catálogo de Mundo Yuri."
        :canonical="route('catalog.series.index')"
    />
    <x-portal-favicon />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
<x-navbar />

<section class="catalog-section mt-5">
    <div class="container-xl px-4">
        <div class="catalog-heading">
            <div>
                <p class="catalog-eyebrow">Explora Mundo Yuri</p>
                <h1 class="section-title">Catálogo</h1>
            </div>
            <p class="catalog-results" id="catalogResults" aria-live="polite"></p>
        </div>

        <div class="catalog-filters" data-catalog-filters>
            <div class="catalog-filter catalog-filter-search">
                <label class="visually-hidden" for="catalogSearch">Buscar en el catálogo</label>
                <svg class="catalog-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input id="catalogSearch" type="search" value="{{ request('q') }}" placeholder="Buscar título o descripción" autocomplete="off">
            </div>
            <div class="catalog-filter">
                <label class="visually-hidden" for="catalogType">Tipo de contenido</label>
                <select id="catalogType">
                    <option value="">Todos los tipos</option>
                    <option value="series" @selected(request('type') === 'series')>Series</option>
                    <option value="movie" @selected(request('type') === 'movie')>Películas</option>
                </select>
            </div>
            <div class="catalog-filter">
                <label class="visually-hidden" for="catalogGenre">Género</label>
                <select id="catalogGenre">
                    <option value="">Todos los géneros</option>
                    @foreach($genres as $genre)
                        <option value="{{ $genre->slug }}" @selected(request('genre') === $genre->slug)>{{ $genre->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row g-3" id="catalogGrid">
            @forelse($series as $item)
                <div
                    class="col-6 col-sm-4 col-md-3 col-lg-2"
                    data-catalog-item
                    data-type="{{ $item->content_type }}"
                    data-genre="{{ $item->genre?->slug }}"
                    data-search="{{ $item->title }} {{ $item->description }} {{ $item->genre?->name }}"
                >
                    <a href="{{ route('catalog.series.show', $item->slug) }}" class="catalog-card">
                        <div class="catalog-card h-100">
                            <div class="catalog-poster">
                                <x-media-preview
                                    :src="$item->coverMediaUrl() ?: 'https://picsum.photos/300/420?series='.$item->id"
                                    :type="$item->coverMediaUrl() ? $item->coverMediaType() : 'image'"
                                    :alt="$item->title"
                                    class="catalog-poster-media"
                                    :hover-play="$item->coverMediaType() === 'video'"
                                />
                            </div>
                            <div class="catalog-info">
                                <h6>{{ $item->title }}</h6>
                                <small>{{ $item->genre?->name ?? 'Sin género' }} · {{ $item->content_type === 'series' ? 'Serie' : 'Película' }}</small>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
            @endforelse
        </div>

        <div class="catalog-empty" id="catalogEmpty" @if($series->isNotEmpty()) hidden @endif>
            <span aria-hidden="true">✦</span>
            <p>No encontramos títulos con estos filtros.</p>
            <small>Prueba otra búsqueda o cambia el tipo y género.</small>
        </div>
    </div>
</section>

<x-footer />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@include('partials.hover-media-script')
<script>
    (() => {
        const filters = document.querySelector('[data-catalog-filters]');
        if (!filters) return;

        const search = document.getElementById('catalogSearch');
        const type = document.getElementById('catalogType');
        const genre = document.getElementById('catalogGenre');
        const items = [...document.querySelectorAll('[data-catalog-item]')];
        const results = document.getElementById('catalogResults');
        const empty = document.getElementById('catalogEmpty');
        const normalise = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();

        function updateUrl() {
            const url = new URL(window.location.href);
            const values = { q: search.value.trim(), type: type.value, genre: genre.value };

            Object.entries(values).forEach(([key, value]) => {
                value ? url.searchParams.set(key, value) : url.searchParams.delete(key);
            });
            url.searchParams.delete('page');
            window.history.replaceState({}, '', url);
        }

        function filterCatalogue() {
            const query = normalise(search.value);
            let matches = 0;

            items.forEach((item) => {
                const isMatch = (!query || normalise(item.dataset.search || '').includes(query))
                    && (!type.value || item.dataset.type === type.value)
                    && (!genre.value || item.dataset.genre === genre.value);

                item.hidden = !isMatch;
                if (isMatch) matches++;
            });

            results.textContent = `${matches} ${matches === 1 ? 'título' : 'títulos'}`;
            empty.hidden = matches !== 0;
            updateUrl();
        }

        search.addEventListener('input', filterCatalogue);
        type.addEventListener('change', filterCatalogue);
        genre.addEventListener('change', filterCatalogue);
        filterCatalogue();
    })();
</script>
</body>
</html>
