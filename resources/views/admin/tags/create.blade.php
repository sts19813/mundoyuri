@extends('layouts.admin')
@section('title', 'Nueva etiqueta')
@section('toolbar')<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Nueva etiqueta</h1></div>@endsection
@section('content')<form method="POST" action="{{ route('admin.tags.store') }}">@include('admin.tags._form')</form>@endsection
