@extends('layouts.admin')

@section('title', 'Dashboard - Admin')

@section('toolbar')
<div class="page-title d-flex flex-column justify-content-center me-3">
    <h1 class="page-heading fw-bold fs-3 m-0">Dashboard de Catalogo</h1>
    <span class="text-muted fs-7">Control de contenido y validacion</span>
</div>
@endsection

@section('content')
<div class="row g-5 g-xl-8">
    <div class="col-xl-3"><div class="card"><div class="card-body"><div class="fs-6 text-gray-600">Usuarios</div><div class="fs-2hx fw-bold">{{ $stats['users'] }}</div></div></div></div>
    <div class="col-xl-3"><div class="card"><div class="card-body"><div class="fs-6 text-gray-600">Generos</div><div class="fs-2hx fw-bold">{{ $stats['genres'] }}</div></div></div></div>
    <div class="col-xl-3"><div class="card"><div class="card-body"><div class="fs-6 text-gray-600">Series/Peliculas</div><div class="fs-2hx fw-bold">{{ $stats['series'] }}</div></div></div></div>
    <div class="col-xl-3"><div class="card"><div class="card-body"><div class="fs-6 text-gray-600">Episodios</div><div class="fs-2hx fw-bold">{{ $stats['episodes'] }}</div></div></div></div>
</div>

<div class="row g-5 g-xl-8 mt-1">
    <div class="col-xl-4"><div class="card"><div class="card-body"><div class="fs-6 text-gray-600">Pendientes de serie</div><div class="fs-2hx fw-bold text-warning">{{ $stats['pending_series'] }}</div></div></div></div>
    <div class="col-xl-4"><div class="card"><div class="card-body"><div class="fs-6 text-gray-600">Pendientes de episodio</div><div class="fs-2hx fw-bold text-warning">{{ $stats['pending_episodes'] }}</div></div></div></div>
    <div class="col-xl-4"><div class="card"><div class="card-body"><div class="fs-6 text-gray-600">Comentarios</div><div class="fs-2hx fw-bold">{{ $stats['comments'] }}</div></div></div></div>
</div>

<div class="card mt-8">
    <div class="card-body d-flex flex-wrap gap-3">
        <a href="{{ route('admin.moderation.index') }}" class="btn btn-primary">Ir a validacion</a>
        <a href="{{ route('admin.series.create') }}" class="btn btn-light-primary">Nueva serie o pelicula</a>
        <a href="{{ route('admin.episodes.create') }}" class="btn btn-light-primary">Nuevo episodio</a>
        <a href="{{ route('admin.genres.create') }}" class="btn btn-light-primary">Nuevo genero</a>
    </div>
</div>
@endsection
