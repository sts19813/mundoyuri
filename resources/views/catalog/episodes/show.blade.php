<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $series->title }} · {{ $episode->title }} · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
<x-navbar />

@php
    $partSources = $episode->sources->where('source_type', 'part')->sortBy('sort_order')->values();
    $fullSources = $episode->sources->where('source_type', '!=', 'part')->values();
    $primary = $partSources->firstWhere('is_primary', true) ?: $partSources->first() ?: $fullSources->firstWhere('is_primary', true) ?: $fullSources->first();
@endphp

<section class="py-5 mt-5">
    <div class="container-xl px-4">
        <div class="mb-3 text-muted">
            <a href="{{ route('home') }}">Inicio</a> / <a href="{{ route('catalog.series.show', $series->slug) }}">{{ $series->title }}</a> / Episodio {{ $episode->episode_number }}
        </div>

        <h1 class="section-title">{{ $series->title }} · S{{ $episode->season_number }}E{{ $episode->episode_number }} · {{ $episode->title }}</h1>
        <div class="d-flex align-items-center gap-1 text-muted small mb-2" title="Vistas">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                <circle cx="12" cy="12" r="3" />
            </svg>
            <span>{{ number_format($episode->views_count) }} {{ $episode->views_count === 1 ? 'vista' : 'vistas' }}</span>
        </div>
        <p class="text-muted">{{ $episode->description ?: 'Sin descripcion disponible.' }}</p>

        <div class="card mb-4">
            <div class="card-body">
                @if($primary)
                    <div class="ratio ratio-16x9 mb-3" id="catalogEpisodeFrameWrap" @if($primary->player_type !== 'iframe') style="display:none;" @endif>
                        <iframe id="catalogEpisodePlayer" src="{{ $primary->player_type === 'iframe' ? $primary->playable_url : '' }}" title="Video" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                    </div>
                    <div class="mb-3" id="catalogEpisodeVideoWrap" @if($primary->player_type !== 'video') style="display:none;" @endif>
                        <video id="catalogEpisodeVideo" class="w-100 rounded" controls playsinline preload="metadata" style="aspect-ratio: 16 / 9; background: #000;">
                            <source src="{{ $primary->player_type === 'video' ? $primary->playable_url : '' }}">
                        </video>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">Este episodio aun no tiene fuente de video disponible.</div>
                @endif

                @if($partSources->isNotEmpty())
                    <h5 class="mt-3">Partes del episodio</h5>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($partSources as $source)
                            <button type="button" class="btn {{ $source->is_primary || ($loop->first && !$partSources->contains(fn($item) => $item->is_primary)) ? 'btn-primary' : 'btn-light' }} catalog-source-switcher" data-video-url="{{ $source->playable_url }}" data-player-type="{{ $source->player_type }}">
                                {{ $source->label ?: 'Parte '.($source->sort_order ?: $loop->iteration) }}
                            </button>
                        @endforeach
                    </div>
                @endif

                <h5 class="mt-3">Fuentes disponibles</h5>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($fullSources as $source)
                        <button type="button" class="btn {{ $source->is_primary ? 'btn-primary' : 'btn-light' }} catalog-source-switcher" data-video-url="{{ $source->playable_url }}" data-player-type="{{ $source->player_type }}">
                            {{ strtoupper($source->provider) }}{{ $source->label ? ' · '.$source->label : '' }}
                        </button>
                    @empty
                        <span class="text-muted">{{ $partSources->isNotEmpty() ? 'Este episodio se reproduce por partes.' : 'Sin fuentes registradas.' }}</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="mb-3">Lista de episodios</h4>
                <div class="list-group">
                    @foreach($episodes as $item)
                        <a href="{{ route('catalog.episodes.show', [$series->slug, $item->slug]) }}" class="list-group-item list-group-item-action {{ $item->id === $episode->id ? 'active' : '' }}">
                            S{{ $item->season_number }}E{{ $item->episode_number }} · {{ $item->title }}
                            <small class="d-block {{ $item->id === $episode->id ? 'text-white-50' : 'text-muted' }}">{{ optional($item->release_date)->format('d/m/Y') ?: 'Sin fecha' }}</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        @include('catalog.partials.comments', ['comments' => $episode->comments, 'targetType' => 'episode', 'targetId' => $episode->id])
    </div>
</section>

<x-footer />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const catalogFrameWrap = document.getElementById('catalogEpisodeFrameWrap');
const catalogFrame = document.getElementById('catalogEpisodePlayer');
const catalogVideoWrap = document.getElementById('catalogEpisodeVideoWrap');
const catalogVideo = document.getElementById('catalogEpisodeVideo');

function switchCatalogPlayer(type, url) {
    if (!url) {
        return;
    }

    if (type === 'video') {
        if (catalogFrame) {
            catalogFrame.src = '';
        }
        if (catalogFrameWrap) {
            catalogFrameWrap.style.display = 'none';
        }
        if (catalogVideo) {
            catalogVideo.pause();
            catalogVideo.src = url;
            catalogVideo.load();
        }
        if (catalogVideoWrap) {
            catalogVideoWrap.style.display = '';
        }

        return;
    }

    if (catalogVideo) {
        catalogVideo.pause();
        catalogVideo.removeAttribute('src');
        catalogVideo.load();
    }
    if (catalogVideoWrap) {
        catalogVideoWrap.style.display = 'none';
    }
    if (catalogFrame) {
        catalogFrame.src = url;
    }
    if (catalogFrameWrap) {
        catalogFrameWrap.style.display = '';
    }
}

document.querySelectorAll('.catalog-source-switcher').forEach((button) => {
    button.addEventListener('click', () => {
        const nextUrl = button.getAttribute('data-video-url');
        const playerType = button.getAttribute('data-player-type') || 'iframe';

        switchCatalogPlayer(playerType, nextUrl);

        document.querySelectorAll('.catalog-source-switcher').forEach((item) => {
            item.classList.remove('btn-primary');
            item.classList.add('btn-light');
        });
        button.classList.remove('btn-light');
        button.classList.add('btn-primary');
    });
});
</script>
</body>
</html>
