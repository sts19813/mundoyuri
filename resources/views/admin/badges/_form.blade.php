@php($editing = isset($badge))

<div class="card">
    <div class="card-body p-9">
        <div class="row g-6 mb-6">
            <div class="col-lg-6">
                <label class="form-label required">Nombre</label>
                <input type="text" name="name" value="{{ old('name', $badge->name ?? '') }}" maxlength="100" required
                    class="form-control form-control-solid @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label required">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $badge->slug ?? '') }}" maxlength="100"
                    placeholder="Se genera desde el nombre" class="form-control form-control-solid @error('slug') is-invalid @enderror">
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-6">
            <label class="form-label">Descripción</label>
            <textarea name="description" rows="4" maxlength="1000"
                class="form-control form-control-solid @error('description') is-invalid @enderror">{{ old('description', $badge->description ?? '') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-6 mb-7">
            <div class="col-md-6 col-xl-3">
                <label class="form-label required">Tipo</label>
                <select name="type" required class="form-select form-select-solid @error('type') is-invalid @enderror">
                    @foreach(['legacy' => 'Histórica', 'achievement' => 'Logro', 'staff' => 'Staff', 'special' => 'Especial', 'contribution' => 'Contribución', 'development' => 'Desarrollo', 'activity' => 'Actividad', 'forum' => 'Foro', 'questions' => 'Preguntas', 'social' => 'Social', 'catalog' => 'Catálogo', 'community' => 'Comunidad', 'seniority' => 'Antigüedad', 'event' => 'Evento', 'fun' => 'Divertida', 'secret' => 'Secreta'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $badge->type ?? 'achievement') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 col-xl-3">
                <label class="form-label required">Prioridad</label>
                <input type="number" name="priority" min="0" max="65535" required value="{{ old('priority', $badge->priority ?? 0) }}"
                    class="form-control form-control-solid @error('priority') is-invalid @enderror">
                <div class="form-text">Las más altas se muestran primero.</div>
                @error('priority')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 col-xl-3">
                <label class="form-label">Icono</label>
                <input type="text" name="icon" maxlength="50" value="{{ old('icon', $badge->icon ?? '') }}" placeholder="✦"
                    class="form-control form-control-solid @error('icon') is-invalid @enderror">
                @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 col-xl-3">
                <label class="form-label">Color</label>
                <input type="color" name="color" value="{{ old('color', $badge->color ?? '#f472b6') }}"
                    class="form-control form-control-color form-control-solid @error('color') is-invalid @enderror">
                @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <input type="hidden" name="is_active" value="0">
        <label class="form-check form-switch form-check-custom form-check-solid">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $badge->is_active ?? true))>
            <span class="form-check-label">
                <strong class="d-block">Activa</strong>
                <small class="text-muted">Solo las insignias activas pueden asignarse y mostrarse públicamente.</small>
            </span>
        </label>
    </div>

    <div class="card-footer d-flex justify-content-end gap-3">
        <a href="{{ route('admin.badges.index') }}" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary">{{ $editing ? 'Guardar cambios' : 'Crear insignia' }}</button>
    </div>
</div>
