@extends('layouts.admin')

@section('title', 'Ver Usuario - Admin')

@section('toolbar')
    <!--begin::Page title-->
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ $user->name }}
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
            <li class="breadcrumb-item text-muted">Ver</li>
        </ul>
        <!--end::Breadcrumb-->
    </div>
    <!--end::Page title-->
@endsection

@section('content')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
    <!--begin::Card-->
    <div class="card mb-5 mb-xl-10">
        <!--begin::Card header-->
        <div class="card-header border-0 cursor-pointer" role="button">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">Información del Usuario</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--begin::Card header-->

        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-bold fs-6">Nombre</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <div class="fw-bold">{{ $user->name }}</div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">Perfil comunitario</label>
                <div class="col-lg-8 d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge badge-light-info">{{ ucfirst($user->profile_visibility) }}</span>
                    @if($user->communityRank)<span class="badge badge-light-primary">{{ $user->communityRank->name }}</span>@endif
                    @if($user->is_legacy)<span class="badge badge-light-warning">Miembro histórico</span>@endif
                    @foreach($user->badges as $badge)
                        <span class="badge badge-light-success">{{ $badge->icon }} {{ $badge->name }}</span>
                    @endforeach
                </div>
            </div>

            @if($user->is_legacy)
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-bold fs-6">Datos históricos</label>
                    <div class="col-lg-8">
                        <div class="fw-semibold">Ingreso: {{ $user->legacy_joined_at?->format('d/m/Y') ?: 'Sin fecha' }}</div>
                        <div class="text-muted">Fuente: {{ $user->legacy_source ?: 'Sin especificar' }}</div>
                        <div class="text-muted">Verificado: {{ $user->legacy_verified ? 'Sí' : 'No' }}</div>
                        @if($user->legacy_notes)<div class="mt-3 p-3 bg-light rounded">{{ $user->legacy_notes }}</div>@endif
                    </div>
                </div>
            @endif

            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-bold fs-6">Email</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <div class="fw-bold">{{ $user->email }}</div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-bold fs-6">Rol</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <span class="badge badge-light-primary">{{ $user->role }}</span>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-bold fs-6">Estado</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <span class="badge {{ $user->is_active ? 'badge-light-success' : 'badge-light-danger' }}">
                        {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row mb-0">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-bold fs-6">Fecha de Registro</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <div class="fw-bold">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
        </div>
        <!--end::Card body-->

        <!--begin::Card footer-->
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary me-3">
                Editar
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-light">
                Volver
            </a>
        </div>
        <!--end::Card footer-->
    </div>
    <!--end::Card-->

    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <div>
                    <h3 class="fw-bold m-0">Insignias comunitarias</h3>
                    <div class="text-muted fs-7 mt-1">Reconocimientos independientes del rango y de los permisos.</div>
                </div>
            </div>
        </div>
        <div class="card-body border-top p-9">
            <form method="POST" action="{{ route('admin.users.badges.store', $user) }}" class="row g-4 align-items-end mb-8">
                @csrf
                <div class="col-lg-4">
                    <label class="form-label required">Insignia</label>
                    <select name="badge_id" required class="form-select form-select-solid @error('badge_id') is-invalid @enderror">
                        <option value="">Seleccionar…</option>
                        @foreach($availableBadges as $availableBadge)
                            <option value="{{ $availableBadge->id }}" @selected((string) old('badge_id') === (string) $availableBadge->id)>
                                {{ $availableBadge->icon }} {{ $availableBadge->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('badge_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Nota interna de la concesión</label>
                    <input type="text" name="note" value="{{ old('note') }}" maxlength="1000"
                        class="form-control form-control-solid @error('note') is-invalid @enderror" placeholder="Evidencia o contexto opcional">
                    @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-lg-2">
                    <button type="submit" class="btn btn-primary w-100">Asignar</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed gy-4">
                    <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Insignia</th><th>Otorgada por</th><th>Fecha</th><th>Nota interna</th><th class="text-end">Acción</th></tr></thead>
                    <tbody>
                        @forelse($user->badges as $badge)
                            <tr>
                                <td><span class="badge badge-light-info">{{ $badge->icon }} {{ $badge->name }}</span></td>
                                <td>{{ $badgeAwarders->get($badge->pivot->awarded_by, 'Sistema / dato importado') }}</td>
                                <td>{{ $badge->pivot->awarded_at?->format('d/m/Y H:i') ?: 'Sin fecha' }}</td>
                                <td class="text-muted">{{ $badge->pivot->note ?: '—' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.users.badges.destroy', [$user, $badge]) }}" onsubmit="return confirm('¿Retirar esta insignia?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light-danger">Retirar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-6">Esta persona no tiene insignias asignadas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
