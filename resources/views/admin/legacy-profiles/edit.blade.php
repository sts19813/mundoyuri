@extends('layouts.admin')
@section('title', 'Editar perfil histórico - Admin')
@section('toolbar')<h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Editar perfil histórico</h1>@endsection
@section('content')<form method="POST" action="{{ route('admin.legacy-profiles.update', $legacyProfile) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('admin.legacy-profiles._form')</form>@endsection
