@php($editing = isset($legacyProfile))

<div class="card">
    <div class="card-body p-9">
        <div class="alert alert-light-info mb-7">
            Un perfil histórico no crea una cuenta, no requiere correo ni contraseña y no puede iniciar sesión. Los datos corresponden al archivo de Mundo Yuri.
        </div>

        <div class="row g-6 mb-6">
            <div class="col-lg-6">
                <label class="form-label required">Identificador histórico externo</label>
                <input type="text" name="legacy_external_key" maxlength="191" required value="{{ old('legacy_external_key', $legacyProfile->legacy_external_key ?? '') }}"
                    placeholder="foro-2007:123" class="form-control form-control-solid @error('legacy_external_key') is-invalid @enderror">
                <div class="form-text">Clave estable para importaciones repetibles; no se muestra públicamente.</div>
                @error('legacy_external_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label required">Nickname histórico</label>
                <input type="text" name="nickname" maxlength="120" required value="{{ old('nickname', $legacyProfile->nickname ?? '') }}"
                    class="form-control form-control-solid @error('nickname') is-invalid @enderror">
                @error('nickname')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-6 mb-6">
            <div class="col-lg-6">
                <label class="form-label required">Slug público</label>
                <input type="text" name="slug" maxlength="150" value="{{ old('slug', $legacyProfile->slug ?? '') }}" placeholder="Se genera desde el nickname"
                    class="form-control form-control-solid @error('slug') is-invalid @enderror">
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label required">Fuente</label>
                <input type="text" name="source" maxlength="255" required value="{{ old('source', $legacyProfile->source ?? 'archivo-mundo-yuri') }}"
                    placeholder="captura-foro-2007" class="form-control form-control-solid @error('source') is-invalid @enderror">
                @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-6 mb-6">
            <div class="col-md-4">
                <label class="form-label">Fecha histórica de registro</label>
                <input type="date" name="legacy_joined_at" value="{{ old('legacy_joined_at', $legacyProfile->legacy_joined_at?->format('Y-m-d')) }}"
                    class="form-control form-control-solid @error('legacy_joined_at') is-invalid @enderror">
                @error('legacy_joined_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Rango antiguo</label>
                <input type="text" name="legacy_rank" maxlength="120" value="{{ old('legacy_rank', $legacyProfile->legacy_rank ?? '') }}"
                    class="form-control form-control-solid @error('legacy_rank') is-invalid @enderror">
                @error('legacy_rank')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label required">Mensajes antiguos</label>
                <input type="number" name="legacy_message_count" min="0" max="4294967295" required value="{{ old('legacy_message_count', $legacyProfile->legacy_message_count ?? 0) }}"
                    class="form-control form-control-solid @error('legacy_message_count') is-invalid @enderror">
                @error('legacy_message_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-6 mb-6">
            <div class="col-md-6"><label class="form-label">Localización histórica</label><input type="text" name="legacy_location" maxlength="120" value="{{ old('legacy_location', $legacyProfile->legacy_location ?? '') }}" class="form-control form-control-solid @error('legacy_location') is-invalid @enderror">@error('legacy_location')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label">Ocupación histórica</label><input type="text" name="legacy_occupation" maxlength="160" value="{{ old('legacy_occupation', $legacyProfile->legacy_occupation ?? '') }}" class="form-control form-control-solid @error('legacy_occupation') is-invalid @enderror">@error('legacy_occupation')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
        <div class="mb-6"><label class="form-label">Intereses históricos</label><textarea name="legacy_interests" rows="3" maxlength="2000" class="form-control form-control-solid @error('legacy_interests') is-invalid @enderror">{{ old('legacy_interests', $legacyProfile->legacy_interests ?? '') }}</textarea>@error('legacy_interests')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-6"><label class="form-label">Sitio web histórico</label><input type="url" name="legacy_website" maxlength="2048" value="{{ old('legacy_website', $legacyProfile->legacy_website ?? '') }}" placeholder="https://" class="form-control form-control-solid @error('legacy_website') is-invalid @enderror">@error('legacy_website')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>

        <div class="mb-6">
            <label class="form-label">Avatar histórico</label>
            @if($editing && $legacyProfile->avatarUrl())<img src="{{ $legacyProfile->avatarUrl() }}" alt="Avatar histórico actual" class="d-block rounded mb-3" width="96" height="96" style="object-fit: cover;">@endif
            <input type="file" name="legacy_avatar" accept="image/jpeg,image/png,image/webp" class="form-control form-control-solid @error('legacy_avatar') is-invalid @enderror">
            <div class="form-text">JPG, PNG o WebP; máximo 2 MB y 800 × 800 px. No se descargan URLs externas durante la importación.</div>
            @error('legacy_avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if($editing)
                <label class="form-check form-check-custom form-check-solid mt-3"><input type="hidden" name="legacy_avatar_remove" value="0"><input type="checkbox" name="legacy_avatar_remove" value="1" class="form-check-input"><span class="form-check-label">Quitar avatar histórico</span></label>
            @endif
        </div>

        <div class="mb-6"><label class="form-label">Fuente o evidencia</label><textarea name="evidence" rows="4" maxlength="5000" class="form-control form-control-solid @error('evidence') is-invalid @enderror">{{ old('evidence', $legacyProfile->evidence ?? '') }}</textarea><div class="form-text">Referencia de captura, archivo o evidencia de procedencia. Solo para administración.</div>@error('evidence')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-7"><label class="form-label">Notas privadas de administración</label><textarea name="admin_notes" rows="4" maxlength="5000" class="form-control form-control-solid @error('admin_notes') is-invalid @enderror">{{ old('admin_notes', $legacyProfile->admin_notes ?? '') }}</textarea><div class="form-text">Nunca se muestran públicamente.</div>@error('admin_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>

        <input type="hidden" name="is_published" value="0">
        <label class="form-check form-switch form-check-custom form-check-solid"><input type="checkbox" name="is_published" value="1" class="form-check-input" @checked((bool) old('is_published', $legacyProfile->is_published ?? true))><span class="form-check-label"><strong class="d-block">Publicar en el archivo histórico</strong><small class="text-muted">Los perfiles no publicados solo se ven en administración.</small></span></label>
    </div>
    <div class="card-footer d-flex justify-content-end gap-3"><a href="{{ route('admin.legacy-profiles.index') }}" class="btn btn-light">Cancelar</a><button type="submit" class="btn btn-primary">{{ $editing ? 'Guardar cambios' : 'Crear perfil histórico' }}</button></div>
</div>
