@php($authReturn = request()->routeIs('login', 'register', 'password.*') ? null : request()->fullUrl())

<div class="portal-auth-overlay" data-auth-overlay hidden>
    <div class="portal-auth-backdrop" data-auth-close></div>
    <section class="portal-auth-dialog" id="portal-auth-dialog" role="dialog" aria-modal="true" aria-labelledby="portal-auth-title" data-auth-dialog
        data-flash-intent="{{ session('auth_modal_intent') }}" data-flash-error="{{ session('auth_modal_intent') ? session('error') : '' }}">
        <button type="button" class="portal-auth-close" data-auth-close aria-label="Cerrar ventana de acceso">&times;</button>
        <div class="portal-auth-brand"><span class="portal-auth-sparkle" aria-hidden="true">✦</span> MUNDO YURI</div>
        <h2 id="portal-auth-title">Tu espacio para cada historia</h2>
        <p class="portal-auth-intro">Crea tu cuenta y haz crecer la comunidad.</p>

        <div class="portal-auth-tabs" role="tablist" aria-label="Acceso a Mundo Yuri">
            <button type="button" id="portal-auth-login-tab" role="tab" aria-controls="portal-auth-login-panel" aria-selected="true" data-auth-tab="login">Iniciar sesión</button>
            <button type="button" id="portal-auth-register-tab" role="tab" aria-controls="portal-auth-register-panel" aria-selected="false" tabindex="-1" data-auth-tab="register">Registrarme</button>
        </div>

        <div id="portal-auth-login-panel" role="tabpanel" aria-labelledby="portal-auth-login-tab" data-auth-panel="login">
            <a class="portal-auth-google" href="{{ route('auth.google.redirect', array_filter(['intent' => 'login', 'return' => $authReturn])) }}">
                <img src="{{ asset('metronic/assets/media/svg/brand-logos/google-icon.svg') }}" alt="" width="22" height="22">
                Continuar con Google
            </a>
            <p class="portal-auth-google-note">Si aún no tienes cuenta, se creará al continuar con Google.</p>
            <div class="portal-auth-divider"><span>o usa tu correo</span></div>
            <form method="POST" action="{{ route('login') }}" data-auth-form="login">
                @csrf
                @if($authReturn)<input type="hidden" name="return" value="{{ $authReturn }}">@endif
                <div class="portal-auth-feedback" role="alert" data-auth-feedback hidden></div>
                <label for="portal-auth-login-email">Correo electrónico</label>
                <input id="portal-auth-login-email" name="email" type="email" autocomplete="email" required>
                <label for="portal-auth-login-password">Contraseña</label>
                <input id="portal-auth-login-password" name="password" type="password" autocomplete="current-password" required>
                <div class="portal-auth-options">
                    <label class="portal-auth-remember"><input type="checkbox" name="remember" value="1"> Recordarme</label>
                    <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                </div>
                <button class="portal-auth-submit" type="submit">Iniciar sesión</button>
            </form>
        </div>

        <div id="portal-auth-register-panel" role="tabpanel" aria-labelledby="portal-auth-register-tab" data-auth-panel="register" hidden>
            <a class="portal-auth-google" href="{{ route('auth.google.redirect', array_filter(['intent' => 'register', 'return' => $authReturn])) }}">
                <img src="{{ asset('metronic/assets/media/svg/brand-logos/google-icon.svg') }}" alt="" width="22" height="22">
                Registrarme con Google
            </a>
            <p class="portal-auth-google-note">Si ya tienes cuenta, entrarás a tu perfil.</p>
            <div class="portal-auth-divider"><span>o crea tu cuenta con correo</span></div>
            <form method="POST" action="{{ route('register') }}" data-auth-form="register">
                @csrf
                @if($authReturn)<input type="hidden" name="return" value="{{ $authReturn }}">@endif
                <div class="portal-auth-feedback" role="alert" data-auth-feedback hidden></div>
                <label for="portal-auth-register-name">Nombre</label>
                <input id="portal-auth-register-name" name="name" type="text" autocomplete="name" required>
                <label for="portal-auth-register-email">Correo electrónico</label>
                <input id="portal-auth-register-email" name="email" type="email" autocomplete="email" required>
                <label for="portal-auth-register-password">Contraseña</label>
                <input id="portal-auth-register-password" name="password" type="password" autocomplete="new-password" required minlength="8">
                <label for="portal-auth-register-confirmation">Confirmar contraseña</label>
                <input id="portal-auth-register-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="8">
                <button class="portal-auth-submit" type="submit">Crear mi perfil</button>
            </form>
        </div>
    </section>
</div>
