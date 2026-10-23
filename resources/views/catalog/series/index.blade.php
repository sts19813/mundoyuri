@extends('layouts.portal')

@section('head')

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-seo
        :title="$activeSection ? 'Series '.$activeSection->name.' y Yuri subtituladas en español' : 'Series GL, Yuri y Anime Gratis Sub Español'"
        :description="$activeSection ? 'Explora '.$activeSection->name.' con episodios y fichas en español en Mundo Yuri.' : 'Explora series GL, yuri, doramas y anime gratis con subtítulos en español. Encuentra episodios, estrenos y tus historias Girls Love favoritas.'"
        :canonical="route('catalog.series.index')"
    />
    <x-portal-favicon />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">


@endsection

@section('body')

<x-navbar />

<section class="catalog-section mt-5">
    <div class="container-xl px-4">
        <div class="catalog-heading">
            <div>
                <p class="catalog-eyebrow">{{ $activeSection ? 'Explora '.$activeSection->name : 'Series GL, Yuri y Anime' }}</p>
                <h1 class="section-title">{{ $activeSection ? 'Catálogo '.$activeSection->name : 'Series GL y Yuri subtituladas en español' }}</h1>
            </div>
            <p class="catalog-results" id="catalogResults" aria-live="polite"></p>
        </div>

        <div class="catalog-filters" data-catalog-filters>
            <div class="catalog-filter catalog-filter-search">
                <label class="visually-hidden" for="catalogSearch">Buscar en el catálogo</label>
                <svg class="catalog-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input id="catalogSearch" type="search" value="{{ request('q') }}" placeholder="Título, actriz, dupla o etiqueta" list="catalogSuggestions" autocomplete="off">
                <datalist id="catalogSuggestions">
                    @foreach($series as $item)<option value="{{ $item->title }}">Título</option>@endforeach
                    @foreach($tags as $tag)<option value="{{ $tag->name }}">Etiqueta</option>@endforeach
                    @foreach($artists as $artist)<option value="{{ $artist->name }}">Artista</option>@endforeach
                    @foreach($duos as $duo)<option value="{{ $duo->name }}">Dupla</option>@endforeach
                </datalist>
            </div>
            <div class="catalog-filter-shell">
                <div class="catalog-filter-toolbar">
                    <button class="catalog-filter-toggle" id="catalogFiltersToggle" type="button" aria-expanded="false" aria-controls="catalogFiltersPanel">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                        <span>Filtros</span>
                        <span class="catalog-filter-count" id="catalogFilterCount" hidden></span>
                        <svg class="catalog-filter-chevron" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <button class="catalog-clear-filters" id="catalogClearFilters" type="button" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        <span>Limpiar filtros</span>
                    </button>
                </div>
                <div class="catalog-filter-panel" id="catalogFiltersPanel" hidden>
                    <div class="catalog-advanced-grid">
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
                    <div class="catalog-filter">
                        <label class="visually-hidden" for="catalogTag">Etiqueta</label>
                        <select id="catalogTag">
                            <option value="">Todas las etiquetas</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->slug }}" @selected(request('tag') === $tag->slug)>{{ $tag->name }} ({{ $tag->series_count }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="catalog-filter">
                        <label class="visually-hidden" for="catalogCountry">País</label>
                        <select id="catalogCountry">
                            <option value="">Todos los países</option>
                            @foreach($countries as $country)<option value="{{ $country }}" @selected(request('country') === $country)>{{ $country }}</option>@endforeach
                        </select>
                    </div>
                    <div class="catalog-filter">
                        <label class="visually-hidden" for="catalogYear">Año</label>
                        <select id="catalogYear">
                            <option value="">Todos los años</option>
                            @foreach($years as $year)<option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>@endforeach
                        </select>
                    </div>
                    <div class="catalog-filter">
                        <label class="visually-hidden" for="catalogStatus">Estado</label>
                        <select id="catalogStatus">
                            <option value="">Todos los estados</option>
                            <option value="ongoing" @selected(request('status') === 'ongoing')>En emisión</option>
                            <option value="completed" @selected(request('status') === 'completed')>Completada</option>
                            <option value="upcoming" @selected(request('status') === 'upcoming')>Próximamente</option>
                        </select>
                    </div>
                    @if($artists->isNotEmpty())
                        <div class="catalog-filter">
                            <label class="visually-hidden" for="catalogArtist">Artista</label>
                            <select id="catalogArtist">
                                <option value="">Todas las artistas</option>
                                @foreach($artists as $artist)<option value="{{ $artist->slug }}" @selected(request('artist') === $artist->slug)>{{ $artist->name }}</option>@endforeach
                            </select>
                        </div>
                    @endif
                    @if($duos->isNotEmpty())
                        <div class="catalog-filter">
                            <label class="visually-hidden" for="catalogDuo">Dupla</label>
                            <select id="catalogDuo">
                                <option value="">Todas las duplas</option>
                                @foreach($duos as $duo)<option value="{{ $duo->slug }}" @selected(request('duo') === $duo->slug)>{{ $duo->name }}</option>@endforeach
                            </select>
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3" id="catalogGrid">
            @forelse($series as $item)
                <div
                    class="col-6 col-sm-4 col-md-3 col-lg-2"
                    data-catalog-item
                    data-type="{{ $item->content_type }}"
                    data-genre="{{ $item->genre?->slug }}"
                    data-tags="{{ $item->tags->pluck('slug')->join('|') }}"
                    data-country="{{ $item->country_of_origin }}"
                    data-year="{{ $item->release_year }}"
                    data-status="{{ $item->status }}"
                    data-artists="{{ $item->artists->pluck('slug')->join('|') }}"
                    data-duos="{{ $item->duos->pluck('slug')->join('|') }}"
                    data-search="{{ $item->title }} {{ $item->description }} {{ $item->genre?->name }} {{ $item->tags->pluck('name')->join(' ') }} {{ $item->artists->pluck('name')->join(' ') }} {{ $item->artists->pluck('aliases')->filter()->join(' ') }} {{ $item->duos->pluck('name')->join(' ') }}"
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
                                @php($itemSection = $catalogSections->firstWhere('slug', $item->catalog_section))
                                <small>{{ $itemSection?->label ?: $itemSection?->name ?: $item->catalog_section }} · {{ $item->content_type === 'series' ? 'Serie' : 'Película' }}</small>
                                @if($item->tags->isNotEmpty())
                                    <div class="catalog-card-tags" aria-label="Etiquetas">
                                        @foreach($item->tags->take(2) as $tag)<span>{{ $tag->name }}</span>@endforeach
                                    </div>
                                @endif
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
        const tag = document.getElementById('catalogTag');
        const country = document.getElementById('catalogCountry');
        const year = document.getElementById('catalogYear');
        const status = document.getElementById('catalogStatus');
        const artist = document.getElementById('catalogArtist');
        const duo = document.getElementById('catalogDuo');
        const clear = document.getElementById('catalogClearFilters');
        const panel = document.getElementById('catalogFiltersPanel');
        const toggle = document.getElementById('catalogFiltersToggle');
        const activeCount = document.getElementById('catalogFilterCount');
        const items = [...document.querySelectorAll('[data-catalog-item]')];
        const results = document.getElementById('catalogResults');
        const empty = document.getElementById('catalogEmpty');
        const normalise = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();

        function updateUrl() {
            const url = new URL(window.location.href);
            const values = {
                q: search.value.trim(), type: type.value, genre: genre.value,
                tag: tag?.value || '', country: country?.value || '', year: year?.value || '',
                status: status?.value || '', artist: artist?.value || '', duo: duo?.value || '',
            };

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
                    && (!genre.value || item.dataset.genre === genre.value)
                    && (!tag?.value || item.dataset.tags.split('|').includes(tag.value))
                    && (!country?.value || item.dataset.country === country.value)
                    && (!year?.value || item.dataset.year === year.value)
                    && (!status?.value || item.dataset.status === status.value)
                    && (!artist?.value || item.dataset.artists.split('|').includes(artist.value))
                    && (!duo?.value || item.dataset.duos.split('|').includes(duo.value));

                item.hidden = !isMatch;
                if (isMatch) matches++;
            });

            results.textContent = `${matches} ${matches === 1 ? 'título' : 'títulos'}`;
            empty.hidden = matches !== 0;
            const activeFilters = [search, type, genre, tag, country, year, status, artist, duo]
                .filter(Boolean)
                .filter((filter) => filter.value);
            clear.disabled = activeFilters.length === 0;
            activeCount.textContent = activeFilters.length;
            activeCount.hidden = activeFilters.length === 0;
            updateUrl();
        }

        function setFiltersOpen(isOpen) {
            panel.hidden = !isOpen;
            toggle.setAttribute('aria-expanded', String(isOpen));
        }

        setFiltersOpen(window.matchMedia('(max-width: 768px)').matches);

        search.addEventListener('input', filterCatalogue);
        type.addEventListener('change', filterCatalogue);
        genre.addEventListener('change', filterCatalogue);
        [tag, country, year, status, artist, duo].filter(Boolean).forEach((filter) => filter.addEventListener('change', filterCatalogue));
        toggle.addEventListener('click', () => setFiltersOpen(toggle.getAttribute('aria-expanded') !== 'true'));
        clear.addEventListener('click', () => {
            [search, type, genre, tag, country, year, status, artist, duo]
                .filter(Boolean)
                .forEach((filter) => { filter.value = ''; });
            filterCatalogue();
        });
        filterCatalogue();
    })();
</script>
@endsection
