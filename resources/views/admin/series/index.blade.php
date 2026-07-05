@extends('layouts.admin')

@section('title', 'Series y películas')

@section('toolbar')
<div class="page-title d-flex flex-column justify-content-center me-3">
    <h1 class="page-heading fw-bold fs-3 m-0">Series y películas</h1>
</div>
@can('create series')
    <div><a href="{{ route('admin.series.create') }}" class="btn btn-primary">Nuevo título</a></div>
@endcan
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card mb-5">
    <div class="card-body">
        <form class="row g-3" method="GET" action="{{ route('admin.series.index') }}">
            <div class="col-md-5"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Buscar titulo"></div>
            <div class="col-md-4">
                <select class="form-select" name="moderation_status">
                    <option value="">Todos los estados</option>
                    <option value="pending" @selected(request('moderation_status')==='pending')>Pendiente</option>
                    <option value="approved" @selected(request('moderation_status')==='approved')>Aprobado</option>
                    <option value="rejected" @selected(request('moderation_status')==='rejected')>Rechazado</option>
                </select>
            </div>
            <div class="col-md-3 d-grid"><button class="btn btn-light-primary" type="submit">Filtrar</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-row-dashed gy-5">
            <thead><tr><th>Titulo</th><th>Genero</th><th>Tipo</th><th>Estado</th><th>Moderacion</th><th></th></tr></thead>
            <tbody>
            @forelse($series as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->genre->name }}</td>
                    <td>{{ $item->content_type === 'series' ? 'Serie' : 'Pelicula' }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td><span class="badge badge-light-{{ $item->moderation_status === 'approved' ? 'success' : ($item->moderation_status === 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($item->moderation_status) }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('admin.series.show', $item) }}" class="btn btn-sm btn-light">Ver</a>
                        @can('edit series')
                            <a href="{{ route('admin.series.edit', $item) }}" class="btn btn-sm btn-light-primary">Editar</a>
                        @endcan
                        @can('delete series')
                            <form class="d-inline" method="POST" action="{{ route('admin.series.destroy', $item) }}" onsubmit="return confirm('¿Eliminar título?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger" type="submit">Eliminar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $series->links() }}
    </div>
</div>
@endsection
