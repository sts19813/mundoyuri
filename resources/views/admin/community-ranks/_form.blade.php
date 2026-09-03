@php($editing = isset($communityRank))

<div class="card">
    <div class="card-body p-9">
        <div class="row g-6 mb-6">
            <div class="col-lg-6">
                <label class="form-label required">Nombre</label>
                <input type="text" name="name" value="{{ old('name', $communityRank->name ?? '') }}" maxlength="100" required
                    class="form-control form-control-solid @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label required">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $communityRank->slug ?? '') }}" maxlength="100"
                    placeholder="Se genera desde el nombre" class="form-control form-control-solid @error('slug') is-invalid @enderror">
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-6">
            <label class="form-label">Descripción</label>
            <textarea name="description" rows="4" maxlength="1000"
                class="form-control form-control-solid @error('description') is-invalid @enderror">{{ old('description', $communityRank->description ?? '') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-6 mb-6">
            <div class="col-md-6 col-xl-3">
                <label class="form-label">Mínimo de publicaciones</label>
                <input type="number" name="minimum_posts" min="0" max="4294967295"
                    value="{{ old('minimum_posts', $communityRank->minimum_posts ?? 0) }}"
                    class="form-control form-control-solid @error('minimum_posts') is-invalid @enderror">
                <div class="form-text">Obligatorio para rangos automáticos.</div>
                @error('minimum_posts')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 col-xl-3">
                <label class="form-label required">Prioridad</label>
                <input type="number" name="priority" min="0" max="65535" required
                    value="{{ old('priority', $communityRank->priority ?? 0) }}"
                    class="form-control form-control-solid @error('priority') is-invalid @enderror">
                <div class="form-text">Desempata rangos con el mismo umbral.</div>
                @error('priority')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 col-xl-3">
                <label class="form-label">Icono</label>
                <input type="text" name="icon" maxlength="50" value="{{ old('icon', $communityRank->icon ?? '') }}"
                    placeholder="✦" class="form-control form-control-solid @error('icon') is-invalid @enderror">
                @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 col-xl-3">
                <label class="form-label">Color</label>
                <input type="color" name="color" value="{{ old('color', $communityRank->color ?? '#f472b6') }}"
                    class="form-control form-control-color form-control-solid @error('color') is-invalid @enderror">
                @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-7">
            <label class="form-label">Clases CSS opcionales</label>
            <input type="text" name="css_class" maxlength="120" value="{{ old('css_class', $communityRank->css_class ?? '') }}"
                placeholder="community-rank-staff" class="form-control form-control-solid @error('css_class') is-invalid @enderror">
            <div class="form-text">Solo nombres de clase; no acepta reglas CSS ni HTML.</div>
            @error('css_class')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="row g-5">
            @foreach([
                'is_special' => ['Rango especial', 'Puede asignarse manualmente y prevalece sobre el automático.'],
                'is_legacy' => ['Rango histórico', 'Identifica rangos vinculados con el Mundo Yuri clásico.'],
                'is_active' => ['Activo', 'Los rangos inactivos no se calculan ni pueden asignarse.'],
            ] as $field => [$label, $help])
                <div class="col-lg-4">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <label class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1"
                            @checked((bool) old($field, $communityRank->{$field} ?? ($field === 'is_active')) )>
                        <span class="form-check-label">
                            <strong class="d-block">{{ $label }}</strong>
                            <small class="text-muted">{{ $help }}</small>
                        </span>
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card-footer d-flex justify-content-end gap-3">
        <a href="{{ route('admin.community-ranks.index') }}" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary">{{ $editing ? 'Guardar cambios' : 'Crear rango' }}</button>
    </div>
</div>
