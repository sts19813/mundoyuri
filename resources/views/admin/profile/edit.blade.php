@extends('layouts.admin')

@section('title', 'Editar Perfil - Admin')

@section('toolbar')
    <!--begin::Page title-->
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            Editar Perfil
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
            <li class="breadcrumb-item text-muted">Editar Perfil</li>
        </ul>
        <!--end::Breadcrumb-->
    </div>
    <!--end::Page title-->
@endsection

@section('content')
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <h2 class="fw-bold">Información del Perfil</h2>
            </div>
            <!--end::Card title-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body">
            <!--begin::Form-->
            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!--begin::Row-->
                <div class="row mb-6">
                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::Label-->
                        <label class="form-label fw-bold text-gray-900">Nombre</label>
                        <!--end::Label-->

                        <!--begin::Input-->
                        <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}"
                            class="form-control form-control-solid @error('name') is-invalid @enderror" required />
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <!--end::Input-->
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::Label-->
                        <label class="form-label fw-bold text-gray-900">Email</label>
                        <!--end::Label-->

                        <!--begin::Input-->
                        <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}"
                            class="form-control form-control-solid @error('email') is-invalid @enderror" required />
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <!--end::Input-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Actions-->
                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.profile.show') }}" class="btn btn-light me-3">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
@endsection
