@extends('layouts.admin')
@section('title', 'Crear rol')
@section('toolbar')
<div class="page-title d-flex flex-column justify-content-center me-3"><h1 class="page-heading fw-bold fs-3 m-0">Crear rol</h1></div>
@endsection
@section('content')
<form method="POST" action="{{ route('admin.roles.store') }}">
    @include('admin.roles._form')
</form>
@endsection
