@extends('layouts.admin')

@section('title', 'Dashboard')

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            Dashboard
        </h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">Panel</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="row g-6 g-xl-9">
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-body p-9">
                    <div class="d-flex align-items-center mb-7">
                        <div class="symbol symbol-70px me-5">
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="Avatar de {{ auth()->user()->name }}" data-profile-avatar>
                        </div>
                        <div class="min-w-0">
                            <h2 class="fw-bold text-gray-900 mb-1 text-truncate" data-profile-name>{{ auth()->user()->name }}</h2>
                            <div class="text-muted fw-semibold text-truncate" data-profile-email>{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.profile.show') }}" class="btn btn-primary">
                        <i class="ki-outline ki-user fs-2"></i>
                        Editar perfil
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="row g-6">
                <div class="col-md-6">
                    <a href="{{ route('catalog.series.index') }}" class="card h-100 hover-elevate-up">
                        <div class="card-body p-9">
                            <span class="symbol symbol-50px mb-5">
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-outline ki-film fs-2x text-primary"></i>
                                </span>
                            </span>
                            <div class="fw-bold fs-4 text-gray-900 mb-1">Catálogo</div>
                            <div class="text-muted fw-semibold">Series y películas disponibles</div>
                        </div>
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="{{ route('submissions.create') }}" class="card h-100 hover-elevate-up">
                        <div class="card-body p-9">
                            <span class="symbol symbol-50px mb-5">
                                <span class="symbol-label bg-light-success">
                                    <i class="ki-outline ki-plus-square fs-2x text-success"></i>
                                </span>
                            </span>
                            <div class="fw-bold fs-4 text-gray-900 mb-1">Subir contenido</div>
                            <div class="text-muted fw-semibold">Enviar una nueva recomendación</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
