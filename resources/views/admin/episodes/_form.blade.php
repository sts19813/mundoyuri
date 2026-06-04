@csrf
@if(isset($episode)) @method('PUT') @endif
@php
    $sources = old('source_provider')
        ? collect(old('source_provider'))->map(function ($provider, $idx) {
            return [
                'provider' => $provider,
                'source_type' => old('source_type')[$idx] ?? 'full',
                'video_url' => old('source_url')[$idx] ?? '',
                'label' => old('source_label')[$idx] ?? '',
                'sort_order' => old('source_sort_order')[$idx] ?? ($idx + 1),
            ];
        })->values()->all()
        : (isset($episode)
            ? $episode->sources->map(fn($s) => [
                'provider' => $s->provider === 'youtube' ? 'youtube_link' : $s->provider,
                'source_type' => $s->source_type ?? 'full',
                'video_url' => $s->video_url,
                'label' => $s->label,
                'sort_order' => $s->sort_order ?: 1,
            ])->values()->all()
            : []);

    if (count($sources) === 0) {
        $sources[] = ['provider' => '', 'video_url' => '', 'label' => ''];
    }

    $primaryIndex = (int) old('source_primary', isset($episode) ? ($episode->sources->search(fn($s) => $s->is_primary) ?: 0) : 0);
@endphp
<div class="card">
    <div class="card-body row g-4">
        @if($errors->any())
            <div class="col-12">
                <div class="alert alert-danger d-flex align-items-center p-5 mb-0">
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-danger">Revisa los datos del episodio</h4>
                        <span>{{ $errors->first() }}</span>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-md-6">
            <label class="form-label">Serie</label>
            <select class="form-select @error('series_id') is-invalid @enderror" name="series_id" required>
                @foreach($seriesOptions as $option)
                    <option value="{{ $option->id }}" @selected(old('series_id', $episode->series_id ?? '') == $option->id)>{{ $option->title }}</option>
                @endforeach
            </select>
            @error('series_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Titulo</label>
            <input class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $episode->title ?? '') }}" required>
            @error('title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Temporada</label>
            <input class="form-control @error('season_number') is-invalid @enderror" type="number" name="season_number" value="{{ old('season_number', $episode->season_number ?? 1) }}" required>
            @error('season_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Numero de episodio</label>
            <input class="form-control @error('episode_number') is-invalid @enderror" type="number" name="episode_number" value="{{ old('episode_number', $episode->episode_number ?? 1) }}" required>
            @error('episode_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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

        <div class="col-12">
            <hr>
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <h5 class="mb-0">Fuentes de video</h5>
                <button class="btn btn-light-primary" type="button" id="add-source-row">Agregar fuente</button>
            </div>
            <p class="text-muted fs-7 mt-2 mb-0">Puedes agregar una o muchas fuentes. Para nuevos proveedores, solo hace falta registrarlos en la configuración.</p>
        </div>
        <div class="col-12">
            <div id="episode-sources-list" class="d-flex flex-column gap-4">
                @foreach($sources as $index => $source)
                    <div class="border rounded p-4 source-row" data-source-row>
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label class="form-label source-provider-label">Proveedor {{ $index + 1 }}</label>
                                <select class="form-select" name="source_provider[]">
                                    <option value="">Selecciona</option>
                                    @foreach($sourceProviders as $providerKey => $providerConfig)
                                        <option value="{{ $providerKey }}" @selected($source['provider'] === $providerKey || (($source['provider'] === 'youtube') && ($providerKey === 'youtube_link')))>{{ $providerConfig['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label source-type-label">Modo {{ $index + 1 }}</label>
                                <select class="form-select" name="source_type[]">
                                    <option value="full" @selected(($source['source_type'] ?? 'full') === 'full')>Completo</option>
                                    <option value="part" @selected(($source['source_type'] ?? 'full') === 'part')>Parte</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label source-url-label">URL o iframe {{ $index + 1 }}</label>
                                <input class="form-control" type="text" name="source_url[]" value="{{ $source['video_url'] }}"
                                    placeholder="https://www.youtube.com/watch?v=... o <iframe ...>">
                                @error("source_url.$index")
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label source-label-label">Etiqueta {{ $index + 1 }}</label>
                                <input class="form-control" name="source_label[]" value="{{ $source['label'] }}" placeholder="Ej. Principal, HD Latino, Backup">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label source-order-label">Orden</label>
                                <input class="form-control source-order-input" type="number" min="1" name="source_sort_order[]" value="{{ $source['sort_order'] ?? ($index + 1) }}">
                            </div>
                            <div class="col-md-1 d-flex align-items-end justify-content-between gap-2">
                                <label class="form-check form-check-custom form-check-solid mb-0">
                                    <input class="form-check-input source-primary-input" type="radio" name="source_primary" value="{{ $index }}" {{ $primaryIndex === $index ? 'checked' : '' }}>
                                </label>
                                <button type="button" class="btn btn-icon btn-light-danger source-remove-btn" title="Eliminar fuente">
                                    <span class="fs-5">×</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('admin.episodes.index') }}" class="btn btn-light">Cancelar</a>
        <button class="btn btn-primary" type="submit">Guardar</button>
    </div>
</div>
<template id="episode-source-template">
    <div class="border rounded p-4 source-row" data-source-row>
        <div class="row g-4">
            <div class="col-md-3">
                <label class="form-label source-provider-label">Proveedor</label>
                <select class="form-select" name="source_provider[]">
                    <option value="">Selecciona</option>
                    @foreach($sourceProviders as $providerKey => $providerConfig)
                        <option value="{{ $providerKey }}">{{ $providerConfig['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label source-type-label">Modo</label>
                <select class="form-select" name="source_type[]">
                    <option value="full">Completo</option>
                    <option value="part">Parte</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label source-url-label">URL o iframe</label>
                <input class="form-control" type="text" name="source_url[]" placeholder="https://www.youtube.com/watch?v=... o <iframe ...>">
            </div>
            <div class="col-md-2">
                <label class="form-label source-label-label">Etiqueta</label>
                <input class="form-control" name="source_label[]" placeholder="Ej. Principal, HD Latino, Backup">
            </div>
            <div class="col-md-1">
                <label class="form-label source-order-label">Orden</label>
                <input class="form-control source-order-input" type="number" min="1" name="source_sort_order[]" value="1">
            </div>
            <div class="col-md-1 d-flex align-items-end justify-content-between gap-2">
                <label class="form-check form-check-custom form-check-solid mb-0">
                    <input class="form-check-input source-primary-input" type="radio" name="source_primary" value="0">
                </label>
                <button type="button" class="btn btn-icon btn-light-danger source-remove-btn" title="Eliminar fuente">
                    <span class="fs-5">×</span>
                </button>
            </div>
        </div>
    </div>
</template>
<script>
(() => {
    const list = document.getElementById('episode-sources-list');
    const addButton = document.getElementById('add-source-row');
    const template = document.getElementById('episode-source-template');
    const form = document.getElementById('episode-form');

    if (!list || !addButton || !template) {
        return;
    }

    const refreshRows = () => {
        const rows = [...list.querySelectorAll('[data-source-row]')];

        rows.forEach((row, index) => {
            row.querySelector('.source-provider-label').textContent = `Proveedor ${index + 1}`;
            row.querySelector('.source-type-label').textContent = `Modo ${index + 1}`;
            row.querySelector('.source-url-label').textContent = `URL o iframe ${index + 1}`;
            row.querySelector('.source-label-label').textContent = `Etiqueta ${index + 1}`;
            row.querySelector('.source-order-label').textContent = 'Orden';
            row.querySelector('.source-primary-input').value = index;
            const orderInput = row.querySelector('.source-order-input');
            if (orderInput && !orderInput.value) {
                orderInput.value = index + 1;
            }
        });

        if (rows.length === 1) {
            rows[0].querySelector('.source-remove-btn').setAttribute('disabled', 'disabled');
        } else {
            rows.forEach((row) => row.querySelector('.source-remove-btn').removeAttribute('disabled'));
        }

        if (!rows.some((row) => row.querySelector('.source-primary-input').checked) && rows[0]) {
            rows[0].querySelector('.source-primary-input').checked = true;
        }
    };

    addButton.addEventListener('click', () => {
        const fragment = template.content.cloneNode(true);
        list.appendChild(fragment);
        refreshRows();
    });

    list.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.source-remove-btn');

        if (!removeButton) {
            return;
        }

        const rows = list.querySelectorAll('[data-source-row]');

        if (rows.length <= 1) {
            return;
        }

        const row = removeButton.closest('[data-source-row]');
        const radio = row?.querySelector('.source-primary-input');
        const wasChecked = radio?.checked;

        row?.remove();
        refreshRows();

        if (wasChecked) {
            list.querySelector('.source-primary-input')?.setAttribute('checked', 'checked');
            const firstRadio = list.querySelector('.source-primary-input');

            if (firstRadio) {
                firstRadio.checked = true;
            }
        }
    });

    refreshRows();

    if (!form || form.dataset.ajaxSubmit !== 'true') {
        return;
    }

    const clearValidation = () => {
        form.querySelectorAll('.is-invalid').forEach((element) => element.classList.remove('is-invalid'));
        form.querySelectorAll('[data-inline-error]').forEach((element) => element.remove());
    };

    const showInlineError = (fieldName, message) => {
        const normalizedName = fieldName.replace(/\.(\d+)/g, '[$1]');
        const field = form.querySelector(`[name="${normalizedName}"]`);

        if (!field) {
            return;
        }

        field.classList.add('is-invalid');

        const error = document.createElement('div');
        error.className = 'invalid-feedback d-block';
        error.dataset.inlineError = 'true';
        error.textContent = message;
        field.insertAdjacentElement('afterend', error);
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearValidation();

        const submitButton = form.querySelector('button[type="submit"]');
        submitButton?.setAttribute('data-kt-indicator', 'on');
        submitButton?.setAttribute('disabled', 'disabled');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: new FormData(form),
            });

            const payload = await response.json();

            if (!response.ok) {
                if (response.status === 422 && payload.errors) {
                    Object.entries(payload.errors).forEach(([field, messages]) => {
                        if (Array.isArray(messages) && messages[0]) {
                            showInlineError(field, messages[0]);
                        }
                    });

                    adminToast('error', payload.message || 'Revisa los campos marcados.');
                    return;
                }

                throw new Error(payload.message || 'No se pudo guardar el episodio.');
            }

            adminToast('success', payload.message || 'Episodio guardado.');

            if (payload.redirect) {
                setTimeout(() => {
                    window.location.href = payload.redirect;
                }, 500);
            }
        } catch (error) {
            adminToast('error', error.message || 'Ocurrió un error inesperado.');
        } finally {
            submitButton?.removeAttribute('data-kt-indicator');
            submitButton?.removeAttribute('disabled');
        }
    });
})();
</script>
