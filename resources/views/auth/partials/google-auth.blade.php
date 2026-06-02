@php
    $label = $label ?? 'Continuar con Google';
    $intent = $intent ?? 'login';
@endphp

<div class="mb-8">
    <a href="{{ route('auth.google.redirect', ['intent' => $intent]) }}"
        class="btn google-auth-btn btn-flex flex-center rounded-3 w-100 py-3 px-4 fw-semibold">
        <img alt="Google" src="{{ asset('metronic/assets/media/svg/brand-logos/google-icon.svg') }}" class="h-20px me-3" />
        <span>{{ $label }}</span>
    </a>
</div>

<div class="separator separator-content my-10">
    <span class="text-gray-500 fw-semibold fs-7 text-uppercase">o con correo</span>
</div>
