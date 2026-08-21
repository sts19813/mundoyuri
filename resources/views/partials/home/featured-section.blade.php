<section>
    <div class="container-xl px-4">
        <div class="section-header">
            <h2 class="section-title">{{ $title }}</h2>
            <a href="{{ $catalogUrl }}" class="section-link">Ver catálogo →</a>
        </div>
        <div class="featured-rail">
            @forelse($series as $item)
                <a href="{{ route('catalog.series.show', $item->slug) }}" class="featured-card">
                    <x-media-preview :src="$item->bannerMediaUrl() ?: 'https://picsum.photos/800/400?'.$item->id" :type="$item->bannerMediaUrl() ? $item->bannerMediaType() : 'image'" :alt="$item->title" class="featured-card-media" :hover-play="$item->bannerMediaType() === 'video'" />
                    <div class="featured-card-overlay"></div>
                    <div class="featured-card-body"><span class="featured-card-badge">{{ $label }} · Serie</span><h5>{{ $item->title }}</h5><small>{{ $item->release_year ?: 'S/F' }} · {{ $item->status === 'completed' ? 'Completada' : 'En curso' }}</small></div>
                </a>
            @empty
                <div class="featured-card"><x-media-preview src="https://picsum.photos/800/400?empty-featured-{{ $sectionId }}" type="image" alt="Sin contenido" class="featured-card-media" /><div class="featured-card-overlay"></div><div class="featured-card-body"><span class="featured-card-badge">{{ $label }}</span><h5>Próximamente</h5><small>Esta sección aún no tiene títulos publicados.</small></div></div>
            @endforelse
        </div>
    </div>
</section>
