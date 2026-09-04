@extends('layouts.admin')
@section('title', 'Editar categoría de foro - Admin')
@section('toolbar')<h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Editar categoría</h1>@endsection
@section('content')<form method="POST" action="{{ route('admin.forum-categories.update', $forumCategory) }}">@csrf @method('PUT') @include('admin.forum-categories._form')</form>@endsection
