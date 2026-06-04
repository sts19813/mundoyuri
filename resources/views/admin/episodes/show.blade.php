@extends('layouts.admin')
@section('title', 'Detalle episodio')
@section('toolbar')<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">{{ $episode->title }}</h1></div>@endsection
@section('content')
<div class="card mb-5">
    <div class="card-body">
        <p><strong>Serie:</strong> {{ $episode->series->title }}</p>
        <p><strong>Episodio:</strong> S{{ $episode->season_number }}E{{ $episode->episode_number }}</p>
        <p><strong>Fecha:</strong> {{ optional($episode->release_date)->format('d/m/Y') ?: 'N/D' }}</p>
        <p><strong>Moderacion:</strong> {{ $episode->moderation_status }}</p>
        <p><strong>Descripcion:</strong> {{ $episode->description ?: 'Sin descripcion' }}</p>
        <a href="{{ route('admin.episodes.edit', $episode) }}" class="btn btn-primary">Editar</a>
        <a href="{{ route('admin.episodes.index') }}" class="btn btn-light">Volver</a>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3 class="card-title">Fuentes</h3></div>
    <div class="card-body">
        <ul class="list-group">
            @forelse($episode->sources as $source)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $source->source_type === 'part' ? 'Parte '.$source->sort_order : 'Fuente' }} · {{ strtoupper($source->provider) }}{{ $source->label ? ' · '.$source->label : '' }}</span>
                    <a href="{{ $source->playable_url }}" target="_blank" rel="noopener">Abrir</a>
                </li>
            @empty
                <li class="list-group-item text-muted">Sin fuentes registradas</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
