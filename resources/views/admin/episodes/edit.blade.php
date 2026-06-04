@extends('layouts.admin')
@section('title', 'Editar episodio')
@section('toolbar')<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Editar episodio</h1></div>@endsection
@section('content')
<form method="POST" action="{{ route('admin.episodes.update', $episode) }}" id="episode-form" data-ajax-submit="true">
    @include('admin.episodes._form', ['episode' => $episode])
</form>
@endsection
