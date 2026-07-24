<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <base href="{{ asset('metronic') }}/" />
    <title>@yield('title', 'Mundo Yuri')</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <x-portal-favicon />

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <style>
        body.auth-portal-page {
            min-height: 100vh;
            margin: 0;
            color: #f7eef8;
            background: linear-gradient(90deg, rgba(8, 5, 11, .72), rgba(8, 5, 11, .4) 52%, rgba(8, 5, 11, .68)),
                url('{{ asset('assets/img/wallpaper-login.jpg') }}') center center / cover fixed no-repeat;
        }

        .auth-portal-page .gl-nav { top: 0 !important; }
        .auth-portal-page #kt_app_root { min-height: 100vh; padding: 110px 40px 40px; }
        .auth-portal-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(390px, 500px);
            align-items: center;
            gap: clamp(40px, 7vw, 110px);
            width: min(1240px, 100%);
            margin: auto;
        }
        .auth-portal-intro { text-align: left; text-shadow: 0 3px 22px rgba(0, 0, 0, .75); }
        .auth-portal-intro h1, .auth-portal-description { color: #fff !important; }
        .auth-portal-intro h1 {
            max-width: 650px;
            margin: 0 0 20px;
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.4rem, 5vw, 4.8rem) !important;
            line-height: 1.04;
        }
        .auth-portal-description { max-width: 520px; font-size: 1.05rem !important; line-height: 1.75; }
        .auth-portal-panel {
            width: 100%;
            padding: clamp(30px, 4vw, 48px);
            border: 1px solid rgba(244, 63, 142, .24);
            border-radius: 24px;
            background: linear-gradient(145deg, rgba(29, 18, 33, .96), rgba(15, 10, 18, .97)) !important;
            box-shadow: 0 28px 80px rgba(0, 0, 0, .38);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .auth-portal-panel > div,
        .auth-portal-panel .bg-body,
        .auth-portal-panel .flex-column-fluid,
        .auth-portal-panel .flex-column-fluid > div {
            width: 100% !important;
            min-height: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        .auth-portal-panel .pb-15,
        .auth-portal-panel .pb-lg-20 { padding-bottom: 0 !important; }
        .auth-portal-panel h1 { color: #fff !important; font-family: 'Playfair Display', serif; }
        .auth-portal-panel .text-gray-500,
        .auth-portal-panel .text-muted,
        .auth-portal-panel .form-check-label { color: #aa9aae !important; }
        .auth-portal-panel .form-control {
            min-height: 50px;
            border: 1px solid rgba(255, 255, 255, .12) !important;
            border-radius: 12px;
            background: rgba(255, 255, 255, .055) !important;
            color: #fff !important;
        }
        .auth-portal-panel .form-control::placeholder { color: #89798e; }
        .auth-portal-panel .form-control:focus {
            border-color: rgba(244, 63, 142, .72) !important;
            box-shadow: 0 0 0 3px rgba(244, 63, 142, .12);
        }
        .auth-portal-panel .btn-primary {
            min-height: 50px;
            border-color: #f43f8e !important;
            border-radius: 12px;
            background: linear-gradient(135deg, #f43f8e, #d92d78) !important;
            box-shadow: 0 10px 26px rgba(244, 63, 142, .24);
        }
        .auth-portal-panel .link-primary,
        .auth-portal-panel a { color: #f56aa5 !important; }
        .auth-portal-panel .form-check-input:checked { border-color: #f43f8e; background-color: #f43f8e; }
        .auth-portal-panel .d-flex.flex-stack.px-lg-10 { display: none !important; }

        .google-auth-btn {
            min-height: 50px;
            border-color: rgba(255, 255, 255, .12) !important;
            border-radius: 12px;
            background: rgba(255, 255, 255, .07) !important;
            color: #f7eef8 !important;
            box-shadow: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .google-auth-btn:hover,
        .google-auth-btn:focus {
            transform: translateY(-1px);
            border-color: rgba(244, 63, 142, .6) !important;
            background: rgba(244, 63, 142, .1) !important;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .2);
            color: #fff !important;
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

        @media (max-width: 991.98px) {
            body.auth-portal-page { background-position: 43% center; background-attachment: scroll; }
            .auth-portal-page #kt_app_root { padding: 108px 18px 38px; }
            .auth-portal-shell { display: block; width: min(520px, 100%); }
            .auth-portal-intro { padding: 0 8px 28px; text-align: center; }
            .auth-portal-intro h1 { font-size: clamp(2rem, 9vw, 3.2rem) !important; margin-bottom: 12px !important; }
            .auth-portal-panel { width: 100%; padding: 28px 22px !important; }
        }
    </style>

    @stack('styles')
</head>
<body id="kt_body" class="auth-portal-page app-blank">
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
        <div class="auth-portal-shell">
            <div class="auth-portal-intro">
                <h1 class="fw-bold">Historias que te harán sentir</h1>
                <div class="auth-portal-description fw-semibold">
                    Tu espacio para descubrir y compartir historias Girls' Love de todo el mundo.
                </div>
            </div>
            <main class="auth-portal-panel">
                @yield('auth_content')
            </main>
        </div>
    </div>

    <script>var hostUrl = "assets/";</script>
    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>

    @stack('scripts')
    <x-neko-assistant />
</body>
</html>
