@extends('layouts.admin')

@isset($slot)
    @section('content')
        {{ $slot }}
    @endsection
@endisset
