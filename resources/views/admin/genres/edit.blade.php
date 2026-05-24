@extends('layouts.admin')
@section('title', 'Editar genero')
@section('toolbar')
<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Editar genero</h1></div>
@endsection
@section('content')
<form method="POST" action="{{ route('admin.genres.update', $genre) }}">
    @include('admin.genres._form', ['genre' => $genre])
</form>
@endsection
