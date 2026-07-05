@extends('layouts.admin')

@section('title', 'Panel - Mundo Yuri')

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center me-3">
        <h1 class="page-heading fw-bold fs-3 m-0">Panel de contenido</h1>
        <span class="text-muted fs-7">
            @can('moderate content')
                Gestión y moderación del catálogo
            @else
                Tus aportes y actividad en Mundo Yuri
            @endcan
        </span>
    </div>
@endsection

@section('content')
    <div class="card mb-7">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-5">
            <div>
                <div class="text-muted fw-semibold mb-1">Hola, {{ auth()->user()->name }}</div>
                <h2 class="fw-bold text-gray-900 mb-1">
                    @can('moderate content')
                        El catálogo está listo para gestionar
                    @else
                        Comparte nuevas series, películas y episodios
                    @endcan
                </h2>
                <p class="text-muted mb-0">
                    @can('moderate content')
                        Revisa pendientes o continúa organizando la información publicada.
                    @else
                        Tus aportes quedarán pendientes hasta que un moderador los revise.
                    @endcan
                </p>
            </div>
            <div class="d-flex flex-wrap gap-3">
                @can('create series')
                    <a href="{{ route('admin.series.create') }}" class="btn btn-primary">
                        <i class="ki-outline ki-plus fs-2"></i>Nueva serie o película
                    </a>
                @endcan
                @can('create episodes')
                    <a href="{{ route('admin.episodes.create') }}" class="btn btn-light-primary">
                        <i class="ki-outline ki-plus-square fs-2"></i>Nuevo episodio
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-8">
        @if($stats['users'] !== null)
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="fs-6 text-gray-600">Usuarios</div>
                    <div class="fs-2hx fw-bold">{{ $stats['users'] }}</div>
                </div></div>
            </div>
        @endif
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="fs-6 text-gray-600">{{ auth()->user()->can('moderate content') ? 'Títulos totales' : 'Mis títulos' }}</div>
                <div class="fs-2hx fw-bold">{{ $stats['series'] }}</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="fs-6 text-gray-600">{{ auth()->user()->can('moderate content') ? 'Episodios totales' : 'Mis episodios' }}</div>
                <div class="fs-2hx fw-bold">{{ $stats['episodes'] }}</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="fs-6 text-gray-600">Comentarios</div>
                <div class="fs-2hx fw-bold">{{ $stats['comments'] }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-5 g-xl-8 mt-1">
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fs-6 text-gray-600">Títulos pendientes</div>
                    <div class="fs-2hx fw-bold text-warning">{{ $stats['pending_series'] }}</div>
                </div>
                <i class="ki-outline ki-screen fs-3x text-warning"></i>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fs-6 text-gray-600">Episodios pendientes</div>
                    <div class="fs-2hx fw-bold text-warning">{{ $stats['pending_episodes'] }}</div>
                </div>
                <i class="ki-outline ki-subtitle fs-3x text-warning"></i>
            </div></div>
        </div>
    </div>

    @can('moderate content')
        <div class="card mt-8">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="fw-bold fs-4 text-gray-900">Cola de moderación</div>
                    <div class="text-muted">Valida aportes antes de publicarlos.</div>
                </div>
                <a href="{{ route('admin.moderation.index') }}" class="btn btn-primary">Revisar pendientes</a>
            </div>
        </div>
    @endcan
@endsection
