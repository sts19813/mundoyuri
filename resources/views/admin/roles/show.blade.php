@extends('layouts.admin')
@section('title', 'Detalle rol')
@section('toolbar')
<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Rol: {{ $role->name }}</h1></div>
@endsection
@section('content')
<div class="card mb-5">
    <div class="card-body">
        <p><strong>Usuarios asignados:</strong> {{ $role->users->count() }}</p>
        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary">Editar</a>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-light">Volver</a>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3 class="card-title">Permisos</h3></div>
    <div class="card-body">
        @forelse($role->permissions as $permission)
            <span class="badge badge-light-primary me-1 mb-1">{{ $permission->name }}</span>
        @empty
            <span class="text-muted">Sin permisos asignados.</span>
        @endforelse
    </div>
</div>
@endsection
