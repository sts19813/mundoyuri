@extends('layouts.admin')
@section('title', 'Artistas')
@section('toolbar')<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Artistas</h1></div><div><a href="{{ route('admin.artists.create') }}" class="btn btn-primary">Nueva artista</a></div>@endsection
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-row-dashed gy-5"><thead><tr><th>Nombre</th><th>Alias</th><th>Títulos</th><th>Estado</th><th></th></tr></thead><tbody>
@forelse($artists as $artist)<tr><td>{{ $artist->name }}</td><td>{{ $artist->aliases ?: '—' }}</td><td>{{ $artist->series_count }}</td><td><span class="badge {{ $artist->is_active ? 'badge-light-success' : 'badge-light-danger' }}">{{ $artist->is_active ? 'Activa' : 'Inactiva' }}</span></td><td class="text-end"><a href="{{ route('admin.artists.edit', $artist) }}" class="btn btn-sm btn-light-primary">Editar</a><form class="d-inline" method="POST" action="{{ route('admin.artists.destroy', $artist) }}" data-dialog-confirm="Esta acción no se puede deshacer." data-dialog-title="¿Eliminar artista?" data-dialog-confirm-text="Eliminar">@csrf @method('DELETE')<button class="btn btn-sm btn-light-danger">Eliminar</button></form></td></tr>
@empty<tr><td colspan="5" class="text-center text-muted">Aún no hay artistas.</td></tr>@endforelse
</tbody></table></div>{{ $artists->links() }}</div></div>
@endsection
