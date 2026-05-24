@extends('layouts.admin')
@section('title', 'Ver genero')
@section('toolbar')
<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">{{ $genre->name }}</h1></div>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <p><strong>Slug:</strong> {{ $genre->slug }}</p>
        <p><strong>Descripcion:</strong> {{ $genre->description ?: 'Sin descripcion' }}</p>
        <p><strong>Estado:</strong> {{ $genre->is_active ? 'Activo' : 'Inactivo' }}</p>
        <a href="{{ route('admin.genres.edit', $genre) }}" class="btn btn-primary">Editar</a>
        <a href="{{ route('admin.genres.index') }}" class="btn btn-light">Volver</a>
    </div>
</div>
@endsection
