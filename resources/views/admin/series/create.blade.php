@extends('layouts.admin')
@section('title', 'Nueva serie')
@section('toolbar')<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Crear serie/pelicula</h1></div>@endsection
@section('content')
<form method="POST" action="{{ route('admin.series.store') }}" enctype="multipart/form-data">
    @include('admin.series._form')
</form>
@endsection
