@extends('layouts.admin')
@section('title', 'Editar foro - Admin')
@section('toolbar')<h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Editar foro</h1>@endsection
@section('content')<form method="POST" action="{{ route('admin.forums.update', $forum) }}">@csrf @method('PUT') @include('admin.forums._form')</form>@endsection
