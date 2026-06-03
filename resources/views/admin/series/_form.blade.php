@csrf
@if(isset($series)) @method('PUT') @endif
<div class="card">
    <div class="card-body row g-4">
        <div class="col-md-4">
            <label class="form-label">Genero</label>
            <select class="form-select" name="genre_id" required>
                @foreach($genres as $genre)
                    <option value="{{ $genre->id }}" @selected(old('genre_id', $series->genre_id ?? '') == $genre->id)>{{ $genre->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tipo</label>
            <select class="form-select" name="content_type" required>
                <option value="series" @selected(old('content_type', $series->content_type ?? 'series') === 'series')>Serie</option>
                <option value="movie" @selected(old('content_type', $series->content_type ?? '') === 'movie')>Pelicula</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Estado</label>
            <select class="form-select" name="status" required>
                <option value="ongoing" @selected(old('status', $series->status ?? 'ongoing') === 'ongoing')>En emision</option>
                <option value="completed" @selected(old('status', $series->status ?? '') === 'completed')>Completada</option>
                <option value="upcoming" @selected(old('status', $series->status ?? '') === 'upcoming')>Proximamente</option>
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label">Titulo</label>
            <input class="form-control" name="title" value="{{ old('title', $series->title ?? '') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Slug (opcional)</label>
            <input class="form-control" name="slug" value="{{ old('slug', $series->slug ?? '') }}">
        </div>
        <div class="col-12">
            <label class="form-label">Descripcion</label>
            <textarea class="form-control" rows="4" name="description" required>{{ old('description', $series->description ?? '') }}</textarea>
        </div>
        <div class="col-md-3"><label class="form-label">Pais</label><input class="form-control" name="country_of_origin" value="{{ old('country_of_origin', $series->country_of_origin ?? '') }}"></div>
        <div class="col-md-3"><label class="form-label">Ano</label><input class="form-control" type="number" name="release_year" value="{{ old('release_year', $series->release_year ?? '') }}"></div>
        <div class="col-md-2"><label class="form-label">Temporadas</label><input class="form-control" type="number" name="total_seasons" value="{{ old('total_seasons', $series->total_seasons ?? 1) }}"></div>
        <div class="col-md-2"><label class="form-label">Episodios</label><input class="form-control" type="number" name="total_episodes" value="{{ old('total_episodes', $series->total_episodes ?? 0) }}"></div>
        <div class="col-md-2"><label class="form-label">Duracion min</label><input class="form-control" type="number" name="duration_minutes" value="{{ old('duration_minutes', $series->duration_minutes ?? '') }}"></div>
        <div class="col-md-6">
            <label class="form-label">Archivo banner</label>
            <input class="form-control" type="file" name="banner_image" accept="image/*,video/*">
            @if(isset($series) && $series->bannerMediaUrl())
                <div class="mt-3">
                    <x-media-preview :src="$series->bannerMediaUrl()" :type="$series->bannerMediaType()" :alt="$series->title" class="w-100 rounded" style="max-height: 220px; object-fit: cover;" :autoplay="true" />
                </div>
            @endif
        </div>
        <div class="col-md-6">
            <label class="form-label">Archivo portada</label>
            <input class="form-control" type="file" name="cover_image" accept="image/*,video/*">
            <div class="form-text">Se usa como carátula en portada, catálogo y el resto del sitio.</div>
            @if(isset($series) && $series->coverMediaUrl())
                <div class="mt-3" style="max-width: 220px;">
                    <x-media-preview :src="$series->coverMediaUrl()" :type="$series->coverMediaType()" :alt="$series->title" class="w-100 rounded" style="aspect-ratio: 3 / 4; object-fit: cover;" :autoplay="true" />
                </div>
            @endif
        </div>
        <div class="col-md-6"><label class="form-label">Trailer URL</label><input class="form-control" type="url" name="trailer_url" value="{{ old('trailer_url', $series->trailer_url ?? '') }}"></div>
        <div class="col-md-3">
            <label class="form-label">Moderacion</label>
            <select class="form-select" name="moderation_status" required>
                <option value="pending" @selected(old('moderation_status', $series->moderation_status ?? 'pending') === 'pending')>Pendiente</option>
                <option value="approved" @selected(old('moderation_status', $series->moderation_status ?? '') === 'approved')>Aprobado</option>
                <option value="rejected" @selected(old('moderation_status', $series->moderation_status ?? '') === 'rejected')>Rechazado</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <label class="form-check form-check-custom form-check-solid">
                <input class="form-check-input" type="checkbox" name="is_featured" value="1" {{ old('is_featured', $series->is_featured ?? false) ? 'checked' : '' }}>
                <span class="form-check-label">Destacada</span>
            </label>
        </div>
        <div class="col-12">
            <label class="form-label">Notas de moderacion</label>
            <textarea class="form-control" rows="3" name="moderation_notes">{{ old('moderation_notes', $series->moderation_notes ?? '') }}</textarea>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('admin.series.index') }}" class="btn btn-light">Cancelar</a>
        <button class="btn btn-primary" type="submit">Guardar</button>
    </div>
</div>
