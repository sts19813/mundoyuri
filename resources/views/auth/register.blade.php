@extends('layouts.auth')

@section('title', 'Crear cuenta | Holos')

@section('auth_content')
    <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
        <!--begin::Card-->
        <div class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-20">
            <!--begin::Wrapper-->
            <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-15 pb-lg-20">
                <!--begin::Form-->
                <form method="POST" action="{{ route('register') }}" class="form w-100" novalidate>
                    @csrf

                    {{-- HEADING --}}
                    <div class="text-center mb-11">
                        <h1 class="text-gray-900 fw-bolder mb-3">
                            Crear cuenta
                        </h1>
                        <div class="text-gray-500 fw-semibold fs-6">
                            Regístrate para acceder al sistema
                        </div>
                    </div>

                    {{-- ERRORES --}}
                    @if ($errors->any())
                        <div class="alert alert-danger d-flex align-items-center mb-5">
                            <i class="ki-outline ki-cross-circle fs-2 me-3"></i>
                            <div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- NAME --}}
                    <div class="fv-row mb-8">
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nombre completo"
                            class="form-control bg-transparent @error('name') is-invalid @enderror" required autofocus />
                        @error('name')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- EMAIL --}}
                    <div class="fv-row mb-8">
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Correo electrónico"
                            autocomplete="username"
                            class="form-control bg-transparent @error('email') is-invalid @enderror" required />
                        @error('email')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- PASSWORD --}}
                    <div class="fv-row mb-8">
                        <input type="password" name="password" placeholder="Contraseña"
                            autocomplete="new-password"
                            class="form-control bg-transparent @error('password') is-invalid @enderror" required />
                        @error('password')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- PASSWORD CONFIRM --}}
                    <div class="fv-row mb-8">
                        <input type="password" name="password_confirmation" placeholder="Confirmar contraseña"
                            autocomplete="new-password"
                            class="form-control bg-transparent" required />
                    </div>

                    {{-- SUBMIT --}}
                    <div class="d-grid mb-10">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">
                                Crear cuenta
                            </span>
                        </button>
                    </div>

                    {{-- LOGIN --}}
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
                <!--begin::Languages-->
                <div class="me-0">
                    <button class="btn btn-flex btn-link btn-color-gray-700 btn-active-color-primary rotate fs-base"
                        data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start"
                        data-kt-menu-offset="0px, 0px">
                        <img class="w-20px h-20px rounded me-3"
                            src="/metronic/assets/media/flags/mexico.svg" alt="MX" />
                        <span class="me-1">Español (MX)</span>
                        <i class="ki-outline ki-down fs-5 text-muted rotate-180 m-0"></i>
                    </button>
                </div>

                <!--begin::Links-->
                <div class="d-flex fw-semibold text-primary fs-base gap-5"></div>
                <!--end::Links-->
            </div>
            <!--end::Footer-->
        </div>
        <!--end::Card-->
    </div>
@endsection