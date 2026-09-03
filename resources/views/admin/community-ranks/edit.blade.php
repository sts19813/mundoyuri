@extends('layouts.admin')

@section('title', 'Editar rango comunitario - Admin')

@section('toolbar')
    <div class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Editar {{ $communityRank->name }}</h1>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.community-ranks.update', $communityRank) }}">
        @csrf
        @method('PUT')
        @include('admin.community-ranks._form')
    </form>
@endsection
