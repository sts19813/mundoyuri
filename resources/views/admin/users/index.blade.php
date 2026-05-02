@extends('layouts.admin')

@section('title', 'Gestión de Usuarios - Admin')

@section('toolbar')
    <!--begin::Page title-->
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            Usuarios
        </h1>
        <!--end::Title-->

        <!--begin::Breadcrumb-->
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 ms-2">
            <!--begin::Item-->
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Admin</a>
            </li>
            <!--end::Item-->

            <!--begin::Item-->
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-400 w-5px h-2px"></span>
            </li>
            <!--end::Item-->

            <!--begin::Item-->
            <li class="breadcrumb-item text-muted">Usuarios</li>
            <!--end::Item-->
        </ul>
        <!--end::Breadcrumb-->
    </div>
    <!--end::Page title-->
@endsection

@section('content')
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-3"></i>
                    <input type="text" id="searchInput" placeholder="Buscar usuario..."
                        class="form-control form-control-solid w-250px ps-9" />
                </div>
            </div>
            <!--end::Card title-->

            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
                    <i class="ki-outline ki-plus fs-2"></i>Nuevo Usuario
                </a>
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body pt-0">
            <!--begin::Table wrapper-->
            <div class="table-responsive">
                <!--begin::Table-->
                <table class="table table-row-dashed table-row-gray-300 gy-7" id="users_table">
                    <!--begin::Table head-->
                    <thead>
                        <tr class="fw-bold fs-6 text-gray-800 border-bottom border-gray-200">
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha de Registro</th>
                            <th class="min-w-100px">Acciones</th>
                        </tr>
                    </thead>
                    <!--end::Table head-->

                    <!--begin::Table body-->
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-50px me-5">
                                            <img alt="Avatar" src="{{ asset('/metronic/assets/media/avatars/300-2.jpg') }}" />
                                        </div>
                                        <div class="d-flex justify-content-start flex-column">
                                            <a href="{{ route('admin.users.show', $user->id) }}" class="text-dark fw-bold text-hover-primary fs-6">
                                                {{ $user->name }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge badge-light-primary">{{ $user->role }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'badge-light-success' : 'badge-light-danger' }}">
                                        {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-light btn-active-light-primary">
                                        Editar
                                    </a>
                                    @if($user->id !== Auth::id())
                                        <button class="btn btn-sm btn-light btn-active-light-danger" onclick="deleteUser({{ $user->id }})">
                                            Eliminar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10">
                                    <div class="text-gray-500">No hay usuarios registrados</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <!--end::Table body-->
                </table>
                <!--end::Table-->
            </div>
            <!--end::Table wrapper-->

            <!--begin::Pagination-->
            <div class="d-flex justify-content-center">
                {{ $users->links() }}
            </div>
            <!--end::Pagination-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
@endsection

@push('scripts')
    <script>
        function deleteUser(userId) {
            if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
                fetch(`/admin/users/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (response.ok) {
                        window.location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }
    </script>
@endpush
