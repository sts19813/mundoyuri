@extends('layouts.admin')

@section('title', 'Crear Usuario - Admin')

@section('toolbar')
    <!--begin::Page title-->
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            Crear Nuevo Usuario
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
            <li class="breadcrumb-item text-muted">Crear</li>
        </ul>
        <!--end::Breadcrumb-->
    </div>
    <!--end::Page title-->
@endsection

@section('content')
    <!--begin::Form-->
    <form action="{{ route('admin.users.store') }}" method="POST" class="form">
        @csrf

        <!--begin::Card-->
        <div class="card">
            <!--begin::Card body-->
            <div class="card-body">
                <!--begin::Row-->
                <div class="row mb-6">
                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::Label-->
                        <label class="form-label fw-bold text-gray-900">Nombre</label>
                        <!--end::Label-->

                        <!--begin::Input-->
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control form-control-solid @error('name') is-invalid @enderror"
                            placeholder="Nombre del usuario" required />
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
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="form-control form-control-solid @error('email') is-invalid @enderror"
                            placeholder="Correo electrónico" required />
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <!--end::Input-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Row-->
                <div class="row mb-6">
                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::Label-->
                        <label class="form-label fw-bold text-gray-900">Contraseña</label>
                        <!--end::Label-->

                        <!--begin::Input-->
                        <input type="password" name="password"
                            class="form-control form-control-solid @error('password') is-invalid @enderror"
                            placeholder="Contraseña" required />
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <!--end::Input-->
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::Label-->
                        <label class="form-label fw-bold text-gray-900">Confirmar Contraseña</label>
                        <!--end::Label-->

                        <!--begin::Input-->
                        <input type="password" name="password_confirmation"
                            class="form-control form-control-solid"
                            placeholder="Confirmar contraseña" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Row-->
                <div class="row mb-6">
                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::Label-->
                        <label class="form-label fw-bold text-gray-900">Rol</label>
                        <!--end::Label-->

                        <!--begin::Select-->
                        <select name="role" class="form-select form-select-solid @error('role') is-invalid @enderror" required>
                            <option value="">Seleccionar rol</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                            <option value="moderator" {{ old('role') === 'moderator' ? 'selected' : '' }}>Moderador</option>
                            <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Usuario</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <!--end::Select-->
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::Label-->
                        <label class="form-label fw-bold text-gray-900">Estado</label>
                        <!--end::Label-->

                        <!--begin::Checkbox-->
                        <div class="form-check form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked />
                            <label class="form-check-label" for="is_active">
                                Activo
                            </label>
                        </div>
                        <!--end::Checkbox-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->
            </div>
            <!--end::Card body-->

            <!--begin::Card footer-->
            <div class="card-footer d-flex justify-content-end">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light me-3">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Usuario</button>
            </div>
            <!--end::Card footer-->
        </div>
        <!--end::Card-->
    </form>
    <!--end::Form-->
@endsection
