@extends('layouts.admin')

@section('title', 'Nueva insignia - Admin')

@section('toolbar')
    <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Nueva insignia comunitaria</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.badges.store') }}">
        @csrf
        @include('admin.badges._form')
    </form>
@endsection
