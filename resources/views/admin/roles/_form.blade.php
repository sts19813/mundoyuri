@csrf
@if(isset($role)) @method('PUT') @endif
<div class="card">
    <div class="card-body">
        <div class="mb-5">
            <label class="form-label required">Nombre del rol</label>
            <input type="text" name="name" class="form-control form-control-solid" value="{{ old('name', $role->name ?? '') }}" required>
        </div>

        <div>
            <label class="form-label">Permisos</label>
            <div class="row g-3">
                @php $selectedPermissions = collect(old('permissions', isset($role) ? $role->permissions->pluck('name')->toArray() : [])); @endphp
                @foreach($permissions as $permission)
                    <div class="col-md-4">
                        <label class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" {{ $selectedPermissions->contains($permission->name) ? 'checked' : '' }}>
                            <span class="form-check-label">{{ $permission->name }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-light">Cancelar</a>
        <button class="btn btn-primary" type="submit">Guardar rol</button>
    </div>
</div>
