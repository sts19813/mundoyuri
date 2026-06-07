@php
    $panelUser = auth()->user();
    $isAdminPanel = $panelUser?->isAdmin();
@endphp

<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Wrapper-->
    <div id="kt_app_sidebar_wrapper" class="app-sidebar-wrapper">
        <div class="hover-scroll-y my-5 my-lg-2 mx-4" data-kt-scroll="true"
            data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_header" data-kt-scroll-wrappers="#kt_app_sidebar_wrapper"
            data-kt-scroll-offset="5px">
            <!--begin::Sidebar menu-->
            <div id="kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false"
                class="app-sidebar-menu-primary menu menu-column menu-rounded menu-sub-indention menu-state-icon-primary menu-state-bullet-primary fw-semibold fs-6 px-3 mb-5">

                <div class="menu-item mb-2">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Panel</span>
                    </div>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <span class="menu-icon"><i class="ki-outline ki-home-2 fs-2"></i></span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.profile.*') || request()->routeIs('profile.*') ? 'active' : '' }}"
                        href="{{ route('admin.profile.show') }}">
                        <span class="menu-icon"><i class="ki-outline ki-user fs-2"></i></span>
                        <span class="menu-title">Mi perfil</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        <span class="menu-icon"><i class="ki-outline ki-abstract-26 fs-2"></i></span>
                        <span class="menu-title">Inicio portal</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('catalog.series.*') ? 'active' : '' }}" href="{{ route('catalog.series.index') }}">
                        <span class="menu-icon"><i class="ki-outline ki-film fs-2"></i></span>
                        <span class="menu-title">Series y películas</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('catalog.genres.*') ? 'active' : '' }}" href="{{ route('catalog.genres.index') }}">
                        <span class="menu-icon"><i class="ki-outline ki-category fs-2"></i></span>
                        <span class="menu-title">Géneros</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('submissions.*') ? 'active' : '' }}" href="{{ route('submissions.create') }}">
                        <span class="menu-icon"><i class="ki-outline ki-plus-square fs-2"></i></span>
                        <span class="menu-title">Subir contenido</span>
                    </a>
                </div>

                @if($isAdminPanel)
                    <div class="menu-item mt-6 mb-2">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Administración</span>
                        </div>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <span class="menu-icon"><i class="ki-outline ki-setting-2 fs-2"></i></span>
                            <span class="menu-title">Dashboard admin</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.users.*', 'admin.roles.*', 'admin.permissions.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-profile-user fs-2"></i></span>
                            <span class="menu-title">Usuarios y permisos</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.genres.*') ? 'active' : '' }}" href="{{ route('admin.genres.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-category fs-2"></i></span>
                            <span class="menu-title">Géneros admin</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.series.*') ? 'active' : '' }}" href="{{ route('admin.series.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-film fs-2"></i></span>
                            <span class="menu-title">Series admin</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.episodes.*') ? 'active' : '' }}" href="{{ route('admin.episodes.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-video fs-2"></i></span>
                            <span class="menu-title">Episodios</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.moderation.*') ? 'active' : '' }}" href="{{ route('admin.moderation.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-shield-tick fs-2"></i></span>
                            <span class="menu-title">Validación</span>
                        </a>
                    </div>
                @endif
            </div>
            <!--end::Sidebar menu-->
        </div>
    </div>
    <!--end::Wrapper-->
</div>
<!--end::Sidebar-->
