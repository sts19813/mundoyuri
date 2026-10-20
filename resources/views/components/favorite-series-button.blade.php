@props(['series', 'compact' => false])

@php
    $isFavorite = auth()->check()
        && auth()->user()->favoriteSeries()->whereKey($series->id)->exists();
@endphp

<div @class(['favorite-series-control', 'favorite-series-control--compact' => $compact])>
    @auth
        <form method="POST" action="{{ $isFavorite ? route('series.favorites.destroy', $series) : route('series.favorites.store', $series) }}">
            @csrf
            @if($isFavorite)
                @method('DELETE')
            @endif
            <button class="favorite-series-btn {{ $isFavorite ? 'is-favorite' : '' }}" type="submit" @if($compact) aria-label="{{ $isFavorite ? 'Quitar serie de favoritas' : 'Guardar serie en favoritas' }}" title="{{ $isFavorite ? 'Quitar de favoritas' : 'Guardar serie' }}" @endif>
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>
                </svg>
                <span>{{ $compact ? ($isFavorite ? 'Guardada' : 'Guardar serie') : ($isFavorite ? 'Quitar de favoritas' : 'Agregar a favoritas') }}</span>
            </button>
        </form>
    @else
        <a class="favorite-series-btn" href="{{ route('login', ['return' => request()->fullUrl()]) }}" @if($compact) aria-label="Inicia sesión para guardar esta serie" title="Guardar serie" @endif>
            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>
            </svg>
            <span>{{ $compact ? 'Guardar serie' : 'Inicia sesión para guardar' }}</span>
        </a>
    @endauth
</div>
