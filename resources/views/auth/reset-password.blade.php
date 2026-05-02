@extends('layouts.auth')

@section('title', 'Restablecer contraseña | Holos')

@section('content')
    <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
        <!--begin::Card-->
        <div class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-20">
            <!--begin::Wrapper-->
            <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-15 pb-lg-20">

                <!--begin::Heading-->
                <div class="text-center mb-11">
                    <h1 class="text-gray-900 fw-bolder mb-3">
                        Restablecer contraseña
                    </h1>
                    <div class="text-gray-500 fw-semibold fs-6">
                        Ingresa tu nueva contraseña para continuar
                    </div>
                </div>
                <!--end::Heading-->

                <!--begin::Form-->
                <form method="POST" action="{{ route('password.store') }}" class="form w-100" novalidate>
                    @csrf

                    <!-- Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email -->
                    <div class="fv-row mb-8">
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $request->email) }}"
                            placeholder="Correo electrónico"
                            autocomplete="username"
                            class="form-control bg-transparent @error('email') is-invalid @enderror"
                            required
                            autofocus
                        />
                        @error('email')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="fv-row mb-8">
                        <input
                            type="password"
                            name="password"
                            placeholder="Nueva contraseña"
                            autocomplete="new-password"
                            class="form-control bg-transparent @error('password') is-invalid @enderror"
                            required
                        />
                        @error('password')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="fv-row mb-8">
                        <input
                            type="password"
                            name="password_confirmation"
                            placeholder="Confirmar nueva contraseña"
                            autocomplete="new-password"
                            class="form-control bg-transparent @error('password_confirmation') is-invalid @enderror"
                            required
                        />
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Submit -->
                    <div class="d-grid mb-10">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">
                                Restablecer contraseña
                            </span>
                        </button>
                    </div>

                    <!-- Back to login -->
                    <div class="text-gray-500 text-center fw-semibold fs-6">
                        ¿Ya tienes una cuenta?
                        <a href="{{ route('login') }}" class="link-primary">
                            Inicia sesión
                        </a>
                    </div>

                </form>
                <!--end::Form-->
            </div>
            <!--end::Wrapper-->

            <!--begin::Footer-->
            <div class="d-flex flex-stack px-lg-10">
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

                <div class="d-flex fw-semibold text-primary fs-base gap-5">
                 
                </div>
            </div>
            <!--end::Footer-->
        </div>
        <!--end::Card-->
    </div>
@endsection
