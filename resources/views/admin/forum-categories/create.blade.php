@extends('layouts.admin')
@section('title', 'Nueva categoría de foro - Admin')
@section('toolbar')<h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Nueva categoría de foro</h1>@endsection
@section('content')<form method="POST" action="{{ route('admin.forum-categories.store') }}">@csrf @include('admin.forum-categories._form')</form>@endsection
