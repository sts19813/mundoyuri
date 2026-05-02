@extends('layouts.admin')

@section('title', 'Ver Usuario - Admin')

@section('toolbar')
    <!--begin::Page title-->
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ $user->name }}
        </h1>
        <!--end::Title-->

        <!--begin::Breadcrumb-->
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 ms-2">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Admin</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-400 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('admin.users.index') }}" class="text-muted text-hover-primary">Usuarios</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-400 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">Ver</li>
        </ul>
        <!--end::Breadcrumb-->
    </div>
    <!--end::Page title-->
@endsection

@section('content')
    <!--begin::Card-->
    <div class="card mb-5 mb-xl-10">
        <!--begin::Card header-->
        <div class="card-header border-0 cursor-pointer" role="button">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">Información del Usuario</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--begin::Card header-->

        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-bold fs-6">Nombre</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <div class="fw-bold">{{ $user->name }}</div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-bold fs-6">Email</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <div class="fw-bold">{{ $user->email }}</div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-bold fs-6">Rol</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <span class="badge badge-light-primary">{{ $user->role }}</span>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-bold fs-6">Estado</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <span class="badge {{ $user->is_active ? 'badge-light-success' : 'badge-light-danger' }}">
                        {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row mb-0">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-bold fs-6">Fecha de Registro</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <div class="fw-bold">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
        </div>
        <!--end::Card body-->

        <!--begin::Card footer-->
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary me-3">
                Editar
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-light">
                Volver
            </a>
        </div>
        <!--end::Card footer-->
    </div>
    <!--end::Card-->
@endsection
