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
                class="app-sidebar-menu-primary menu menu-column menu-rounded menu-sub-indention menu-state-bullet-primary px-3 mb-5">

                <div class="menu-item mb-2">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Panel</span>
                    </div>
                </div>

                <div class="menu-item">
                    <a href="{{ route('dashboard') }}"
                        class="menu-link {{ request()->is('dashboard*') ? 'active' : '' }}">
                        <span class="menu-icon">
                            <i class="ki-outline ki-home-2 fs-2"></i>
                        </span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a href="{{ route('profile.edit') }}" class="menu-link {{ request()->is('profile*') ? 'active' : '' }}">
                        <span class="menu-icon">
                            <i class="ki-outline ki-user fs-2"></i>
                        </span>
                        <span class="menu-title">Mi perfil</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a href="{{ route('home') }}" class="menu-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <span class="menu-icon">
                            <i class="ki-outline ki-abstract-26 fs-2"></i>
                        </span>
                        <span class="menu-title">Inicio portal</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a href="{{ route('catalog.series.index') }}" class="menu-link {{ request()->routeIs('catalog.series.*') ? 'active' : '' }}">
                        <span class="menu-icon">
                            <i class="ki-outline ki-film fs-2"></i>
                        </span>
                        <span class="menu-title">Series y películas</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a href="{{ route('catalog.genres.index') }}" class="menu-link {{ request()->routeIs('catalog.genres.*') ? 'active' : '' }}">
                        <span class="menu-icon">
                            <i class="ki-outline ki-category fs-2"></i>
                        </span>
                        <span class="menu-title">Géneros</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a href="{{ route('submissions.create') }}" class="menu-link {{ request()->routeIs('submissions.*') ? 'active' : '' }}">
                        <span class="menu-icon">
                            <i class="ki-outline ki-plus-square fs-2"></i>
                        </span>
                        <span class="menu-title">Subir contenido</span>
                    </a>
                </div>

                @php
                    $panelUser = auth()->user();
                    $isAdminPanel = $panelUser && ($panelUser->role === 'admin' || (method_exists($panelUser, 'hasRole') && $panelUser->hasRole('admin')));
                @endphp

                @if($isAdminPanel)
                    <div class="menu-item mt-6 mb-2">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Administración</span>
                        </div>
                    </div>

                    <div class="menu-item">
                        <a href="{{ route('admin.dashboard') }}" class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-setting-2 fs-2"></i>
                            </span>
                            <span class="menu-title">Dashboard admin</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a href="{{ route('admin.moderation.index') }}" class="menu-link {{ request()->routeIs('admin.moderation.*') ? 'active' : '' }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-shield-tick fs-2"></i>
                            </span>
                            <span class="menu-title">Validación</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a href="{{ route('admin.users.index') }}" class="menu-link {{ request()->routeIs('admin.users.*', 'admin.roles.*', 'admin.permissions.*') ? 'active' : '' }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-profile-user fs-2"></i>
                            </span>
                            <span class="menu-title">Usuarios y permisos</span>
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
