@extends('layouts.auth')

@section('title', 'Confirmar contraseña | Holos')

@section('content')
    <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
        <!--begin::Card-->
        <div class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-20">
            <!--begin::Wrapper-->
            <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-15 pb-lg-20">

                <!--begin::Heading-->
                <div class="text-center mb-11">
                    <h1 class="text-gray-900 fw-bolder mb-3">
                        Confirmar contraseña
                    </h1>
                    <div class="text-gray-500 fw-semibold fs-6">
                        Esta es un área segura. Por favor confirma tu contraseña para continuar.
                    </div>
                </div>
                <!--end::Heading-->

                <!--begin::Form-->
                <form method="POST" action="{{ route('password.confirm') }}" class="form w-100" novalidate>
                    @csrf

                    <!-- Password -->
                    <div class="fv-row mb-8">
                        <input
                            type="password"
                            name="password"
                            placeholder="Contraseña actual"
                            autocomplete="current-password"
                            class="form-control bg-transparent @error('password') is-invalid @enderror"
                            required
                            autofocus
                        />
                        @error('password')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Submit -->
                    <div class="d-grid mb-10">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">
                                Confirmar
                            </span>
                        </button>
                    </div>

                </form>
                <!--end::Form-->
            </div>
            <!--end::Wrapper-->

            <!--begin::Footer-->
            <div class="d-flex flex-stack px-lg-10">

                <!--begin::Languages-->
                <div class="me-0">
                    <button
                        class="btn btn-flex btn-link btn-color-gray-700 btn-active-color-primary rotate fs-base"
                        data-kt-menu-trigger="click"
                        data-kt-menu-placement="bottom-start">
                        <img class="w-20px h-20px rounded me-3"
                             src="/metronic/assets/media/flags/mexico.svg" alt="MX" />
                        <span class="me-1">Español (MX)</span>
                        <i class="ki-outline ki-down fs-5 text-muted rotate-180 m-0"></i>
                    </button>
                </div>
                <!--end::Languages-->

                <!--begin::Links-->
                <div class="d-flex fw-semibold text-primary fs-base gap-5">
                    <a href="#" target="_blank">Términos</a>
                    <a href="#" target="_blank">Planes</a>
                    <a href="#" target="_blank">Contáctanos</a>
                </div>
                <!--end::Links-->

            </div>
            <!--end::Footer-->
        </div>
        <!--end::Card-->
    </div>
@endsection
