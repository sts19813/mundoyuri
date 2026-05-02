@extends('layouts.admin')

@section('title', 'Mi Perfil - Admin')

@section('toolbar')
    <!--begin::Page title-->
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            Mi Perfil
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
            <li class="breadcrumb-item text-muted">Perfil</li>
        </ul>
        <!--end::Breadcrumb-->
    </div>
    <!--end::Page title-->
@endsection

@section('content')
    <!--begin::Card-->
    <div class="card mb-5 mb-xl-10">
        <!--begin::Card header-->
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_account_profile_details" aria-expanded="true">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">Detalles del Perfil</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--begin::Card header-->

        <!--begin::Content-->
        <div id="kt_account_profile_details" class="collapse show">
            <!--begin::Form-->
            <form id="kt_account_profile_details_form" class="form" action="{{ route('admin.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!--begin::Card body-->
                <div class="card-body border-top p-9">
                    <!--begin::Row-->
                    <div class="row mb-6">
                        <!--begin::Label-->
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Nombre</label>
                        <!--end::Label-->

                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}"
                                class="form-control form-control-lg form-control-solid @error('name') is-invalid @enderror"
                                placeholder="Nombre" required />
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
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
                        <div class="col-lg-8 fv-row">
                            <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}"
                                class="form-control form-control-lg form-control-solid @error('email') is-invalid @enderror"
                                placeholder="Email" required />
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->

                    <!--begin::Row-->
                    <div class="row mb-0">
                        <!--begin::Label-->
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Rol</label>
                        <!--end::Label-->

                        <!--begin::Col-->
                        <div class="col-lg-8">
                            <div class="fw-bold">{{ Auth::user()->role }}</div>
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Card body-->

                <!--begin::Actions-->
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Card-->

    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_account_security_details" aria-expanded="true">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">Cambiar Contraseña</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--begin::Card header-->

        <!--begin::Content-->
        <div id="kt_account_security_details" class="collapse show">
            <!--begin::Form-->
            <form id="kt_account_security_details_form" class="form" action="{{ route('admin.profile.updatePassword') }}" method="POST">
                @csrf
                @method('PUT')

                <!--begin::Card body-->
                <div class="card-body border-top p-9">
                    <!--begin::Row-->
                    <div class="row mb-6">
                        <!--begin::Label-->
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Contraseña Actual</label>
                        <!--end::Label-->

                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <input type="password" name="current_password"
                                class="form-control form-control-lg form-control-solid @error('current_password') is-invalid @enderror"
                                placeholder="Contraseña actual" required />
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->

                    <!--begin::Row-->
                    <div class="row mb-6">
                        <!--begin::Label-->
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Nueva Contraseña</label>
                        <!--end::Label-->

                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <input type="password" name="password"
                                class="form-control form-control-lg form-control-solid @error('password') is-invalid @enderror"
                                placeholder="Nueva contraseña" required />
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->

                    <!--begin::Row-->
                    <div class="row mb-0">
                        <!--begin::Label-->
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Confirmar Contraseña</label>
                        <!--end::Label-->

                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <input type="password" name="password_confirmation"
                                class="form-control form-control-lg form-control-solid"
                                placeholder="Confirmar contraseña" required />
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Card body-->

                <!--begin::Actions-->
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Card-->
@endsection
