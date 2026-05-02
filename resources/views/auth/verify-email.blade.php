@extends('layouts.auth')

@section('title', 'Verificar correo electrónico | Holos')

@section('content')
    <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
        <!--begin::Card-->
        <div class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-20">
            <!--begin::Wrapper-->
            <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-15 pb-lg-20">

                <!--begin::Heading-->
                <div class="text-center mb-11">
                    <h1 class="text-gray-900 fw-bolder mb-3">
                        Verifica tu correo electrónico
                    </h1>
                    <div class="text-gray-500 fw-semibold fs-6">
                        Antes de continuar, revisa tu bandeja de entrada y confirma tu correo electrónico.
                    </div>
                </div>
                <!--end::Heading-->

                <!--begin::Alert principal-->
                <div class="alert alert-info d-flex align-items-center mb-6">
                    <i class="ki-outline ki-information-5 fs-2 me-3"></i>
                    <div>
                        Te hemos enviado un enlace de verificación a tu correo electrónico.
                        Si no lo encuentras, revisa la carpeta de spam.
                    </div>
                </div>
                <!--end::Alert principal-->

                <!--begin::Status-->
                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success d-flex align-items-center mb-6">
                        <i class="ki-outline ki-check-circle fs-2 me-3"></i>
                        <div>
                            Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
                        </div>
                    </div>
                @endif
                <!--end::Status-->

                <!--begin::Actions-->
                <div class="d-flex flex-stack flex-wrap gap-3">

                    <!-- Reenviar verificación -->
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            Reenviar correo de verificación
                        </button>
                    </form>

                    <!-- Cerrar sesión -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-light-danger">
                            Cerrar sesión
                        </button>
                    </form>

                </div>
                <!--end::Actions-->

            </div>
            <!--end::Wrapper-->

            <!--begin::Footer-->
            <div class="d-flex flex-stack px-lg-10 mt-10">

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
                  
                </div>
                <!--end::Links--> 
            </div>
            <!--end::Footer-->
        </div>
        <!--end::Card-->
    </div>
@endsection
