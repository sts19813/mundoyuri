@props(['transparent' => false])

<nav class="gl-nav{{ $transparent ? '' : ' scrolled' }}" id="navbar">
    <div class="nav-inner">
        <a href="{{ route('home') }}" class="brand" aria-label="Mundo Yuri">
            <img src="{{ asset('assets/img/logos/Logo_default.png') }}" alt="Mundo Yuri" class="brand-logo">
        </a>
        <ul class="nav-links" id="navLinks">
            <li><a href="{{ route('home') }}">Inicio</a></li>
            <li><a href="{{ route('catalog.series.index', ['type' => 'series']) }}">Series</a></li>
            <li><a href="{{ route('catalog.series.index', ['type' => 'movie']) }}">Peliculas</a></li>
            <li><a href="{{ route('catalog.genres.index') }}">Generos</a></li>
            <li><a href="{{ route('about') }}">Nosotros</a></li>
        </ul>
        <div class="nav-actions">
            <form action="{{ route('catalog.series.index') }}" method="GET" class="nav-search-form" role="search">
                <input type="text" name="q" class="nav-search" placeholder="Buscar series..." value="{{ request('q') }}" aria-label="Buscar series">
            </form>

            @guest
                <a href="{{ request()->routeIs('login', 'register', 'password.*') ? route('login') : route('login', ['return' => request()->fullUrl()]) }}" class="nav-login">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                        <polyline points="10 17 15 12 10 7" />
                        <line x1="15" y1="12" x2="3" y2="12" />
                    </svg>
                    <span>Iniciar sesión</span>
                </a>
            @else
                @php($portalUser = auth()->user())
                <div class="portal-user-menu" data-user-menu>
                    <button type="button" class="portal-user-trigger" data-user-menu-trigger aria-haspopup="true" aria-expanded="false" aria-label="Abrir menú de {{ $portalUser->name }}">
                        @if($portalUser->hasProfileAvatar())
                            <img src="{{ $portalUser->avatarUrl() }}" alt="Foto de perfil de {{ $portalUser->name }}" class="portal-avatar portal-avatar-image">
                        @else
                            <span class="portal-avatar portal-avatar-fallback" aria-hidden="true">{{ $portalUser->initials() }}</span>
                        @endif
                        <span class="portal-user-name">{{ $portalUser->alias ?: $portalUser->name }}</span>
                        <svg class="portal-user-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>

                    <div class="portal-user-dropdown" data-user-menu-dropdown role="menu">
                        <div class="portal-user-summary">
                            @if($portalUser->hasProfileAvatar())
                                <img src="{{ $portalUser->avatarUrl() }}" alt="" class="portal-avatar portal-avatar-image portal-avatar-lg">
                            @else
                                <span class="portal-avatar portal-avatar-fallback portal-avatar-lg" aria-hidden="true">{{ $portalUser->initials() }}</span>
                            @endif
                            <div>
                                <strong>{{ $portalUser->name }}</strong>
                                <span>{{ $portalUser->email }}</span>
                            </div>
                        </div>
                        <div class="portal-dropdown-divider"></div>
                        <a href="{{ route('profile.edit') }}" class="portal-dropdown-item" role="menuitem">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                            Mi perfil
                        </a>
                        <a href="{{ route('submissions.create') }}" class="portal-dropdown-item" role="menuitem">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                            Subir contenido
                        </a>
                        <div class="portal-dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="portal-dropdown-item portal-dropdown-logout" role="menuitem">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            @endguest
        </div>
        <button class="nav-toggler" id="navToggler" aria-label="Toggle menu">&#9776;</button>
    </div>
</nav>

@once
    <script>
        document.addEventListener('click', function (event) {
            document.querySelectorAll('[data-user-menu]').forEach(function (menu) {
                const trigger = menu.querySelector('[data-user-menu-trigger]');
                const clickedTrigger = trigger && trigger.contains(event.target);

                if (clickedTrigger) {
                    const willOpen = !menu.classList.contains('is-open');
                    document.querySelectorAll('[data-user-menu].is-open').forEach(function (openMenu) {
                        openMenu.classList.remove('is-open');
                        openMenu.querySelector('[data-user-menu-trigger]')?.setAttribute('aria-expanded', 'false');
                    });
                    menu.classList.toggle('is-open', willOpen);
                    trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    return;
                }

                if (!menu.contains(event.target)) {
                    menu.classList.remove('is-open');
                    trigger?.setAttribute('aria-expanded', 'false');
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;
            document.querySelectorAll('[data-user-menu].is-open').forEach(function (menu) {
                menu.classList.remove('is-open');
                const trigger = menu.querySelector('[data-user-menu-trigger]');
                trigger?.setAttribute('aria-expanded', 'false');
                trigger?.focus();
            });
        });
    </script>
@endonce
