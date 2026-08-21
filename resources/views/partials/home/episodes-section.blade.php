<section class="episodes-section" id="{{ $sectionId }}">
    <div class="container-xl px-4">
        <div class="section-header">
            <h2 class="section-title">{{ $title }}</h2>
            <a href="{{ $catalogUrl }}" class="section-link">Ver todo →</a>
        </div>
        <div class="row g-3">
            @forelse($episodes->take(8) as $episode)
                <div class="col-6 col-md-3">
                    <a href="{{ route('public.episodes.show', $episode->slug) }}" class="episode-card">
                        <div class="episode-thumb">
                            <x-media-preview :src="$episode->series?->bannerMediaUrl() ?: $episode->previewMediaUrl('640/360')" :type="$episode->series?->bannerMediaUrl() ? $episode->series->bannerMediaType() : $episode->previewMediaType()" :alt="$episode->title" class="episode-thumb-media" />
                            <span class="ep-live"></span>
                            <div class="ep-play-btn"><div class="ep-play-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><path d="M8 5v14l11-7z" /></svg></div></div>
                        </div>
                        <div class="episode-info"><h6>Episodio {{ $episode->episode_number }}</h6><small>{{ $episode->series->title ?? 'Serie desconocida' }}</small></div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="episode-card p-4">Aún no hay episodios publicados de {{ $label }}.</div></div>
            @endforelse
        </div>
    </div>
</section>
