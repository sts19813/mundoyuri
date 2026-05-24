@extends('layouts.admin')

@section('title', 'Validacion de contenido')

@section('toolbar')
<div class="page-title d-flex flex-column justify-content-center me-3">
    <h1 class="page-heading fw-bold fs-3 m-0">Validacion de contenido</h1>
</div>
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card mb-8">
    <div class="card-header"><h3 class="card-title">Series/Peliculas pendientes</h3></div>
    <div class="card-body table-responsive">
        <table class="table table-row-dashed gy-5">
            <thead><tr><th>Titulo</th><th>Genero</th><th>Autor</th><th></th></tr></thead>
            <tbody>
            @forelse($pendingSeries as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->genre->name }}</td>
                    <td>{{ $item->creator?->name ?: 'N/D' }}</td>
                    <td class="text-end">
                        <form class="d-inline" method="POST" action="{{ route('admin.moderation.series.approve', $item) }}">@csrf<button class="btn btn-sm btn-success">Aprobar</button></form>
                        <form class="d-inline" method="POST" action="{{ route('admin.moderation.series.reject', $item) }}">
                            @csrf
                            <input type="hidden" name="moderation_notes" value="No cumple lineamientos de calidad.">
                            <button class="btn btn-sm btn-danger">Rechazar</button>
                        </form>
                        <a href="{{ route('admin.series.edit', $item) }}" class="btn btn-sm btn-light">Revisar</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">Sin pendientes</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $pendingSeries->links() }}
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Episodios pendientes</h3></div>
    <div class="card-body table-responsive">
        <table class="table table-row-dashed gy-5">
            <thead><tr><th>Serie</th><th>Episodio</th><th>Fuentes</th><th>Autor</th><th></th></tr></thead>
            <tbody>
            @forelse($pendingEpisodes as $item)
                <tr>
                    <td>{{ $item->series->title }}</td>
                    <td>S{{ $item->season_number }}E{{ $item->episode_number }} · {{ $item->title }}</td>
                    <td>{{ $item->sources->count() }}</td>
                    <td>{{ $item->creator?->name ?: 'N/D' }}</td>
                    <td class="text-end">
                        <form class="d-inline" method="POST" action="{{ route('admin.moderation.episodes.approve', $item) }}">@csrf<button class="btn btn-sm btn-success">Aprobar</button></form>
                        <form class="d-inline" method="POST" action="{{ route('admin.moderation.episodes.reject', $item) }}">
                            @csrf
                            <input type="hidden" name="moderation_notes" value="Contenido incompleto o invalido.">
                            <button class="btn btn-sm btn-danger">Rechazar</button>
                        </form>
                        <a href="{{ route('admin.episodes.edit', $item) }}" class="btn btn-sm btn-light">Revisar</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Sin pendientes</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $pendingEpisodes->links() }}
    </div>
</div>
@endsection
