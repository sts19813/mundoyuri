@extends('layouts.admin')
@section('title', 'Configurar inicio')
@section('content')
    <h1 class="h3 mb-5">Configurar inicio</h1>
    <form method="POST" action="{{ route('admin.catalog-sections.update', $portalSection) }}" enctype="multipart/form-data">@include('admin.catalog-sections._form')</form>
@endsection
