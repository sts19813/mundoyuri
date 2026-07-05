@php
    $user = Auth::user();
    $isAdmin = $user?->isAdmin();
    $panelRole = $isAdmin ? 'admin' : ($user?->getRoleNames()->first() ?: $user?->role ?: 'user');
    $panelRoleLabel = match ($panelRole) {
        'admin' => 'Administrador',
        'moderator' => 'Moderador',
        default => 'Usuario',
    };
    $panelRoleBadge = match ($panelRole) {
        'admin' => 'success',
        'moderator' => 'primary',
        default => 'secondary',
    };
@endphp

<div class="app-navbar flex-grow-1 justify-content-end" id="kt_app_header_navbar">
    <div class="app-navbar-item d-flex align-items-center me-1 me-lg-3">
        <a href="{{ route('home') }}"
            class="btn btn-icon btn-custom btn-color-gray-600 btn-active-color-primary w-35px h-35px w-md-40px h-md-40px"
            data-bs-toggle="tooltip" title="Ir al portal">
            <i class="ki-outline ki-abstract-26 fs-2"></i>
        </a>
    </div>

    <div class="app-navbar-item ms-1 ms-lg-3 me-3 me-lg-6" id="kt_header_user_menu_toggle">
        <div class="cursor-pointer symbol symbol-circle symbol-35px symbol-md-40px"
            data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
            data-kt-menu-placement="bottom-end">
            <img src="{{ $user->avatarUrl() }}" alt="Avatar de {{ $user->name }}" data-profile-avatar />
        </div>

        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-300px"
            data-kt-menu="true">
            <div class="menu-item px-3">
                <div class="menu-content d-flex align-items-center px-3">
                    <div class="symbol symbol-50px me-5">
                        <img src="{{ $user->avatarUrl() }}" alt="Avatar de {{ $user->name }}" data-profile-avatar />
                    </div>
                    <div class="d-flex flex-column min-w-0">
                        <div class="fw-bold d-flex align-items-center fs-5 text-gray-900 text-truncate">
                            <span data-profile-name>{{ $user->name }}</span>
                            <span class="badge badge-light-{{ $panelRoleBadge }} fw-bold fs-8 px-2 py-1 ms-2">{{ $panelRoleLabel }}</span>
                        </div>
                        <a href="mailto:{{ $user->email }}" class="fw-semibold text-muted text-hover-primary fs-7 text-truncate"
                            data-profile-email>
                            {{ $user->email }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="separator my-2"></div>

            <div class="menu-item px-5">
                <a href="{{ route('admin.profile.show') }}" class="menu-link px-5">
                    <span class="menu-icon">
                        <i class="ki-outline ki-user fs-2"></i>
                    </span>
                    <span class="menu-title">Mi perfil</span>
                </a>
            </div>

            <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
                <a href="#" class="menu-link px-5">
                    <span class="menu-icon">
                        <i class="ki-outline ki-night-day theme-light-show fs-2"></i>
                        <i class="ki-outline ki-moon theme-dark-show fs-2"></i>
                    </span>
                    <span class="menu-title position-relative">Tema</span>
                </a>

                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                    data-kt-menu="true" data-kt-element="theme-mode-menu">
                    <div class="menu-item px-3 my-0">
                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                            <span class="menu-icon" data-kt-element="icon">
                                <i class="ki-outline ki-night-day fs-2"></i>
                            </span>
                            <span class="menu-title">Claro</span>
                        </a>
                    </div>
                    <div class="menu-item px-3 my-0">
                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                            <span class="menu-icon" data-kt-element="icon">
                                <i class="ki-outline ki-moon fs-2"></i>
                            </span>
                            <span class="menu-title">Oscuro</span>
                        </a>
                    </div>
                    <div class="menu-item px-3 my-0">
                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                            <span class="menu-icon" data-kt-element="icon">
                                <i class="ki-outline ki-screen fs-2"></i>
                            </span>
                            <span class="menu-title">Sistema</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="separator my-2"></div>

            <div class="menu-item px-5">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="menu-link px-5 border-0 bg-transparent w-100 text-start text-danger">
                        <span class="menu-icon">
                            <i class="ki-outline ki-exit-right fs-2 text-danger"></i>
                        </span>
                        <span class="menu-title">Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
