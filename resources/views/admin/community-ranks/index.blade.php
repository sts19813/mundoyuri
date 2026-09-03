@extends('layouts.admin')

@section('title', 'Rangos comunitarios - Admin')

@section('toolbar')
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Rangos comunitarios</h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 ms-2">
            <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Admin</a></li>
            <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
            <li class="breadcrumb-item text-muted">Comunidad</li>
        </ul>
    </div>
    <a href="{{ route('admin.community-ranks.create') }}" class="btn btn-sm btn-primary">
        <i class="ki-outline ki-plus fs-2"></i>Nuevo rango
    </a>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div>
                    <h2 class="fw-bold text-gray-900 mb-1">Progresión y títulos especiales</h2>
                    <div class="text-muted fs-7">Los rangos son visuales; nunca conceden roles ni permisos Spatie.</div>
                </div>
            </div>
        </div>
        <div class="card-body pt-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                            <th>Rango</th>
                            <th>Tipo</th>
                            <th>Mínimo</th>
                            <th>Prioridad</th>
                            <th>Asignaciones</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($communityRanks as $communityRank)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="fs-2">{{ $communityRank->icon ?: '✦' }}</span>
                                        <div>
                                            <div class="fw-bold text-gray-900">{{ $communityRank->name }}</div>
                                            <div class="text-muted fs-7">{{ $communityRank->slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $communityRank->is_special ? 'badge-light-warning' : 'badge-light-primary' }}">
                                        {{ $communityRank->is_special ? 'Especial/manual' : 'Automático' }}
                                    </span>
                                    @if($communityRank->is_legacy)<span class="badge badge-light-info">Histórico</span>@endif
                                </td>
                                <td>{{ $communityRank->is_special ? '—' : number_format($communityRank->minimum_posts) }}</td>
                                <td>{{ $communityRank->priority }}</td>
                                <td>{{ $communityRank->users_count }}</td>
                                <td><span class="badge {{ $communityRank->is_active ? 'badge-light-success' : 'badge-light-danger' }}">{{ $communityRank->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.community-ranks.edit', $communityRank) }}" class="btn btn-sm btn-light-primary me-1">
                                        <i class="ki-outline ki-pencil fs-4"></i>Editar
                                    </a>
                                    <form method="POST" action="{{ route('admin.community-ranks.destroy', $communityRank) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este rango? Las asignaciones manuales volverán al cálculo automático.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light-danger"><i class="ki-outline ki-trash fs-4"></i>Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-10">No hay rangos comunitarios.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $communityRanks->links() }}
        </div>
    </div>
@endsection
