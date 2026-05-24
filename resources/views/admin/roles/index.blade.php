@extends('layouts.admin')

@section('title', 'Roles - Admin')

@section('toolbar')
<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Roles y permisos</h1></div>
<div><a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Nuevo rol</a></div>
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->has('role'))<div class="alert alert-danger">{{ $errors->first('role') }}</div>@endif
<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-row-dashed gy-5">
            <thead><tr><th>Rol</th><th>Usuarios</th><th>Permisos</th><th></th></tr></thead>
            <tbody>
            @forelse($roles as $role)
                <tr>
                    <td><span class="fw-bold">{{ $role->name }}</span></td>
                    <td>{{ $role->users_count }}</td>
                    <td>
                        @foreach($role->permissions->take(5) as $permission)
                            <span class="badge badge-light-primary me-1 mb-1">{{ $permission->name }}</span>
                        @endforeach
                        @if($role->permissions->count() > 5)
                            <span class="badge badge-light">+{{ $role->permissions->count() - 5 }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-light">Ver</a>
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-light-primary">Editar</a>
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('Eliminar rol?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-light-danger" type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">No hay roles</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $roles->links() }}
    </div>
</div>
@endsection
