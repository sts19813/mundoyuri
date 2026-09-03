@extends('layouts.admin')
@section('title', 'Importar perfil histórico - Admin')
@section('toolbar')<h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Importar perfil histórico manualmente</h1>@endsection
@section('content')<form method="POST" action="{{ route('admin.legacy-profiles.store') }}" enctype="multipart/form-data">@csrf @include('admin.legacy-profiles._form')</form>@endsection
