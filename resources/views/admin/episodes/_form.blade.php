@csrf
@if(isset($episode)) @method('PUT') @endif
@php
    $sources = old('source_provider')
        ? collect(old('source_provider'))->map(function ($provider, $idx) {
            return [
                'provider' => $provider,
                'video_url' => old('source_url')[$idx] ?? '',
                'label' => old('source_label')[$idx] ?? '',
            ];
        })->values()->all()
        : (isset($episode) ? $episode->sources->map(fn($s) => ['provider' => $s->provider, 'video_url' => $s->video_url, 'label' => $s->label])->values()->all() : []);

    if (count($sources) < 3) {
        for ($i = count($sources); $i < 3; $i++) {
            $sources[] = ['provider' => '', 'video_url' => '', 'label' => ''];
        }
    }

    $primaryIndex = old('source_primary', isset($episode) ? ($episode->sources->search(fn($s) => $s->is_primary) ?: 0) : 0);
@endphp
<div class="card">
    <div class="card-body row g-4">
        <div class="col-md-6">
            <label class="form-label">Serie</label>
            <select class="form-select" name="series_id" required>
                @foreach($seriesOptions as $option)
                    <option value="{{ $option->id }}" @selected(old('series_id', $episode->series_id ?? '') == $option->id)>{{ $option->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Titulo</label>
            <input class="form-control" name="title" value="{{ old('title', $episode->title ?? '') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Temporada</label>
            <input class="form-control" type="number" name="season_number" value="{{ old('season_number', $episode->season_number ?? 1) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Numero de episodio</label>
            <input class="form-control" type="number" name="episode_number" value="{{ old('episode_number', $episode->episode_number ?? 1) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Slug (opcional)</label>
            <input class="form-control" name="slug" value="{{ old('slug', $episode->slug ?? '') }}">
        </div>
        <div class="col-md-4"><label class="form-label">Fecha publicacion</label><input class="form-control" type="date" name="release_date" value="{{ old('release_date', isset($episode->release_date) ? $episode->release_date->format('Y-m-d') : '') }}"></div>
        <div class="col-md-4"><label class="form-label">Duracion min</label><input class="form-control" type="number" name="duration_minutes" value="{{ old('duration_minutes', $episode->duration_minutes ?? '') }}"></div>
        <div class="col-md-4"><label class="form-label">Thumbnail URL</label><input class="form-control" type="url" name="thumbnail_image" value="{{ old('thumbnail_image', $episode->thumbnail_image ?? '') }}"></div>
        <div class="col-12"><label class="form-label">Descripcion</label><textarea class="form-control" rows="4" name="description">{{ old('description', $episode->description ?? '') }}</textarea></div>
        <div class="col-md-6">
            <label class="form-label">Moderacion</label>
            <select class="form-select" name="moderation_status" required>
                <option value="pending" @selected(old('moderation_status', $episode->moderation_status ?? 'pending') === 'pending')>Pendiente</option>
                <option value="approved" @selected(old('moderation_status', $episode->moderation_status ?? '') === 'approved')>Aprobado</option>
                <option value="rejected" @selected(old('moderation_status', $episode->moderation_status ?? '') === 'rejected')>Rechazado</option>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">Notas de moderacion</label><input class="form-control" name="moderation_notes" value="{{ old('moderation_notes', $episode->moderation_notes ?? '') }}"></div>

        <div class="col-12"><hr><h5>Fuentes de video</h5></div>
        @foreach($sources as $index => $source)
            <div class="col-md-3">
                <label class="form-label">Proveedor {{ $index + 1 }}</label>
                <select class="form-select" name="source_provider[]">
                    <option value="">Selecciona</option>
                    <option value="youtube" @selected($source['provider'] === 'youtube')>YouTube</option>
                    <option value="vimeo" @selected($source['provider'] === 'vimeo')>Vimeo</option>
                    <option value="byse" @selected($source['provider'] === 'byse')>BYSE</option>
                    <option value="voe" @selected($source['provider'] === 'voe')>VOE</option>
                    <option value="ok" @selected($source['provider'] === 'ok')>OK</option>
                    <option value="netu" @selected($source['provider'] === 'netu')>NETU</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">URL {{ $index + 1 }}</label>
                <input class="form-control" type="url" name="source_url[]" value="{{ $source['video_url'] }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Etiqueta {{ $index + 1 }}</label>
                <input class="form-control" name="source_label[]" value="{{ $source['label'] }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <label class="form-check form-check-custom form-check-solid">
                    <input class="form-check-input" type="radio" name="source_primary" value="{{ $index }}" {{ (int)$primaryIndex === $index ? 'checked' : '' }}>
                </label>
            </div>
        @endforeach
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('admin.episodes.index') }}" class="btn btn-light">Cancelar</a>
        <button class="btn btn-primary" type="submit">Guardar</button>
    </div>
</div>
