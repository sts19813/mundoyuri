<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="{{ route('admin.dashboard') }}">
            <img alt="Logo" src="{{ asset('/metronic/assets/media/logos/default-dark.svg') }}"
                class="h-30px app-sidebar-logo-default" />
            <img alt="Logo" src="{{ asset('/metronic/assets/media/logos/default-light.svg') }}"
                class="h-30px app-sidebar-logo-minimize" />
        </a>
    </div>
    <!--end::Logo-->

    <!--begin::Sidebar Menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid px-6 pb-12" id="kt_app_sidebar_menu" data-kt-scroll="true"
        data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_header"
        data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
        <!--begin::Menu item-->
        <div data-kt-menu-trigger="click" class="menu-item here show menu-accordion">
            <!--begin::Menu link-->
            <span class="menu-link active">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">Menú Principal</span>
            </span>
            <!--end::Menu link-->

            <!--begin::Menu sub-->
            <div class="menu-sub menu-sub-accordion menu-active-bg">
                <!--begin::Menu item-->
                <div class="menu-item">
                    <!--begin::Menu link-->
                    <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                    <!--end::Menu link-->
                </div>
                <!--end::Menu item-->

                <!--begin::Menu item-->
                <div class="menu-item">
                    <!--begin::Menu link-->
                    <a class="menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                        href="{{ route('admin.users.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">Usuarios</span>
                    </a>
                    <!--end::Menu link-->
                </div>
                <!--end::Menu item-->
            </div>
            <!--end::Menu sub-->
        </div>
        <!--end::Menu item-->

        @can('manage roles')
            <!--begin::Menu item-->
            <div class="menu-item">
                <!--begin::Menu link-->
                <a class="menu-link" href="#">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">Roles y Permisos</span>
                </a>
                <!--end::Menu link-->
            </div>
            <!--end::Menu item-->
        @endcan

        @can('view series')
            <!--begin::Menu item-->
            <div class="menu-item">
                <!--begin::Menu link-->
                <a class="menu-link" href="#">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">Series</span>
                </a>
                <!--end::Menu link-->
            </div>
            <!--end::Menu item-->
        @endcan
    </div>
    <!--end::Sidebar Menu-->
</div>
<!--end::Sidebar-->
