@extends('layouts.admin')
@section('title', 'Duplas')
@section('toolbar')<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Duplas</h1></div><div><a href="{{ route('admin.duos.create') }}" class="btn btn-primary">Nueva dupla</a></div>@endsection
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-row-dashed gy-5"><thead><tr><th>Dupla</th><th>Descripción</th><th>Títulos</th><th>Estado</th><th></th></tr></thead><tbody>
@forelse($duos as $duo)<tr><td>{{ $duo->name }}</td><td>{{ \Illuminate\Support\Str::limit($duo->description, 90) ?: '—' }}</td><td>{{ $duo->series_count }}</td><td><span class="badge {{ $duo->is_active ? 'badge-light-success' : 'badge-light-danger' }}">{{ $duo->is_active ? 'Activa' : 'Inactiva' }}</span></td><td class="text-end"><a href="{{ route('admin.duos.edit', $duo) }}" class="btn btn-sm btn-light-primary">Editar</a><form class="d-inline" method="POST" action="{{ route('admin.duos.destroy', $duo) }}" data-dialog-confirm="Esta acción no se puede deshacer." data-dialog-title="¿Eliminar dupla?" data-dialog-confirm-text="Eliminar">@csrf @method('DELETE')<button class="btn btn-sm btn-light-danger">Eliminar</button></form></td></tr>
@empty<tr><td colspan="5" class="text-center text-muted">Aún no hay duplas.</td></tr>@endforelse
</tbody></table></div>{{ $duos->links() }}</div></div>
@endsection
