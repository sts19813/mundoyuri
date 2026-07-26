@props(['series'])

@php
    $isFavorite = auth()->check()
        && auth()->user()->favoriteSeries()->whereKey($series->id)->exists();
@endphp

<div class="favorite-series-control">
    @auth
        <form method="POST" action="{{ $isFavorite ? route('series.favorites.destroy', $series) : route('series.favorites.store', $series) }}">
            @csrf
            @if($isFavorite)
                @method('DELETE')
            @endif
            <button class="favorite-series-btn {{ $isFavorite ? 'is-favorite' : '' }}" type="submit">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>
                </svg>
                {{ $isFavorite ? 'Quitar de favoritas' : 'Agregar a favoritas' }}
            </button>
        </form>
    @else
        <a class="favorite-series-btn" href="{{ route('login') }}">
            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>
            </svg>
            Inicia sesión para guardar
        </a>
    @endauth
</div>
