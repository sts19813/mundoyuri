@extends('layouts.admin')
@section('title', 'Configurar '.$catalogSection->name)
@section('content')
    <h1 class="h3 mb-5">Configurar {{ $catalogSection->name }}</h1>
    <form method="POST" action="{{ route('admin.catalog-sections.update', $catalogSection) }}">@include('admin.catalog-sections._form')</form>
@endsection
