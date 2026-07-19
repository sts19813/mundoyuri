<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <base href="{{ asset('metronic') }}/" />
    <title>@yield('title', 'Mundo Yuri')</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <x-portal-favicon />

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <style>
        .google-auth-btn {
            border-color: var(--bs-gray-300) !important;
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fb 100%);
            color: var(--bs-gray-800) !important;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .google-auth-btn:hover,
        .google-auth-btn:focus {
            transform: translateY(-1px);
            border-color: var(--bs-primary) !important;
            box-shadow: 0 14px 30px rgba(54, 153, 255, 0.16);
            color: var(--bs-gray-900) !important;
        }

        [data-bs-theme="dark"] .google-auth-btn {
            background: linear-gradient(180deg, #1f2433 0%, #171c29 100%);
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: var(--bs-gray-100) !important;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.28);
        }

        [data-bs-theme="dark"] .google-auth-btn:hover,
        [data-bs-theme="dark"] .google-auth-btn:focus {
            border-color: rgba(var(--bs-primary-rgb), 0.5) !important;
            box-shadow: 0 16px 34px rgba(0, 0, 0, 0.38);
        }
    </style>

    @stack('styles')
</head>
<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center">
    <x-navbar />
    <script>
        var defaultThemeMode = "light";
        var themeMode;

        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else if (localStorage.getItem("data-bs-theme") !== null) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else {
                themeMode = defaultThemeMode;
            }

            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }

            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>

    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <style>
            body { background-image: url('assets/media/auth/bg10.jpeg'); }
            [data-bs-theme="dark"] body { background-image: url('assets/media/auth/bg10-dark.jpeg'); }
        </style>

        <div class="d-flex flex-column flex-lg-row flex-column-fluid">
            <div class="d-flex flex-lg-row-fluid">
                <div class="d-flex flex-column flex-center pb-0 pb-lg-10 p-10 w-100">
                    <img class="theme-light-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20" src="assets/media/auth/agency.png" alt="Auth Illustration" />
                    <img class="theme-dark-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20" src="assets/media/auth/agency-dark.png" alt="Auth Illustration" />

                    <h1 class="text-gray-800 fs-2qx fw-bold text-center mb-7">Historias que te harán sentir</h1>
                    <div class="text-gray-600 fs-base text-center fw-semibold">
                        Inicia sesión o crea tu cuenta para formar parte de Mundo Yuri.
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12">
                <div class="bg-body d-flex flex-column flex-center rounded-4 w-md-600px p-10">
                    <div class="d-flex flex-center flex-column align-items-stretch h-lg-100 w-md-400px">
                        <div class="d-flex flex-center flex-column flex-column-fluid pb-15 pb-lg-20">
                            @yield('auth_content')
                        </div>

                        <div class="d-flex flex-stack">
                            <div class="d-flex fw-semibold text-primary fs-base gap-5">
                                <a href="https://keenthemes.com" target="_blank" rel="noopener">Terms</a>
                                <a href="https://keenthemes.com/metronic" target="_blank" rel="noopener">Plans</a>
                                <a href="https://keenthemes.com/support" target="_blank" rel="noopener">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-footer />

    <script>var hostUrl = "assets/";</script>
    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>

    @stack('scripts')
</body>
</html>
