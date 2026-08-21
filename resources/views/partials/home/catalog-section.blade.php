<section class="catalog-section">
    <div class="container-xl px-4">
        <div class="section-header">
            <h2 class="section-title">{{ $title }}</h2>
            <a href="{{ $catalogUrl }}" class="section-link">Ver todo →</a>
        </div>
        <div class="row g-3">
            @forelse($series as $item)
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="{{ route('catalog.series.show', $item->slug) }}" class="catalog-card">
                        <div class="catalog-poster"><x-media-preview :src="$item->coverMediaUrl() ?: 'https://picsum.photos/300/420?series-'.$item->id" :type="$item->coverMediaUrl() ? $item->coverMediaType() : 'image'" :alt="$item->title" class="catalog-poster-media" :hover-play="$item->coverMediaType() === 'video'" /></div>
                        <div class="catalog-info"><h6>{{ $item->title }}</h6><small>{{ $label }} · Serie</small></div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="catalog-card p-4">Aún no hay series publicadas de {{ $label }}.</div></div>
            @endforelse
        </div>
    </div>
</section>
