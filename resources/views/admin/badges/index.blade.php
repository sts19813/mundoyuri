@extends('layouts.admin')

@section('title', 'Insignias comunitarias - Admin')

@section('toolbar')
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Insignias comunitarias</h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 ms-2">
            <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Admin</a></li>
            <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
            <li class="breadcrumb-item text-muted">Comunidad</li>
        </ul>
    </div>
    <a href="{{ route('admin.badges.create') }}" class="btn btn-sm btn-primary"><i class="ki-outline ki-plus fs-2"></i>Nueva insignia</a>
@endsection

@section('content')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div>
                    <h2 class="fw-bold text-gray-900 mb-1">Reconocimientos adicionales</h2>
                    <div class="text-muted fs-7">Las insignias son visuales y no conceden roles, permisos ni rangos.</div>
                </div>
            </div>
        </div>
        <div class="card-body pt-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed gy-5">
                    <thead><tr class="text-start text-muted fw-bold fs-7 text-uppercase"><th>Insignia</th><th>Tipo</th><th>Prioridad</th><th>Asignaciones</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                        @forelse($badges as $badge)
                            <tr>
                                <td><div class="d-flex align-items-center gap-3"><span class="fs-2">{{ $badge->icon ?: '✦' }}</span><div><div class="fw-bold text-gray-900">{{ $badge->name }}</div><div class="text-muted fs-7">{{ $badge->slug }}</div></div></div></td>
                                <td><span class="badge badge-light-info">{{ match($badge->type) { 'legacy' => 'Histórica', 'achievement' => 'Logro', 'staff' => 'Staff', default => 'Especial' } }}</span></td>
                                <td>{{ $badge->priority }}</td>
                                <td>{{ $badge->users_count }}</td>
                                <td><span class="badge {{ $badge->is_active ? 'badge-light-success' : 'badge-light-danger' }}">{{ $badge->is_active ? 'Activa' : 'Inactiva' }}</span></td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.badges.edit', $badge) }}" class="btn btn-sm btn-light-primary me-1"><i class="ki-outline ki-pencil fs-4"></i>Editar</a>
                                    <form method="POST" action="{{ route('admin.badges.destroy', $badge) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta insignia? También se retirará de las personas que la tengan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light-danger"><i class="ki-outline ki-trash fs-4"></i>Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-10">No hay insignias comunitarias.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $badges->links() }}
        </div>
    </div>
@endsection
