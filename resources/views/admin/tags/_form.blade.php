@csrf
@if(isset($tag)) @method('PUT') @endif
<div class="card"><div class="card-body row g-4">
    <div class="col-md-6"><label class="form-label">Nombre</label><input class="form-control" name="name" value="{{ old('name', $tag->name ?? '') }}" required></div>
    <div class="col-md-6"><label class="form-label">Slug (opcional)</label><input class="form-control" name="slug" value="{{ old('slug', $tag->slug ?? '') }}"></div>
    <div class="col-md-6"><label class="form-label">Categoría</label><input class="form-control" name="category" value="{{ old('category', $tag->category ?? 'theme') }}" required><div class="form-text">Ej.: theme, setting, country, format o identity.</div></div>
    <div class="col-md-6 d-flex align-items-end"><label class="form-check form-check-custom form-check-solid"><input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $tag->is_active ?? true) ? 'checked' : '' }}><span class="form-check-label">Etiqueta activa</span></label></div>
</div><div class="card-footer d-flex justify-content-end gap-2"><a href="{{ route('admin.tags.index') }}" class="btn btn-light">Cancelar</a><button class="btn btn-primary" type="submit">Guardar</button></div></div>
