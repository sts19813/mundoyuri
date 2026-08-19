@extends('layouts.admin')
@section('title', 'Nueva sección')
@section('content')
    <h1 class="h3 mb-5">Nueva sección del catálogo</h1>
    <form method="POST" action="{{ route('admin.catalog-sections.store') }}">@include('admin.catalog-sections._form')</form>
@endsection
