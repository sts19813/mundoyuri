<section class="catalog-section home-catalog" id="titulos" data-home-catalog>
    <div class="container-xl px-4">
        <div class="section-header home-catalog-header">
            <div>
                <h2 class="section-title">{{ $title }}</h2>
            </div>
            <div class="home-catalog-order">
                <label for="home-catalog-sort">Ordenar por</label>
                <select id="home-catalog-sort" data-home-catalog-sort>
                    <option value="premiere">Estreno</option>
                    <option value="views">Vistas</option>
                    <option value="title">Nombre A–Z</option>
                </select>
            </div>
        </div>
        <div class="row g-3">
            @forelse($series as $item)
                @php
                    $itemSectionLabel = match ($item->catalog_section) {
                        'anime' => 'Anime',
                        'series-gl' => 'Serie GL',
                        default => $label,
                    };
                    $itemTypeLabel = $item->content_type === 'movie' ? 'Película' : 'Serie';
                @endphp
                <div class="col-6 col-sm-4 col-md-3 col-lg-2" data-home-catalog-item data-title="{{ $item->title }}" data-premiere="{{ $item->first_episode_date ?: '' }}" data-views="{{ (int) ($item->total_episode_views ?? 0) }}" data-id="{{ $item->id }}">
                    <a href="{{ route('catalog.series.show', $item->slug) }}" class="catalog-card">
                        <div class="catalog-poster"><x-media-preview :src="$item->coverMediaUrl() ?: 'https://picsum.photos/300/420?series-'.$item->id" :type="$item->coverMediaUrl() ? $item->coverMediaType() : 'image'" :alt="$item->title" class="catalog-poster-media" :hover-play="$item->coverMediaType() === 'video'" /></div>
                        <div class="catalog-info"><h6>{{ $item->title }}</h6><small>{{ $itemSectionLabel }} · {{ $itemTypeLabel }}</small></div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="catalog-card p-4">Aún no hay títulos publicados de {{ $label }}.</div></div>
            @endforelse
        </div>
    </div>
</section>
