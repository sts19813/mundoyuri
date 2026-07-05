@extends('layouts.admin')

@section('title', 'Episodios')

@section('toolbar')
<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Episodios</h1></div>
@can('create episodes')
    <div><a href="{{ route('admin.episodes.create') }}" class="btn btn-primary">Nuevo episodio</a></div>
@endcan
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card mb-5">
    <div class="card-body">
        <form class="row g-3" method="GET" action="{{ route('admin.episodes.index') }}">
            <div class="col-md-5">
                <select class="form-select" name="series_id">
                    <option value="">Todas las series</option>
                    @foreach($seriesOptions as $option)
                        <option value="{{ $option->id }}" @selected(request('series_id') == $option->id)>{{ $option->title }}</option>
                    @endforeach
                </select>
            </div>
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
            <thead><tr><th>Serie</th><th>Episodio</th><th>Fecha</th><th>Fuentes</th><th>Moderacion</th><th></th></tr></thead>
            <tbody>
            @forelse($episodes as $episode)
                <tr>
                    <td>{{ $episode->series->title }}</td>
                    <td>S{{ $episode->season_number }}E{{ $episode->episode_number }} · {{ $episode->title }}</td>
                    <td>{{ optional($episode->release_date)->format('d/m/Y') ?: 'N/D' }}</td>
                    <td>{{ $episode->sources->count() }}</td>
                    <td><span class="badge badge-light-{{ $episode->moderation_status === 'approved' ? 'success' : ($episode->moderation_status === 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($episode->moderation_status) }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('admin.episodes.show', $episode) }}" class="btn btn-sm btn-light">Ver</a>
                        @can('edit episodes')
                            <a href="{{ route('admin.episodes.edit', $episode) }}" class="btn btn-sm btn-light-primary">Editar</a>
                        @endcan
                        @can('delete episodes')
                            <form method="POST" action="{{ route('admin.episodes.destroy', $episode) }}" class="d-inline" onsubmit="return confirm('¿Eliminar episodio?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger" type="submit">Eliminar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Sin episodios</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $episodes->links() }}
    </div>
</div>
@endsection
