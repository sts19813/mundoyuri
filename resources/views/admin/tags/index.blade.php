@extends('layouts.admin')

@section('title', 'Etiquetas')

@section('toolbar')
<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Etiquetas</h1></div>
<div><a href="{{ route('admin.tags.create') }}" class="btn btn-primary">Nueva etiqueta</a></div>
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-row-dashed gy-5">
    <thead><tr><th>Etiqueta</th><th>Categoría</th><th>Slug</th><th>Títulos</th><th>Estado</th><th></th></tr></thead>
    <tbody>@forelse($tags as $tag)<tr>
        <td>{{ $tag->name }}</td><td><span class="badge badge-light-primary">{{ $tag->category }}</span></td><td>{{ $tag->slug }}</td><td>{{ $tag->series_count }}</td>
        <td><span class="badge {{ $tag->is_active ? 'badge-light-success' : 'badge-light-danger' }}">{{ $tag->is_active ? 'Activa' : 'Inactiva' }}</span></td>
        <td class="text-end"><a href="{{ route('admin.tags.edit', $tag) }}" class="btn btn-sm btn-light-primary">Editar</a><form class="d-inline" method="POST" action="{{ route('admin.tags.destroy', $tag) }}" data-dialog-confirm="Esta acción no se puede deshacer." data-dialog-title="¿Eliminar etiqueta?" data-dialog-confirm-text="Eliminar">@csrf @method('DELETE')<button class="btn btn-sm btn-light-danger" type="submit">Eliminar</button></form></td>
    </tr>@empty<tr><td colspan="6" class="text-center text-muted">Sin etiquetas</td></tr>@endforelse</tbody>
</table></div>{{ $tags->links() }}</div></div>
@endsection
