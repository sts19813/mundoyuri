@extends('layouts.admin')
@section('title', 'Editar serie')
@section('toolbar')<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Editar serie/pelicula</h1></div>@endsection
@section('content')
<form method="POST" action="{{ route('admin.series.update', $series) }}">
    @include('admin.series._form', ['series' => $series])
</form>
@endsection
