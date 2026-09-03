@extends('layouts.admin')

@section('title', 'Usuarios, roles y permisos - Admin')

@php
    $activeTab = request('tab', 'users');
    if (!in_array($activeTab, ['users', 'roles', 'permissions'], true)) {
        $activeTab = 'users';
    }
@endphp

@section('toolbar')
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            Usuarios y permisos
        </h1>

        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 ms-2">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Admin</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-400 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">Usuarios y permisos</li>
        </ul>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#permissionModal">
            <i class="ki-outline ki-shield fs-2"></i>Permiso
        </button>
        <button type="button" class="btn btn-sm btn-light-primary" data-action="new-role">
            <i class="ki-outline ki-security-user fs-2"></i>Rol
        </button>
        <button type="button" class="btn btn-sm btn-primary" data-action="new-user">
            <i class="ki-outline ki-plus fs-2"></i>Usuario
        </button>
    </div>
@endsection

@section('content')
    <div class="row g-5 g-xl-8 mb-6">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-4">
                    <div class="symbol symbol-50px">
                        <span class="symbol-label bg-light-primary">
                            <i class="ki-outline ki-profile-user fs-2x text-primary"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-gray-900" data-stat="users">0</div>
                        <div class="text-muted fw-semibold">Usuarios</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-4">
                    <div class="symbol symbol-50px">
                        <span class="symbol-label bg-light-success">
                            <i class="ki-outline ki-security-user fs-2x text-success"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-gray-900" data-stat="roles">0</div>
                        <div class="text-muted fw-semibold">Roles</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-4">
                    <div class="symbol symbol-50px">
                        <span class="symbol-label bg-light-info">
                            <i class="ki-outline ki-shield-tick fs-2x text-info"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-gray-900" data-stat="permissions">0</div>
                        <div class="text-muted fw-semibold">Permisos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <ul class="nav nav-tabs nav-line-tabs fw-semibold fs-6" id="accessTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'users' ? 'active' : '' }}" data-bs-toggle="tab"
                            data-bs-target="#users-tab-pane" type="button" role="tab" data-tab-key="users">
                            Usuarios
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'roles' ? 'active' : '' }}" data-bs-toggle="tab"
                            data-bs-target="#roles-tab-pane" type="button" role="tab" data-tab-key="roles">
                            Roles
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'permissions' ? 'active' : '' }}" data-bs-toggle="tab"
                            data-bs-target="#permissions-tab-pane" type="button" role="tab" data-tab-key="permissions">
                            Permisos
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card-body pt-4">
            <div class="tab-content">
                <div class="tab-pane fade {{ $activeTab === 'users' ? 'show active' : '' }}" id="users-tab-pane" role="tabpanel">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-5">
                        <div class="d-flex align-items-center position-relative w-100 w-md-350px">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                            <input type="text" class="form-control form-control-solid ps-12" data-search="users"
                                placeholder="Buscar usuario, email o rol">
                        </div>
                        <button type="button" class="btn btn-primary" data-action="new-user">
                            <i class="ki-outline ki-plus fs-2"></i>Nuevo usuario
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Permisos directos</th>
                                    <th>Estado</th>
                                    <th>Correos</th>
                                    <th>Última sesión</th>
                                    <th>Registro</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'roles' ? 'show active' : '' }}" id="roles-tab-pane" role="tabpanel">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-5">
                        <div class="d-flex align-items-center position-relative w-100 w-md-350px">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                            <input type="text" class="form-control form-control-solid ps-12" data-search="roles"
                                placeholder="Buscar rol o permiso">
                        </div>
                        <button type="button" class="btn btn-primary" data-action="new-role">
                            <i class="ki-outline ki-plus fs-2"></i>Crear rol
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                                    <th>Rol</th>
                                    <th>Usuarios</th>
                                    <th>Permisos asignados</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="rolesTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'permissions' ? 'show active' : '' }}" id="permissions-tab-pane" role="tabpanel">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-5">
                        <div class="d-flex align-items-center position-relative w-100 w-md-350px">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                            <input type="text" class="form-control form-control-solid ps-12" data-search="permissions"
                                placeholder="Buscar permiso">
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#permissionModal">
                            <i class="ki-outline ki-plus fs-2"></i>Crear permiso
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                                    <th>Permiso</th>
                                    <th>Roles</th>
                                    <th>Usuarios directos</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="permissionsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="userForm" method="POST">
                    <input type="hidden" name="_method" value="POST" data-method-field>

                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalTitle">Nuevo usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-danger d-none" data-form-alert></div>

                        <div class="row g-5 mb-7">
                            <div class="col-lg-6">
                                <label class="form-label required">Nombre</label>
                                <input type="text" name="name" class="form-control form-control-solid" required>
                                <div class="invalid-feedback" data-error-for="name"></div>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label required">Email</label>
                                <input type="email" name="email" class="form-control form-control-solid" required>
                                <div class="invalid-feedback" data-error-for="email"></div>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label" id="passwordLabel">Contraseña</label>
                                <input type="password" name="password" class="form-control form-control-solid" autocomplete="new-password">
                                <div class="invalid-feedback" data-error-for="password"></div>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">Confirmar contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control form-control-solid" autocomplete="new-password">
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label required">Rol</label>
                                <select name="role" class="form-select form-select-solid" required></select>
                                <div class="invalid-feedback" data-error-for="role"></div>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label d-block">Estado</label>
                                <label class="form-check form-switch form-check-custom form-check-solid mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                    <span class="form-check-label">Activo</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <h6 class="fw-bold text-gray-900 mb-4">Permisos directos</h6>
                            <input type="hidden" name="permissions_present" value="1">
                            <div class="row g-3" data-permissions-checklist="user"></div>
                        </div>

                        <div class="mt-8 pt-7 border-top" data-community-fields>
                            <h6 class="fw-bold text-gray-900 mb-4">Perfil comunitario</h6>
                            <div class="row g-5">
                                <div class="col-lg-6">
                                    <label class="form-label">Visibilidad</label>
                                    <select name="profile_visibility" class="form-select form-select-solid">
                                        <option value="public">Público</option>
                                        <option value="members">Solo miembros</option>
                                        <option value="private">Privado / oculto</option>
                                    </select>
                                    <div class="invalid-feedback" data-error-for="profile_visibility"></div>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">Rango especial</label>
                                    <select name="community_rank_id" class="form-select form-select-solid">
                                        <option value="">Rango automático</option>
                                        @foreach($communityRanks as $communityRank)
                                            <option value="{{ $communityRank->id }}">{{ $communityRank->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" data-error-for="community_rank_id"></div>
                                </div>
                                <div class="col-lg-6">
                                    <input type="hidden" name="is_legacy" value="0">
                                    <label class="form-check form-switch form-check-custom form-check-solid mt-8">
                                        <input class="form-check-input" type="checkbox" name="is_legacy" value="1">
                                        <span class="form-check-label">Miembro histórico</span>
                                    </label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="hidden" name="legacy_verified" value="0">
                                    <label class="form-check form-switch form-check-custom form-check-solid mt-8">
                                        <input class="form-check-input" type="checkbox" name="legacy_verified" value="1">
                                        <span class="form-check-label">Identidad histórica verificada</span>
                                    </label>
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label">Fecha histórica</label>
                                    <input type="date" name="legacy_joined_at" class="form-control form-control-solid">
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label">Fuente histórica</label>
                                    <input type="text" name="legacy_source" class="form-control form-control-solid" maxlength="255">
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label">Perfil reclamado</label>
                                    <input type="date" name="profile_claimed_at" class="form-control form-control-solid">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notas históricas privadas</label>
                                    <textarea name="legacy_notes" class="form-control form-control-solid" rows="3" maxlength="2000"></textarea>
                                    <div class="form-text">Solo visibles en administración; nunca se publican en el perfil.</div>
                                </div>
                                <div class="col-12">
                                    <input type="hidden" name="community_badges_present" value="1">
                                    <label class="form-label d-block">Insignias</label>
                                    <div class="row g-3">
                                        @forelse($communityBadges as $communityBadge)
                                            <div class="col-md-6 col-xl-4">
                                                <label class="form-check form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" name="community_badges[]" value="{{ $communityBadge->id }}">
                                                    <span class="form-check-label">{{ $communityBadge->icon }} {{ $communityBadge->name }}</span>
                                                </label>
                                            </div>
                                        @empty
                                            <div class="col-12 text-muted">No hay insignias activas.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" data-submit-text="Guardar usuario">Guardar usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="roleForm" method="POST">
                    <input type="hidden" name="_method" value="POST" data-method-field>

                    <div class="modal-header">
                        <h5 class="modal-title" id="roleModalTitle">Crear rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-danger d-none" data-form-alert></div>

                        <div class="mb-6">
                            <label class="form-label required">Nombre del rol</label>
                            <input type="text" name="name" class="form-control form-control-solid" required>
                            <div class="invalid-feedback" data-error-for="name"></div>
                        </div>

                        <div>
                            <h6 class="fw-bold text-gray-900 mb-4">Permisos asignados</h6>
                            <div class="row g-3" data-permissions-checklist="role"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" data-submit-text="Guardar rol">Guardar rol</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="permissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="permissionForm" method="POST" action="{{ route('admin.permissions.store') }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear permiso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" data-form-alert></div>
                        <label class="form-label required">Nombre del permiso</label>
                        <input type="text" name="name" class="form-control form-control-solid" required>
                        <div class="invalid-feedback" data-error-for="name"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" data-submit-text="Crear permiso">Crear permiso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let users = @js($usersPayload);
        let roles = @js($rolesPayload);
        let permissions = @js($permissionsPayload);

        const authUserId = {{ (int) auth()->id() }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const routes = {
            usersStore: @js(route('admin.users.store')),
            usersUpdate: @js(route('admin.users.update', ['user' => '__ID__'])),
            usersDestroy: @js(route('admin.users.destroy', ['user' => '__ID__'])),
            usersEmailNotificationsUpdate: @js(route('admin.users.email-notifications.update', ['user' => '__ID__'])),
            rolesStore: @js(route('admin.roles.store')),
            rolesUpdate: @js(route('admin.roles.update', ['role' => '__ID__'])),
            rolesDestroy: @js(route('admin.roles.destroy', ['role' => '__ID__'])),
            permissionsDestroy: @js(route('admin.permissions.destroy', ['permission' => '__ID__'])),
        };

        const filters = { users: '', roles: '', permissions: '' };
        const userModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('userModal'));
        const roleModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('roleModal'));
        const permissionModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('permissionModal'));
        const userForm = document.getElementById('userForm');
        const roleForm = document.getElementById('roleForm');
        const permissionForm = document.getElementById('permissionForm');

        const endpoint = (template, id) => template.replace('__ID__', id);
        const normalize = (value) => String(value || '').toLowerCase();
        const titleCase = (value) => String(value || '').replace(/-/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        })[char]);

        const toast = (type, message) => {
            if (window.adminToast) {
                window.adminToast(type, message);
                return;
            }
            alert(message);
        };

        const badges = (items, variant = 'primary', emptyText = 'Sin permisos') => {
            if (!items || !items.length) {
                return `<span class="text-muted">${emptyText}</span>`;
            }

            return items.map((item) => `<span class="badge badge-light-${variant} me-1 mb-1">${escapeHtml(item)}</span>`).join('');
        };

        const roleUserCount = (roleName) => users.filter((user) => user.role === roleName).length;
        const permissionRoleCount = (permissionName) => roles.filter((role) => (role.permissions || []).includes(permissionName)).length;
        const permissionUserCount = (permissionName) => users.filter((user) => (user.permissions || []).includes(permissionName)).length;

        const renderStats = () => {
            document.querySelector('[data-stat="users"]').textContent = users.length;
            document.querySelector('[data-stat="roles"]').textContent = roles.length;
            document.querySelector('[data-stat="permissions"]').textContent = permissions.length;
        };

        const renderRoleOptions = (selected = '') => {
            const options = ['<option value="">Seleccionar rol</option>']
                .concat(roles.map((role) => {
                    const isSelected = role.name === selected ? 'selected' : '';
                    return `<option value="${escapeHtml(role.name)}" ${isSelected}>${escapeHtml(titleCase(role.name))}</option>`;
                }))
                .join('');

            userForm.querySelector('[name="role"]').innerHTML = options;
        };

        const renderPermissionChecklist = (target, selected = []) => {
            const container = document.querySelector(`[data-permissions-checklist="${target}"]`);

            if (!container) {
                return;
            }

            if (!permissions.length) {
                container.innerHTML = '<div class="col-12 text-muted">Sin permisos registrados</div>';
                return;
            }

            const selectedSet = new Set(selected || []);
            container.innerHTML = permissions.map((permission) => {
                const checked = selectedSet.has(permission.name) ? 'checked' : '';
                return `
                    <div class="col-md-6 col-xl-4">
                        <label class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="${escapeHtml(permission.name)}" ${checked}>
                            <span class="form-check-label">${escapeHtml(permission.name)}</span>
                        </label>
                    </div>
                `;
            }).join('');
        };

        const renderUsers = () => {
            const tbody = document.getElementById('usersTableBody');
            const query = normalize(filters.users);
            const visibleUsers = users.filter((user) => {
                const haystack = normalize(`${user.name} ${user.email} ${user.role} ${user.community_rank || ''} ${(user.permissions || []).join(' ')}`);
                return !query || haystack.includes(query);
            });

            tbody.innerHTML = visibleUsers.map((user) => {
                const role = user.role || 'Sin rol';
                const deleteButton = user.id === authUserId
                    ? ''
                    : `<button type="button" class="btn btn-sm btn-light-danger" data-action="delete-user" data-id="${user.id}">
                        <i class="ki-outline ki-trash fs-4"></i>Eliminar
                    </button>`;

                return `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-4">
                                    <img alt="${escapeHtml(user.name)}" src="${escapeHtml(user.avatar_url)}">
                                </div>
                                <div>
                                    <div class="fw-bold text-gray-900">${escapeHtml(user.name)}</div>
                                    <div class="text-muted">${escapeHtml(user.email)}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-light-primary">${escapeHtml(titleCase(role))}</span></td>
                        <td>${badges(user.permissions || [], 'info', 'Sin permisos directos')}</td>
                        <td>
                            <span class="badge ${user.is_active ? 'badge-light-success' : 'badge-light-danger'}">
                                ${user.is_active ? 'Activo' : 'Inactivo'}
                            </span>
                            <div class="mt-1">
                                <span class="badge badge-light-secondary">${escapeHtml(titleCase(user.profile_visibility || 'public'))}</span>
                                ${user.is_legacy ? '<span class="badge badge-light-warning">Histórico</span>' : ''}
                            </div>
                        </td>
                        <td>
                            <label class="form-check form-switch form-check-custom form-check-solid mb-0">
                                <input class="form-check-input h-20px w-30px" type="checkbox"
                                    data-action="toggle-email-notifications" data-id="${user.id}"
                                    aria-label="Notificaciones por correo de ${escapeHtml(user.name)}"
                                    ${user.email_notifications_enabled ? 'checked' : ''}>
                                <span class="form-check-label badge ${user.email_notifications_enabled ? 'badge-light-success' : 'badge-light-secondary'}">
                                    ${user.email_notifications_enabled ? 'Habilitados' : 'Desactivados'}
                                </span>
                            </label>
                        </td>
                        <td>
                            ${user.last_login_at
                                ? `<span class="fw-semibold text-gray-800 text-nowrap">${escapeHtml(user.last_login_at)}</span>`
                                : '<span class="text-muted">Sin acceso registrado</span>'}
                        </td>
                        <td>${escapeHtml(user.created_at || '-')}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-light-primary me-1" data-action="edit-user" data-id="${user.id}">
                                <i class="ki-outline ki-pencil fs-4"></i>Editar
                            </button>
                            ${deleteButton}
                        </td>
                    </tr>
                `;
            }).join('') || '<tr><td colspan="8" class="text-center text-muted py-10">No hay usuarios</td></tr>';
        };

        const renderRoles = () => {
            const tbody = document.getElementById('rolesTableBody');
            const query = normalize(filters.roles);
            const visibleRoles = roles.filter((role) => {
                const haystack = normalize(`${role.name} ${(role.permissions || []).join(' ')}`);
                return !query || haystack.includes(query);
            });

            tbody.innerHTML = visibleRoles.map((role) => {
                const usersCount = roleUserCount(role.name);
                const canDelete = role.name !== 'admin' && usersCount === 0;
                const deleteButton = canDelete
                    ? `<button type="button" class="btn btn-sm btn-light-danger" data-action="delete-role" data-id="${role.id}">
                        <i class="ki-outline ki-trash fs-4"></i>Eliminar
                    </button>`
                    : `<button type="button" class="btn btn-sm btn-light" disabled>
                        <i class="ki-outline ki-lock fs-4"></i>Eliminar
                    </button>`;

                return `
                    <tr>
                        <td>
                            <div class="fw-bold text-gray-900">${escapeHtml(titleCase(role.name))}</div>
                            <div class="text-muted">${escapeHtml(role.name)}</div>
                        </td>
                        <td><span class="badge badge-light">${usersCount}</span></td>
                        <td>${badges(role.permissions || [], 'primary')}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-light-primary me-1" data-action="edit-role" data-id="${role.id}">
                                <i class="ki-outline ki-pencil fs-4"></i>Editar
                            </button>
                            ${deleteButton}
                        </td>
                    </tr>
                `;
            }).join('') || '<tr><td colspan="4" class="text-center text-muted py-10">No hay roles</td></tr>';
        };

        const renderPermissions = () => {
            const tbody = document.getElementById('permissionsTableBody');
            const query = normalize(filters.permissions);
            const visiblePermissions = permissions.filter((permission) => !query || normalize(permission.name).includes(query));

            tbody.innerHTML = visiblePermissions.map((permission) => `
                <tr>
                    <td><span class="fw-bold text-gray-900">${escapeHtml(permission.name)}</span></td>
                    <td><span class="badge badge-light-primary">${permissionRoleCount(permission.name)}</span></td>
                    <td><span class="badge badge-light-info">${permissionUserCount(permission.name)}</span></td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-light-danger" data-action="delete-permission" data-id="${permission.id}">
                            <i class="ki-outline ki-trash fs-4"></i>Eliminar
                        </button>
                    </td>
                </tr>
            `).join('') || '<tr><td colspan="4" class="text-center text-muted py-10">No hay permisos</td></tr>';
        };

        const renderAll = () => {
            renderStats();
            renderUsers();
            renderRoles();
            renderPermissions();
        };

        const clearFormErrors = (form) => {
            form.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach((feedback) => feedback.textContent = '');
            const alert = form.querySelector('[data-form-alert]');
            if (alert) {
                alert.classList.add('d-none');
                alert.textContent = '';
            }
        };

        const resetForm = (form) => {
            form.reset();
            clearFormErrors(form);
        };

        const setFormErrors = (form, errors, fallback) => {
            const alert = form.querySelector('[data-form-alert]');
            const messages = [];

            Object.entries(errors || {}).forEach(([field, fieldMessages]) => {
                const baseField = field.replace(/\.\d+$/, '');
                const input = form.querySelector(`[name="${baseField}"], [name="${baseField}[]"]`);
                const feedback = form.querySelector(`[data-error-for="${baseField}"]`);
                const message = Array.isArray(fieldMessages) ? fieldMessages[0] : fieldMessages;

                messages.push(message);

                if (input) {
                    input.classList.add('is-invalid');
                }

                if (feedback) {
                    feedback.textContent = message;
                }
            });

            if (alert) {
                alert.textContent = messages[0] || fallback || 'Revisa los campos marcados.';
                alert.classList.remove('d-none');
            }
        };

        const submitAjax = async (form, onSuccess) => {
            clearFormErrors(form);
            const submitButton = form.querySelector('[type="submit"]');
            const originalText = submitButton?.innerHTML;

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando';
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: new FormData(form),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    setFormErrors(form, data.errors || {}, data.message);
                    return;
                }

                onSuccess(data);
                toast('success', data.message || 'Cambios guardados.');
            } catch (error) {
                setFormErrors(form, {}, 'No se pudo completar la operación.');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            }
        };

        const destroyAjax = async (url, message, onSuccess) => {
            if (typeof Swal === 'undefined') {
                if (!confirm(message)) {
                    return;
                }

                const body = new FormData();
                body.append('_method', 'DELETE');

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body,
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    toast('error', data.message || 'No se pudo eliminar.');
                    return;
                }

                onSuccess(data);
                toast('success', data.message || 'Elemento eliminado.');
                return;
            }

            const result = await Swal.fire({
                title: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-light',
                },
                buttonsStyling: false,
            });

            if (!result.isConfirmed) {
                return;
            }

            const body = new FormData();
            body.append('_method', 'DELETE');

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body,
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                toast('error', data.message || 'No se pudo eliminar.');
                return;
            }

            onSuccess(data);
            toast('success', data.message || 'Elemento eliminado.');
        };

        const openUserModal = (user = null) => {
            resetForm(userForm);
            renderRoleOptions(user?.role || '');
            renderPermissionChecklist('user', user?.permissions || []);

            userForm.action = user ? endpoint(routes.usersUpdate, user.id) : routes.usersStore;
            userForm.querySelector('[data-method-field]').value = user ? 'PUT' : 'POST';
            document.getElementById('userModalTitle').textContent = user ? 'Editar usuario' : 'Nuevo usuario';
            document.getElementById('passwordLabel').textContent = user ? 'Nueva contraseña' : 'Contraseña';

            userForm.querySelector('[name="password"]').required = !user;
            userForm.querySelector('[name="password_confirmation"]').required = !user;

            const communityFields = userForm.querySelector('[data-community-fields]');
            communityFields.classList.toggle('d-none', !user);
            communityFields.querySelectorAll('input, select, textarea').forEach((field) => {
                field.disabled = !user;
            });

            if (user) {
                userForm.querySelector('[name="name"]').value = user.name || '';
                userForm.querySelector('[name="email"]').value = user.email || '';
                userForm.querySelector('[name="is_active"]').checked = Boolean(user.is_active);
                userForm.querySelector('[name="profile_visibility"]').value = user.profile_visibility || 'public';
                userForm.querySelector('[name="community_rank_id"]').value = user.community_rank_id || '';
                userForm.querySelector('[name="is_legacy"][type="checkbox"]').checked = Boolean(user.is_legacy);
                userForm.querySelector('[name="legacy_verified"][type="checkbox"]').checked = Boolean(user.legacy_verified);
                userForm.querySelector('[name="legacy_joined_at"]').value = user.legacy_joined_at || '';
                userForm.querySelector('[name="legacy_source"]').value = user.legacy_source || '';
                userForm.querySelector('[name="legacy_notes"]').value = user.legacy_notes || '';
                userForm.querySelector('[name="profile_claimed_at"]').value = user.profile_claimed_at || '';

                const selectedBadges = new Set((user.community_badges || []).map(Number));
                userForm.querySelectorAll('[name="community_badges[]"]').forEach((field) => {
                    field.checked = selectedBadges.has(Number(field.value));
                });
            } else {
                userForm.querySelector('[name="is_active"]').checked = true;
            }

            userModal.show();
        };

        const openRoleModal = (role = null) => {
            resetForm(roleForm);
            renderPermissionChecklist('role', role?.permissions || []);

            roleForm.action = role ? endpoint(routes.rolesUpdate, role.id) : routes.rolesStore;
            roleForm.querySelector('[data-method-field]').value = role ? 'PUT' : 'POST';
            document.getElementById('roleModalTitle').textContent = role ? 'Editar rol' : 'Crear rol';

            if (role) {
                roleForm.querySelector('[name="name"]').value = role.name || '';
            }

            roleModal.show();
        };

        userForm.addEventListener('submit', (event) => {
            event.preventDefault();
            submitAjax(userForm, (data) => {
                const index = users.findIndex((user) => user.id === data.user.id);
                if (index >= 0) {
                    users[index] = data.user;
                } else {
                    users.unshift(data.user);
                }

                userModal.hide();
                renderAll();
            });
        });

        roleForm.addEventListener('submit', (event) => {
            event.preventDefault();
            submitAjax(roleForm, (data) => {
                const index = roles.findIndex((role) => role.id === data.role.id);
                const previousName = index >= 0 ? roles[index].name : null;

                if (index >= 0) {
                    roles[index] = data.role;
                } else {
                    roles.push(data.role);
                }

                if (previousName && previousName !== data.role.name) {
                    users = users.map((user) => user.role === previousName ? { ...user, role: data.role.name } : user);
                }

                roleModal.hide();
                renderRoleOptions();
                renderAll();
            });
        });

        permissionForm.addEventListener('submit', (event) => {
            event.preventDefault();
            submitAjax(permissionForm, (data) => {
                permissions.push(data.permission);
                permissionModal.hide();
                permissionForm.reset();
                renderAll();
            });
        });

        document.addEventListener('change', async (event) => {
            const toggle = event.target.closest('[data-action="toggle-email-notifications"]');
            if (!toggle) {
                return;
            }

            const id = Number(toggle.dataset.id || 0);
            const user = users.find((item) => item.id === id);
            if (!user) {
                return;
            }

            const previousValue = Boolean(user.email_notifications_enabled);
            toggle.disabled = true;

            try {
                const response = await fetch(endpoint(routes.usersEmailNotificationsUpdate, id), {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ enabled: toggle.checked }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message || 'No se pudo actualizar la preferencia.');
                }

                user.email_notifications_enabled = Boolean(data.email_notifications_enabled);
                renderUsers();
                toast('success', data.message || 'Preferencia actualizada.');
            } catch (error) {
                user.email_notifications_enabled = previousValue;
                renderUsers();
                toast('error', error.message || 'No se pudo actualizar la preferencia.');
            }
        });

        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-action]');
            if (!trigger) {
                return;
            }

            const action = trigger.dataset.action;
            const id = Number(trigger.dataset.id || 0);

            if (action === 'new-user') {
                openUserModal();
            }

            if (action === 'edit-user') {
                const user = users.find((item) => item.id === id);
                if (user) {
                    openUserModal(user);
                }
            }

            if (action === 'delete-user') {
                destroyAjax(endpoint(routes.usersDestroy, id), 'Eliminar este usuario?', () => {
                    users = users.filter((user) => user.id !== id);
                    renderAll();
                });
            }

            if (action === 'new-role') {
                openRoleModal();
            }

            if (action === 'edit-role') {
                const role = roles.find((item) => item.id === id);
                if (role) {
                    openRoleModal(role);
                }
            }

            if (action === 'delete-role') {
                destroyAjax(endpoint(routes.rolesDestroy, id), 'Eliminar este rol?', () => {
                    roles = roles.filter((role) => role.id !== id);
                    renderRoleOptions();
                    renderAll();
                });
            }

            if (action === 'delete-permission') {
                destroyAjax(endpoint(routes.permissionsDestroy, id), 'Eliminar este permiso?', () => {
                    const permission = permissions.find((item) => item.id === id);
                    permissions = permissions.filter((item) => item.id !== id);

                    if (permission) {
                        roles = roles.map((role) => ({
                            ...role,
                            permissions: (role.permissions || []).filter((name) => name !== permission.name),
                        }));
                        users = users.map((user) => ({
                            ...user,
                            permissions: (user.permissions || []).filter((name) => name !== permission.name),
                        }));
                    }

                    renderAll();
                });
            }
        });

        document.querySelectorAll('[data-search]').forEach((input) => {
            input.addEventListener('input', () => {
                filters[input.dataset.search] = input.value || '';
                renderAll();
            });
        });

        document.querySelectorAll('#accessTabs [data-bs-toggle="tab"]').forEach((tabButton) => {
            tabButton.addEventListener('shown.bs.tab', (event) => {
                const nextUrl = new URL(window.location.href);
                nextUrl.searchParams.set('tab', event.target.dataset.tabKey || 'users');
                window.history.replaceState({}, '', nextUrl.toString());
            });
        });

        renderRoleOptions();
        renderAll();
    });
</script>
@endpush
