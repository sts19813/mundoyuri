<!--begin::Header-->
<div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-offset="{default: '200px', lg: '300px'}"
    data-kt-sticky-transition-timingfunction="linear" data-kt-sticky-transition-duration=".5s">
    <!--begin::Header container-->
    <div class="app-container container-xxl d-flex align-items-stretch justify-content-between"
        id="kt_app_header_container">
        <!--begin::Logo-->
        <div class="d-flex align-items-center flex-lg-grow-1" id="kt_app_header_logo">
            <a href="{{ route('admin.dashboard') }}">
                <img alt="Logo" src="{{ asset('/metronic/assets/media/logos/default-dark.svg') }}" class="h-25px app-sidebar-logo-default" />
                <img alt="Logo" src="{{ asset('/metronic/assets/media/logos/default-light.svg') }}" class="h-25px app-sidebar-logo-minimize" />
            </a>
        </div>
        <!--end::Logo-->

        <!--begin::Header nav-->
        <div class="d-flex align-items-center justify-content-end flex-lg-grow-1" id="kt_app_header_nav">
            <!--begin::Navbar-->
            <div class="app-navbar flex-shrink-0">
                <!--begin::User menu-->
                <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
                    <!--begin::Menu- wrapper-->
                    <div class="cursor-pointer symbol symbol-30px" data-kt-menu-trigger="click"
                        data-kt-menu-placement="bottom-end">
                        <img src="{{ asset('/metronic/assets/media/avatars/300-2.jpg') }}" alt="user" />
                    </div>

                    <!--begin::Menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-6 w-275px py-4"
                        data-kt-menu="true">
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <div class="menu-content d-flex align-items-center px-3">
                                <div class="symbol symbol-50px me-5">
                                    <img alt="Logo" src="{{ asset('/metronic/assets/media/avatars/300-2.jpg') }}" />
                                </div>

                                <div class="d-flex flex-column">
                                    <div class="fw-bolder d-flex align-items-center fs-5">
                                        {{ Auth::user()->name }}
                                    </div>
                                    <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">
                                        {{ Auth::user()->email }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--end::Menu item-->

                        <!--begin::Menu separator-->
                        <div class="separator my-2"></div>
                        <!--end::Menu separator-->

                        <!--begin::Menu item-->
                        <div class="menu-item px-5">
                            <a href="{{ route('admin.profile.show') }}" class="menu-link px-5">
                                Mi Perfil
                            </a>
                        </div>
                        <!--end::Menu item-->

                        <!--begin::Menu item-->
                        <div class="menu-item px-5">
                            <a href="{{ route('admin.profile.edit') }}" class="menu-link px-5">
                                Configuración
                            </a>
                        </div>
                        <!--end::Menu item-->

                        <!--begin::Menu separator-->
                        <div class="separator my-2"></div>
                        <!--end::Menu separator-->

                        <!--begin::Menu item-->
                        <div class="menu-item px-5">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="menu-link px-5 text-danger">
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                        <!--end::Menu item-->
                    </div>
                    <!--end::Menu-->
                    <!--end::Menu- wrapper-->
                </div>
                <!--end::User menu-->
            </div>
            <!--end::Navbar-->
        </div>
        <!--end::Header nav-->
    </div>
    <!--end::Header container-->
</div>
<!--end::Header-->
