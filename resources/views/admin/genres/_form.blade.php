@csrf
@if(isset($genre)) @method('PUT') @endif
<div class="card">
    <div class="card-body row g-4">
        <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input class="form-control" name="name" value="{{ old('name', $genre->name ?? '') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Slug (opcional)</label>
            <input class="form-control" name="slug" value="{{ old('slug', $genre->slug ?? '') }}">
        </div>
        <div class="col-12">
            <label class="form-label">Descripcion</label>
            <textarea class="form-control" rows="4" name="description">{{ old('description', $genre->description ?? '') }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-check form-check-custom form-check-solid">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $genre->is_active ?? true) ? 'checked' : '' }}>
                <span class="form-check-label">Genero activo</span>
            </label>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('admin.genres.index') }}" class="btn btn-light">Cancelar</a>
        <button class="btn btn-primary" type="submit">Guardar</button>
    </div>
</div>
