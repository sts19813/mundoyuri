@csrf
@if(isset($catalogSection)) @method('PUT') @endif

<div class="card">
    <div class="card-body row g-4">
        <div class="col-md-4">
            <label class="form-label">Nombre de la sección</label>
            <input class="form-control" name="name" value="{{ old('name', $catalogSection->name ?? '') }}" placeholder="Anime" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Etiqueta del contenido</label>
            <input class="form-control" name="label" value="{{ old('label', $catalogSection->label ?? '') }}" placeholder="Anime o Serie GL">
            <div class="form-text">Aparece junto a cada título del catálogo.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">URL corta</label>
            <div class="input-group"><span class="input-group-text">mundoyuri.com/</span><input class="form-control" name="slug" value="{{ old('slug', $catalogSection->slug ?? '') }}" placeholder="anime" required></div>
        </div>
        <div class="col-12"><hr><h4 class="mb-0">Hero de la sección</h4></div>
        <div class="col-md-6"><label class="form-label">Texto superior</label><input class="form-control" name="hero_eyebrow" value="{{ old('hero_eyebrow', $catalogSection->hero_eyebrow ?? '') }}" placeholder="Anime · Actualizado diario"></div>
        <div class="col-md-6">
            <label class="form-label">Video de fondo</label>
            <input class="form-control" type="url" name="hero_video_url" value="{{ old('hero_video_url', $catalogSection->hero_video_url ?? '') }}" placeholder="https://www.youtube.com/watch?v=...">
            <div class="form-text">Admite enlaces de YouTube o URL directa a un video MP4/WebM.</div>
        </div>
        <div class="col-12"><label class="form-label">Título principal</label><input class="form-control" name="hero_title" value="{{ old('hero_title', $catalogSection->hero_title ?? '') }}" required></div>
        <div class="col-12"><label class="form-label">Descripción</label><textarea class="form-control" rows="3" name="hero_description">{{ old('hero_description', $catalogSection->hero_description ?? '') }}</textarea></div>
        <div class="col-md-6"><label class="form-label">Botón principal</label><input class="form-control" name="hero_primary_label" value="{{ old('hero_primary_label', $catalogSection->hero_primary_label ?? '') }}" placeholder="Explorar anime"></div>
        <div class="col-md-6"><label class="form-label">Botón secundario</label><input class="form-control" name="hero_secondary_label" value="{{ old('hero_secondary_label', $catalogSection->hero_secondary_label ?? '') }}" placeholder="Ver novedades"></div>
        <div class="col-md-3"><label class="form-label">Orden</label><input class="form-control" type="number" min="0" name="sort_order" value="{{ old('sort_order', $catalogSection->sort_order ?? 0) }}"></div>
        <div class="col-md-3 d-flex align-items-end"><label class="form-check form-check-custom form-check-solid"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $catalogSection->is_active ?? true))><span class="form-check-label">Sección visible</span></label></div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2"><a href="{{ route('admin.catalog-sections.index') }}" class="btn btn-light">Cancelar</a><button class="btn btn-primary" type="submit">Guardar</button></div>
</div>
