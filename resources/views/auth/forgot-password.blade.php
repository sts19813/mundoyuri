@extends('layouts.auth')

@section('title', 'Recuperar contraseña | Holos')

@section('content')
    <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
        <!--begin::Card-->
        <div class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-20">
            <!--begin::Wrapper-->
            <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-15 pb-lg-20">

                <!--begin::Heading-->
                <div class="text-center mb-11">
                    <h1 class="text-gray-900 fw-bolder mb-3">
                        ¿Olvidaste tu contraseña?
                    </h1>
                    <div class="text-gray-500 fw-semibold fs-6">
                        Ingresa tu correo electrónico y te enviaremos un enlace para restablecerla.
                    </div>
                </div>
                <!--end::Heading-->

                <!--begin::Status-->
                @if (session('status'))
                    <div class="alert alert-success d-flex align-items-center mb-5">
                        <i class="ki-outline ki-check-circle fs-2 me-3"></i>
                        <div>
                            {{ session('status') }}
                        </div>
                    </div>
                @endif
                <!--end::Status-->

                <!--begin::Form-->
                <form method="POST" action="{{ route('password.email') }}" class="form w-100" novalidate>
                    @csrf

                    <!-- Email -->
                    <div class="fv-row mb-8">
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
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

                    <!-- Submit -->
                    <div class="d-grid mb-10">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">
                                Enviar enlace de recuperación
                            </span>
                        </button>
                    </div>

                    <!-- Back to login -->
                    <div class="text-gray-500 text-center fw-semibold fs-6">
                        ¿Recordaste tu contraseña?
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
