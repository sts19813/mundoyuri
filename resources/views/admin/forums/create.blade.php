@extends('layouts.admin')
@section('title', 'Nuevo foro - Admin')
@section('toolbar')<h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Nuevo foro</h1>@endsection
@section('content')<form method="POST" action="{{ route('admin.forums.store') }}">@csrf @include('admin.forums._form')</form>@endsection
