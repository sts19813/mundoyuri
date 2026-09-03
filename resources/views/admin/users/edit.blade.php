@extends('layouts.admin')

@section('title', 'Editar Usuario - Admin')

@section('toolbar')
    <!--begin::Page title-->
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            Editar Usuario
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
            <li class="breadcrumb-item text-muted">Editar</li>
        </ul>
        <!--end::Breadcrumb-->
    </div>
    <!--end::Page title-->
@endsection

@section('content')
    <!--begin::Form-->
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="form">
        @csrf
        @method('PUT')

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
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
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
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
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
                        <label class="form-label fw-bold text-gray-900">Nueva Contraseña (Opcional)</label>
                        <!--end::Label-->

                        <!--begin::Input-->
                        <input type="password" name="password"
                            class="form-control form-control-solid @error('password') is-invalid @enderror"
                            placeholder="Dejar en blanco para no cambiar" />
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
                            placeholder="Confirmar contraseña" />
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
                            @foreach($roles as $roleName)
                                <option value="{{ $roleName }}" {{ old('role', $user->role) === $roleName ? 'selected' : '' }}>
                                    {{ ucfirst($roleName) }}
                                </option>
                            @endforeach
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
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" 
                                {{ old('is_active', $user->is_active) ? 'checked' : '' }} />
                            <label class="form-check-label" for="is_active">
                                Activo
                            </label>
                        </div>
                        <!--end::Checkbox-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <div class="separator separator-dashed my-8"></div>

                <div class="mb-7">
                    <h3 class="fw-bold text-gray-900 mb-2">Perfil comunitario</h3>
                    <div class="text-muted">Gestiona visibilidad, procedencia histórica, rango e insignias sin exponer las notas privadas.</div>
                </div>

                <div class="row g-6 mb-6">
                    <div class="col-xl-6">
                        <label class="form-label fw-bold text-gray-900">Visibilidad</label>
                        <select name="profile_visibility" class="form-select form-select-solid @error('profile_visibility') is-invalid @enderror">
                            <option value="public" @selected(old('profile_visibility', $user->profile_visibility) === 'public')>Público</option>
                            <option value="members" @selected(old('profile_visibility', $user->profile_visibility) === 'members')>Solo miembros</option>
                            <option value="private" @selected(old('profile_visibility', $user->profile_visibility) === 'private')>Privado / oculto</option>
                        </select>
                        @error('profile_visibility')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-xl-6">
                        <label class="form-label fw-bold text-gray-900">Rango especial</label>
                        <select name="community_rank_id" class="form-select form-select-solid @error('community_rank_id') is-invalid @enderror">
                            <option value="">Rango automático</option>
                            @foreach($communityRanks as $communityRank)
                                <option value="{{ $communityRank->id }}" @selected((string) old('community_rank_id', $user->community_rank_id) === (string) $communityRank->id)>{{ $communityRank->name }}</option>
                            @endforeach
                        </select>
                        @error('community_rank_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-6 mb-6">
                    <div class="col-xl-6">
                        <input type="hidden" name="is_legacy" value="0">
                        <label class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_legacy" value="1" @checked((bool) old('is_legacy', $user->is_legacy))>
                            <span class="form-check-label">Miembro histórico de Mundo Yuri</span>
                        </label>
                    </div>
                    <div class="col-xl-6">
                        <input type="hidden" name="legacy_verified" value="0">
                        <label class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="legacy_verified" value="1" @checked((bool) old('legacy_verified', $user->legacy_verified))>
                            <span class="form-check-label">Identidad histórica verificada</span>
                        </label>
                    </div>
                </div>

                <div class="row g-6 mb-6">
                    <div class="col-xl-4">
                        <label class="form-label fw-bold text-gray-900">Fecha histórica de ingreso</label>
                        <input type="date" name="legacy_joined_at" value="{{ old('legacy_joined_at', $user->legacy_joined_at?->format('Y-m-d')) }}" class="form-control form-control-solid @error('legacy_joined_at') is-invalid @enderror">
                    </div>
                    <div class="col-xl-4">
                        <label class="form-label fw-bold text-gray-900">Fuente histórica</label>
                        <input type="text" name="legacy_source" maxlength="255" value="{{ old('legacy_source', $user->legacy_source) }}" class="form-control form-control-solid @error('legacy_source') is-invalid @enderror">
                    </div>
                    <div class="col-xl-4">
                        <label class="form-label fw-bold text-gray-900">Perfil reclamado</label>
                        <input type="date" name="profile_claimed_at" value="{{ old('profile_claimed_at', $user->profile_claimed_at?->format('Y-m-d')) }}" class="form-control form-control-solid @error('profile_claimed_at') is-invalid @enderror">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="form-label fw-bold text-gray-900">Notas históricas privadas</label>
                    <textarea name="legacy_notes" rows="4" maxlength="2000" class="form-control form-control-solid @error('legacy_notes') is-invalid @enderror">{{ old('legacy_notes', $user->legacy_notes) }}</textarea>
                    <div class="form-text">Nunca se muestran en el perfil público.</div>
                </div>

                <div>
                    <input type="hidden" name="community_badges_present" value="1">
                    <label class="form-label fw-bold text-gray-900 d-block">Insignias</label>
                    <div class="row g-4">
                        @forelse($communityBadges as $communityBadge)
                            <div class="col-md-6 col-xl-4">
                                <label class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="community_badges[]" value="{{ $communityBadge->id }}" @checked(in_array($communityBadge->id, old('community_badges', $user->badges->pluck('id')->all())))>
                                    <span class="form-check-label">{{ $communityBadge->icon }} {{ $communityBadge->name }}</span>
                                </label>
                            </div>
                        @empty
                            <div class="col-12 text-muted">No hay insignias activas.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <!--end::Card body-->

            <!--begin::Card footer-->
            <div class="card-footer d-flex justify-content-end">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light me-3">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
            <!--end::Card footer-->
        </div>
        <!--end::Card-->
    </form>
    <!--end::Form-->
@endsection
