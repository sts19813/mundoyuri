@extends('layouts.admin')
@section('title', 'Detalle serie')
@section('toolbar')<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">{{ $series->title }}</h1></div>@endsection
@section('content')
<div class="card mb-5">
    <div class="card-body">
        <p><strong>Genero:</strong> {{ $series->genre->name }}</p>
        <p><strong>Tipo:</strong> {{ $series->content_type }}</p>
        <p><strong>Estado:</strong> {{ $series->status }}</p>
        <p><strong>Moderacion:</strong> {{ $series->moderation_status }}</p>
        <p><strong>Descripcion:</strong> {{ $series->description }}</p>
        <p><strong>Pais:</strong> {{ $series->country_of_origin ?: 'N/D' }}</p>
        <p><strong>Ano:</strong> {{ $series->release_year ?: 'N/D' }}</p>
        <p><strong>Duracion:</strong> {{ $series->duration_minutes ?: 'N/D' }}</p>
        <p><strong>Creado por:</strong> {{ $series->creator?->name ?: 'Sistema' }}</p>
        <p><strong>Aprobado por:</strong> {{ $series->approver?->name ?: 'Pendiente' }}</p>
        @can('edit series')
            <a href="{{ route('admin.series.edit', $series) }}" class="btn btn-primary">Editar</a>
        @endcan
        @can('create episodes')
            <a href="{{ route('admin.episodes.create', ['series_id' => $series->id]) }}" class="btn btn-light-primary">Agregar episodio</a>
        @endcan
        <a href="{{ route('admin.series.index') }}" class="btn btn-light">Volver</a>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Episodios relacionados</h3></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-dashed gy-5">
                <thead><tr><th>Titulo</th><th>Temporada</th><th>Episodio</th><th>Moderacion</th></tr></thead>
                <tbody>
                @forelse($series->episodes as $episode)
                    <tr>
                        <td><a href="{{ route('admin.episodes.show', $episode) }}">{{ $episode->title }}</a></td>
                        <td>{{ $episode->season_number }}</td>
                        <td>{{ $episode->episode_number }}</td>
                        <td>{{ $episode->moderation_status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center">Sin episodios</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
