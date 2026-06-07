@extends('layouts.admin')

@section('title', 'Mi Perfil')

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            Mi perfil
        </h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Panel</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-400 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">Cuenta</li>
        </ul>
    </div>
@endsection

@push('styles')
    <style>
        .profile-cover {
            background:
                linear-gradient(135deg, rgba(119, 35, 255, .12), rgba(20, 184, 166, .12)),
                var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
        }

        .profile-avatar-preview {
            width: 118px;
            height: 118px;
            object-fit: cover;
        }

        .profile-avatar-action {
            position: absolute;
            right: 2px;
            bottom: 8px;
        }
    </style>
@endpush

@section('content')
    <div class="profile-cover rounded p-6 p-lg-8 mb-6">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-5">
            <div class="d-flex align-items-center gap-5 min-w-0">
                <div class="symbol symbol-75px symbol-lg-90px flex-shrink-0">
                    <img src="{{ $user->avatarUrl() }}" alt="Avatar de {{ $user->name }}" data-profile-avatar>
                </div>
                <div class="min-w-0">
                    <h2 class="fw-bold text-gray-900 mb-1 text-truncate" data-profile-name>{{ $user->name }}</h2>
                    <div class="text-muted fw-semibold text-truncate" data-profile-email>{{ $user->email }}</div>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <span class="badge badge-light-primary">{{ ucfirst($user->role) }}</span>
                        @if($user->alias)
                            <span class="badge badge-light" data-profile-alias>{{ $user->alias }}</span>
                        @else
                            <span class="badge badge-light d-none" data-profile-alias></span>
                        @endif
                    </div>
                </div>
            </div>
            <a href="{{ route('home') }}" class="btn btn-light-primary">
                <i class="ki-outline ki-abstract-26 fs-2"></i>
                Ir al portal
            </a>
        </div>
    </div>

    <div class="row g-6 g-xl-9">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header border-0">
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">Datos de cuenta</h3>
                    </div>
                </div>

                <form id="profile-details-form" class="form" action="{{ route('admin.profile.update') }}" method="POST"
                    enctype="multipart/form-data" data-ajax-profile-form>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="avatar_remove" value="0" data-avatar-remove>

                    <div class="card-body border-top p-9">
                        <div class="row mb-8">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Foto de perfil</label>
                            <div class="col-lg-8">
                                <div class="position-relative d-inline-block">
                                    <img src="{{ $user->avatarUrl() }}" alt="Avatar de {{ $user->name }}"
                                        class="rounded profile-avatar-preview border border-3 border-body shadow-sm"
                                        data-profile-avatar data-avatar-preview>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-30px h-30px bg-body shadow profile-avatar-action"
                                        data-bs-toggle="tooltip" title="Cambiar foto">
                                        <i class="ki-outline ki-pencil fs-3"></i>
                                        <input type="file" name="profile_image" accept=".png,.jpg,.jpeg,.webp" class="d-none"
                                            data-avatar-input>
                                    </label>
                                </div>
                                <button type="button" class="btn btn-sm btn-light-danger ms-3" data-avatar-clear>
                                    <i class="ki-outline ki-trash fs-3"></i>
                                    Quitar
                                </button>
                                <div class="invalid-feedback d-block" data-error-for="profile_image"></div>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nombre</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                    class="form-control form-control-lg form-control-solid" autocomplete="name" required>
                                <div class="invalid-feedback" data-error-for="name"></div>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Alias</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="alias" value="{{ old('alias', $user->alias) }}"
                                    class="form-control form-control-lg form-control-solid" autocomplete="nickname">
                                <div class="invalid-feedback" data-error-for="alias"></div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Email</label>
                            <div class="col-lg-8 fv-row">
                                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                    class="form-control form-control-lg form-control-solid" autocomplete="email" required>
                                <div class="invalid-feedback" data-error-for="email"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="submit" class="btn btn-primary" data-ajax-submit>
                            <span class="indicator-label">Guardar cambios</span>
                            <span class="indicator-progress">Guardando...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-6">
                <div class="card-header border-0">
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">Resumen</h3>
                    </div>
                </div>
                <div class="card-body border-top p-9">
                    <div class="d-flex align-items-center mb-7">
                        <span class="symbol symbol-45px me-4">
                            <span class="symbol-label bg-light-primary">
                                <i class="ki-outline ki-profile-circle fs-2 text-primary"></i>
                            </span>
                        </span>
                        <div>
                            <div class="fw-bold text-gray-900">Rol</div>
                            <div class="text-muted">{{ ucfirst($user->role) }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-7">
                        <span class="symbol symbol-45px me-4">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-outline ki-verify fs-2 text-success"></i>
                            </span>
                        </span>
                        <div>
                            <div class="fw-bold text-gray-900">Email</div>
                            <div class="text-muted">{{ $user->email_verified_at ? 'Verificado' : 'Pendiente' }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="symbol symbol-45px me-4">
                            <span class="symbol-label bg-light-info">
                                <i class="ki-outline ki-calendar fs-2 text-info"></i>
                            </span>
                        </span>
                        <div>
                            <div class="fw-bold text-gray-900">Alta</div>
                            <div class="text-muted">{{ optional($user->created_at)->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-0">
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">Contraseña</h3>
                    </div>
                </div>

                <form id="profile-password-form" class="form" action="{{ route('admin.profile.updatePassword') }}"
                    method="POST" data-ajax-profile-form data-reset-on-success>
                    @csrf
                    @method('PUT')

                    <div class="card-body border-top p-9">
                        <div class="mb-6">
                            <label class="form-label required fw-semibold">Contraseña actual</label>
                            <input type="password" name="current_password" class="form-control form-control-solid"
                                autocomplete="current-password" required>
                            <div class="invalid-feedback" data-error-for="current_password"></div>
                        </div>

                        <div class="mb-6">
                            <label class="form-label required fw-semibold">Nueva contraseña</label>
                            <input type="password" name="password" class="form-control form-control-solid"
                                autocomplete="new-password" required>
                            <div class="invalid-feedback" data-error-for="password"></div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label required fw-semibold">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" class="form-control form-control-solid"
                                autocomplete="new-password" required>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="submit" class="btn btn-primary" data-ajax-submit>
                            <span class="indicator-label">Actualizar</span>
                            <span class="indicator-progress">Actualizando...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const avatarInput = document.querySelector('[data-avatar-input]');
            const avatarRemove = document.querySelector('[data-avatar-remove]');
            const avatarClear = document.querySelector('[data-avatar-clear]');
            const clearedAvatar = @js($user->google_avatar ?: asset('metronic/assets/media/avatars/blank.png'));

            function clearErrors(form) {
                form.querySelectorAll('.is-invalid').forEach((input) => input.classList.remove('is-invalid'));
                form.querySelectorAll('[data-error-for]').forEach((error) => {
                    error.textContent = '';
                    error.classList.remove('d-block');
                });
            }

            function setError(form, field, message) {
                const input = form.querySelector(`[name="${field}"]`);
                const error = form.querySelector(`[data-error-for="${field}"]`);

                if (input) {
                    input.classList.add('is-invalid');
                }

                if (error) {
                    error.textContent = message;
                    error.classList.add('d-block');
                }
            }

            function setLoading(button, loading) {
                if (!button) {
                    return;
                }

                button.disabled = loading;
                button.setAttribute('data-kt-indicator', loading ? 'on' : 'off');
            }

            function updateProfileView(user) {
                if (!user) {
                    return;
                }

                document.querySelectorAll('[data-profile-name]').forEach((node) => node.textContent = user.name);
                document.querySelectorAll('[data-profile-email]').forEach((node) => node.textContent = user.email);
                document.querySelectorAll('[data-profile-avatar]').forEach((image) => image.src = user.avatar_url);

                document.querySelectorAll('[data-profile-alias]').forEach((node) => {
                    node.textContent = user.alias || '';
                    node.classList.toggle('d-none', !user.alias);
                });
            }

            if (avatarInput) {
                avatarInput.addEventListener('change', function () {
                    const file = this.files && this.files[0];

                    if (!file) {
                        return;
                    }

                    avatarRemove.value = '0';
                    const url = URL.createObjectURL(file);
                    document.querySelectorAll('[data-profile-avatar]').forEach((image) => image.src = url);
                });
            }

            if (avatarClear) {
                avatarClear.addEventListener('click', function () {
                    avatarRemove.value = '1';

                    if (avatarInput) {
                        avatarInput.value = '';
                    }

                    document.querySelectorAll('[data-profile-avatar]').forEach((image) => image.src = clearedAvatar);
                });
            }

            document.querySelectorAll('[data-ajax-profile-form]').forEach((form) => {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    clearErrors(form);

                    const submitButton = form.querySelector('[data-ajax-submit]');
                    setLoading(submitButton, true);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: new FormData(form),
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            if (payload.errors) {
                                Object.entries(payload.errors).forEach(([field, messages]) => {
                                    setError(form, field, messages[0]);
                                });
                            }

                            throw new Error(payload.message || 'Revisa los campos marcados.');
                        }

                        updateProfileView(payload.user);
                        adminToast('success', payload.message || 'Cambios guardados.');

                        if (form.hasAttribute('data-reset-on-success')) {
                            form.reset();
                        }

                        if (avatarRemove) {
                            avatarRemove.value = '0';
                        }

                        if (avatarInput) {
                            avatarInput.value = '';
                        }
                    } catch (error) {
                        adminToast('error', error.message || 'No se pudo guardar.');
                    } finally {
                        setLoading(submitButton, false);
                    }
                });
            });
        });
    </script>
@endpush
