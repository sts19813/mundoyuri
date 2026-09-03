@extends('layouts.admin')

@section('title', 'Editar insignia - Admin')

@section('toolbar')
    <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Editar insignia comunitaria</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.badges.update', $badge) }}">
        @csrf
        @method('PUT')
        @include('admin.badges._form')
    </form>
@endsection
