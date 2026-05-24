@extends('layouts.admin')
@section('title', 'Nuevo episodio')
@section('toolbar')<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Crear episodio</h1></div>@endsection
@section('content')
<form method="POST" action="{{ route('admin.episodes.store') }}">
    @include('admin.episodes._form')
</form>
@endsection
