@csrf
@method('PUT')

<div class="card">
    <div class="card-body row g-4">
        <div class="col-12">
            <h4 class="mb-1">Portada del portal</h4>
            <p class="text-muted mb-0">Esta configuración se usa en Inicio y en las rutas antiguas del catálogo.</p>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="hero_desktop_image">Imagen desktop</label>
            @if($portalSection->heroDesktopImageUrl())
                <img src="{{ $portalSection->heroDesktopImageUrl() }}" alt="Vista previa desktop" class="rounded border d-block mb-3" style="width:100%;max-height:180px;object-fit:cover;">
            @endif
            <input id="hero_desktop_image" class="form-control" type="file" name="hero_desktop_image" accept="image/jpeg,image/png,image/webp">
            <div class="form-text">JPG, PNG o WebP. Recomendado: formato horizontal de al menos 1600 × 900 px.</div>
            @if($portalSection->hero_desktop_image)
                <label class="form-check form-check-custom form-check-solid mt-2"><input class="form-check-input" type="checkbox" name="remove_hero_desktop_image" value="1"><span class="form-check-label">Quitar imagen desktop</span></label>
            @endif
            @error('hero_desktop_image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="hero_mobile_image">Imagen teléfono</label>
            @if($portalSection->heroMobileImageUrl())
                <img src="{{ $portalSection->heroMobileImageUrl() }}" alt="Vista previa móvil" class="rounded border d-block mb-3" style="width:100%;max-height:180px;object-fit:cover;">
            @endif
            <input id="hero_mobile_image" class="form-control" type="file" name="hero_mobile_image" accept="image/jpeg,image/png,image/webp">
            <div class="form-text">Opcional. Si la dejas vacía, se usa la imagen desktop en teléfono.</div>
            @if($portalSection->hero_mobile_image)
                <label class="form-check form-check-custom form-check-solid mt-2"><input class="form-check-input" type="checkbox" name="remove_hero_mobile_image" value="1"><span class="form-check-label">Quitar imagen teléfono</span></label>
            @endif
            @error('hero_mobile_image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-12"><hr><h4 class="mb-0">Video</h4></div>
        <div class="col-md-8">
            <label class="form-label" for="hero_video_url">Video de fondo</label>
            <input id="hero_video_url" class="form-control" type="url" name="hero_video_url" value="{{ old('hero_video_url', $portalSection->hero_video_url) }}" placeholder="https://www.youtube.com/watch?v=...">
            <div class="form-text">En escritorio admite YouTube o una URL directa MP4/WebM.</div>
            @error('hero_video_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <label class="form-check form-switch form-check-custom form-check-solid pb-2">
                <input class="form-check-input" type="checkbox" name="hero_video_on_mobile" value="1" @checked(old('hero_video_on_mobile', $portalSection->hero_video_on_mobile))>
                <span class="form-check-label">Mostrar video en teléfono</span>
            </label>
        </div>

        <div class="col-12"><hr><h4 class="mb-0">Textos</h4></div>
        <div class="col-md-6"><label class="form-label" for="hero_eyebrow">Texto superior</label><input id="hero_eyebrow" class="form-control" name="hero_eyebrow" value="{{ old('hero_eyebrow', $portalSection->hero_eyebrow) }}" placeholder="Contenido GL"></div>
        <div class="col-12"><label class="form-label" for="hero_title">Título principal</label><input id="hero_title" class="form-control" name="hero_title" value="{{ old('hero_title', $portalSection->hero_title) }}" required></div>
        <div class="col-12"><label class="form-label" for="hero_description">Descripción</label><textarea id="hero_description" class="form-control" rows="3" name="hero_description">{{ old('hero_description', $portalSection->hero_description) }}</textarea></div>

        <div class="col-12"><hr><h4 class="mb-0">Botones</h4></div>
        <div class="col-md-6">
            <label class="form-check form-switch form-check-custom form-check-solid mb-3"><input class="form-check-input" type="checkbox" name="hero_primary_enabled" value="1" @checked(old('hero_primary_enabled', $portalSection->hero_primary_enabled))><span class="form-check-label">Mostrar botón principal</span></label>
            <label class="form-label" for="hero_primary_label">Texto del botón principal</label><input id="hero_primary_label" class="form-control" name="hero_primary_label" value="{{ old('hero_primary_label', $portalSection->hero_primary_label) }}" placeholder="Explorar catálogo">
            <label class="form-label mt-3" for="hero_primary_url">Destino</label><input id="hero_primary_url" class="form-control" name="hero_primary_url" value="{{ old('hero_primary_url', $portalSection->hero_primary_url) }}" placeholder="/series">
        </div>
        <div class="col-md-6">
            <label class="form-check form-switch form-check-custom form-check-solid mb-3"><input class="form-check-input" type="checkbox" name="hero_secondary_enabled" value="1" @checked(old('hero_secondary_enabled', $portalSection->hero_secondary_enabled))><span class="form-check-label">Mostrar botón secundario</span></label>
            <label class="form-label" for="hero_secondary_label">Texto del botón secundario</label><input id="hero_secondary_label" class="form-control" name="hero_secondary_label" value="{{ old('hero_secondary_label', $portalSection->hero_secondary_label) }}" placeholder="Ver novedades">
            <label class="form-label mt-3" for="hero_secondary_url">Destino</label><input id="hero_secondary_url" class="form-control" name="hero_secondary_url" value="{{ old('hero_secondary_url', $portalSection->hero_secondary_url) }}" placeholder="#novedades">
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end"><button class="btn btn-primary" type="submit">Guardar portada</button></div>
</div>
