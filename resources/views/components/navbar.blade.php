@props(['transparent' => false])

<nav class="gl-nav{{ $transparent ? '' : ' scrolled' }}" id="navbar">
    <div class="nav-inner">
        <a href="{{ route('home') }}" class="brand" aria-label="Mundo Yuri">
            <img src="{{ asset('assets/img/logos/mundo-yuri-logo.svg') }}" alt="Mundo Yuri" class="brand-logo">
        </a>
        <ul class="nav-links" id="navLinks">
            <li class="nav-mobile-heading"><span>Explorar Mundo Yuri</span><small>Elige dónde quieres ir</small></li>
            <li class="nav-mobile-search">
                <form action="{{ route('catalog.series.index') }}" method="GET" role="search">
                    <label class="visually-hidden" for="mobile-nav-search">Buscar series</label>
                    <input id="mobile-nav-search" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar series…">
                    <button type="submit" aria-label="Buscar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    </button>
                </form>
            </li>
            <li><a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Inicio</a></li>
            <li><a href="{{ route('community.index') }}" @if(request()->routeIs('community.index', 'community.members', 'community.activity')) aria-current="page" @endif>Comunidad</a></li>
            <li><a href="{{ route('forums.index') }}" @if(request()->routeIs('forums.*', 'forum.*')) aria-current="page" @endif>Foros</a></li>
            <li><a href="{{ route('questions.index') }}" @if(request()->routeIs('questions.*')) aria-current="page" @endif>Preguntas</a></li>
            <li><a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>Nosotros</a></li>
        </ul>
        <div class="nav-actions">
            <form action="{{ route('catalog.series.index') }}" method="GET" class="nav-search-form" role="search">
                <input type="text" name="q" class="nav-search" placeholder="Buscar series..." value="{{ request('q') }}" aria-label="Buscar series">
            </form>

            @guest
                <a href="{{ request()->routeIs('login', 'register', 'password.*') ? route('login') : route('login', ['return' => request()->fullUrl()]) }}" class="nav-login" data-auth-open="login" aria-haspopup="dialog" aria-controls="portal-auth-dialog" aria-expanded="false">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                        <polyline points="10 17 15 12 10 7" />
                        <line x1="15" y1="12" x2="3" y2="12" />
                    </svg>
                    <span>Iniciar sesión</span>
                </a>
            @else
                @php($portalUser = auth()->user())
                @php($portalUnreadMessages = $portalUser->receivedMessages()->whereNull('read_at')->count())
                @php($portalUnreadNotifications = $portalUser->unreadNotifications()->where('type', '!=', \App\Notifications\NewDirectMessageNotification::class)->count())
                @php($portalRecentNotifications = $portalUser->notifications()->where('type', '!=', \App\Notifications\NewDirectMessageNotification::class)->latest()->limit(6)->get())

                <div
                    class="portal-message-menu"
                    data-message-menu
                    data-message-status-url="{{ route('messages.status') }}"
                    data-message-index-url="{{ route('messages.index') }}"
                    data-initial-unread-count="{{ $portalUnreadMessages }}"
                    data-latest-unread-id="{{ optional($portalUser->receivedMessages()->whereNull('read_at')->latest('id')->first())->id ?: 0 }}"
                >
                    <a href="{{ route('messages.index') }}" class="portal-nav-shortcut" data-message-menu-trigger aria-haspopup="true" aria-expanded="false" aria-controls="portal-message-dropdown" aria-label="Mensajes{{ $portalUnreadMessages ? ': '.$portalUnreadMessages.' sin leer' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
                        </svg>
                        <span class="portal-nav-badge" data-message-badge @if(!$portalUnreadMessages) hidden @endif>{{ $portalUnreadMessages > 99 ? '99+' : $portalUnreadMessages }}</span>
                    </a>
                    <div id="portal-message-dropdown" class="portal-message-dropdown" data-message-menu-dropdown>
                        <header class="portal-notification-heading">
                            <div>
                                <strong>Chats</strong>
                                <small data-message-heading-status>{{ $portalUnreadMessages ? $portalUnreadMessages.' sin leer' : 'Mensajes recientes' }}</small>
                            </div>
                            <a href="{{ route('messages.index') }}">Ver todos</a>
                        </header>
                        <div class="portal-message-list" data-message-list>
                            <p class="portal-notification-empty">Cargando chats recientes...</p>
                        </div>
                        <footer class="portal-notification-actions">
                            <a href="{{ route('messages.index') }}">Abrir mensajes</a>
                            <a href="{{ route('community.members') }}">Nuevo chat</a>
                        </footer>
                    </div>
                </div>
                <div class="portal-notification-menu" data-notification-menu>
                    <button type="button" class="portal-nav-shortcut" data-notification-menu-trigger aria-haspopup="true" aria-expanded="false" aria-controls="portal-notification-dropdown" aria-label="Notificaciones{{ $portalUnreadNotifications ? ': '.$portalUnreadNotifications.' sin leer' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                            <path d="M13.7 21a2 2 0 0 1-3.4 0"/>
                        </svg>
                        @if($portalUnreadNotifications)
                            <span class="portal-nav-badge">{{ $portalUnreadNotifications > 99 ? '99+' : $portalUnreadNotifications }}</span>
                        @endif
                    </button>
                    <div id="portal-notification-dropdown" class="portal-notification-dropdown" data-notification-menu-dropdown>
                        <header class="portal-notification-heading">
                            <div>
                                <strong>Notificaciones</strong>
                                <small>{{ $portalUnreadNotifications ? $portalUnreadNotifications.' sin leer' : 'Estás al día' }}</small>
                            </div>
                            <a href="{{ route('notifications.index') }}">Ver todas</a>
                        </header>
                        <div class="portal-notification-list">
                            @forelse($portalRecentNotifications as $notification)
                                <a class="portal-notification-item{{ $notification->read_at ? '' : ' is-unread' }}" href="{{ route('notifications.open', $notification) }}">
                                    @if(filled($notification->data['actor_avatar'] ?? null))
                                        <img src="{{ $notification->data['actor_avatar'] }}" alt="">
                                    @else
                                        <span class="portal-notification-avatar-fallback">MY</span>
                                    @endif
                                    <span>
                                        <strong>{{ $notification->data['title'] ?? 'Nueva actividad' }}</strong>
                                        <small>{{ $notification->data['message'] ?? '' }}</small>
                                        <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                                    </span>
                                    @unless($notification->read_at)<i aria-label="Sin leer"></i>@endunless
                                </a>
                            @empty
                                <p class="portal-notification-empty">No tienes notificaciones todavía.</p>
                            @endforelse
                        </div>
                        <footer class="portal-notification-actions">
                            <a href="{{ route('notifications.index') }}">Ver todas</a>
                            @if($portalRecentNotifications->isNotEmpty())
                                <form method="POST" action="{{ route('notifications.destroy-all') }}" onsubmit="return confirm('¿Borrar todas tus notificaciones? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Borrar todas</button>
                                </form>
                            @endif
                        </footer>
                    </div>
                </div>

                <div class="portal-user-menu" data-user-menu>
                    <button type="button" class="portal-user-trigger" data-user-menu-trigger aria-haspopup="true" aria-expanded="false" aria-label="Abrir menú de {{ $portalUser->name }}">
                        @if($portalUser->hasProfileAvatar())
                            <img src="{{ $portalUser->avatarUrl() }}" alt="Foto de perfil de {{ $portalUser->name }}" @class(['portal-avatar', 'portal-avatar-image', 'is-online' => $portalUser->isOnline()])>
                        @else
                            <span @class(['portal-avatar', 'portal-avatar-fallback', 'is-online' => $portalUser->isOnline()]) aria-hidden="true">{{ $portalUser->initials() }}</span>
                        @endif
                        <span class="portal-user-name">{{ $portalUser->alias ?: $portalUser->name }}</span>
                        <svg class="portal-user-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>

                    <div class="portal-user-dropdown" data-user-menu-dropdown role="menu">
                        <div class="portal-user-summary">
                            @if($portalUser->hasProfileAvatar())
                                <img src="{{ $portalUser->avatarUrl() }}" alt="" @class(['portal-avatar', 'portal-avatar-image', 'portal-avatar-lg', 'is-online' => $portalUser->isOnline()])>
                            @else
                                <span @class(['portal-avatar', 'portal-avatar-fallback', 'portal-avatar-lg', 'is-online' => $portalUser->isOnline()]) aria-hidden="true">{{ $portalUser->initials() }}</span>
                            @endif
                            <div>
                                <strong>{{ $portalUser->name }}</strong>
                                <span>{{ $portalUser->email }}</span>
                            </div>
                        </div>
                        <div class="portal-dropdown-divider"></div>
                        <a href="{{ route('profile.show') }}" class="portal-dropdown-item" role="menuitem">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                            Mi perfil
                        </a>
                        <a href="{{ route('messages.index') }}" class="portal-dropdown-item" role="menuitem">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
                            Mensajes
                            <span class="portal-dropdown-badge" data-message-user-badge @if(!$portalUnreadMessages) hidden @endif>{{ $portalUnreadMessages > 99 ? '99+' : $portalUnreadMessages }}</span>
                        </a>
                        <a href="{{ route('notifications.index') }}" class="portal-dropdown-item" role="menuitem">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                            Notificaciones
                            @if($portalUnreadNotifications)
                                <span class="portal-dropdown-badge">{{ $portalUnreadNotifications > 99 ? '99+' : $portalUnreadNotifications }}</span>
                            @endif
                        </a>
                        <button type="button" class="portal-dropdown-item portal-email-preference" data-push-toggle aria-pressed="{{ $portalUser->push_notifications_enabled ? 'true' : 'false' }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                            <span class="portal-email-preference-copy">
                                <span>Notificaciones del dispositivo</span>
                                <small data-push-status>{{ $portalUser->push_notifications_enabled ? 'Activadas' : 'Activar' }}</small>
                            </span>
                            <span class="portal-preference-switch{{ $portalUser->push_notifications_enabled ? ' is-active' : '' }}" data-push-switch aria-hidden="true"><span></span></span>
                        </button>
                        <button type="button" class="portal-dropdown-item" data-pwa-install hidden>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
                            Instalar Mundo Yuri
                        </button>
                        <form method="POST" action="{{ route('email-episode-notifications.update') }}" class="portal-email-preference-form" data-email-preference-form @if($portalUser->episode_email_notifications_enabled) data-confirm-disable="¿Seguro que quieres pausar los avisos? Podrías perderte nuevos episodios cuando estén disponibles." @endif>
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="enabled" value="{{ $portalUser->episode_email_notifications_enabled ? 0 : 1 }}">
                            <button type="submit" class="portal-dropdown-item portal-email-preference" role="menuitemcheckbox" aria-checked="{{ $portalUser->episode_email_notifications_enabled ? 'true' : 'false' }}">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                                <span class="portal-email-preference-copy">
                                    <span>Correos de episodios</span>
                                    <small>{{ $portalUser->episode_email_notifications_enabled ? 'Activados' : 'Pausados' }}</small>
                                </span>
                                <span class="portal-preference-switch{{ $portalUser->episode_email_notifications_enabled ? ' is-active' : '' }}" aria-hidden="true"><span></span></span>
                            </button>
                        </form>
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
        <button class="nav-toggler" id="navToggler" type="button" aria-label="Abrir menú principal" aria-controls="navLinks" aria-expanded="false">
            <span class="nav-toggler-lines" aria-hidden="true"><span></span><span></span><span></span></span>
        </button>
    </div>
    <button type="button" class="nav-mobile-backdrop" data-nav-backdrop aria-label="Cerrar menú principal" tabindex="-1"></button>
</nav>

@guest
    @include('auth.partials.portal-modal')
    <script src="{{ asset('assets/js/auth-modal.js') }}?v={{ filemtime(public_path('assets/js/auth-modal.js')) }}" defer></script>
@endguest

@once
    <script>
        const portalNavbar = document.getElementById('navbar');
        const portalNavToggler = document.getElementById('navToggler');
        const portalNavLinks = document.getElementById('navLinks');
        const portalNavBackdrop = portalNavbar?.querySelector('[data-nav-backdrop]');

        function closePortalNav() {
            portalNavLinks?.classList.remove('active');
            portalNavToggler?.classList.remove('is-open');
            portalNavToggler?.setAttribute('aria-expanded', 'false');
            portalNavToggler?.setAttribute('aria-label', 'Abrir menú principal');
            document.body.classList.remove('portal-nav-open');
        }

        function closeNotificationMenus() {
            document.querySelectorAll('[data-notification-menu].is-open').forEach(function (menu) {
                menu.classList.remove('is-open');
                menu.querySelector('[data-notification-menu-trigger]')?.setAttribute('aria-expanded', 'false');
            });
        }

        function closeMessageMenus() {
            document.querySelectorAll('[data-message-menu].is-open').forEach(function (menu) {
                menu.classList.remove('is-open');
                menu.querySelector('[data-message-menu-trigger]')?.setAttribute('aria-expanded', 'false');
            });
        }

        portalNavToggler?.addEventListener('click', function () {
            const isOpen = portalNavLinks?.classList.toggle('active') ?? false;
            portalNavToggler.classList.toggle('is-open', isOpen);
            portalNavToggler.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            portalNavToggler.setAttribute('aria-label', isOpen ? 'Cerrar menú principal' : 'Abrir menú principal');
            document.body.classList.toggle('portal-nav-open', isOpen);
            if (isOpen) {
                closeMessageMenus();
                closeNotificationMenus();
                document.querySelectorAll('[data-user-menu].is-open').forEach(function (menu) {
                    menu.classList.remove('is-open');
                    menu.querySelector('[data-user-menu-trigger]')?.setAttribute('aria-expanded', 'false');
                });
            }
        });

        portalNavBackdrop?.addEventListener('click', closePortalNav);

        portalNavLinks?.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closePortalNav);
        });

        document.addEventListener('click', function (event) {
            if (portalNavLinks?.classList.contains('active') && portalNavbar && !portalNavbar.contains(event.target)) {
                closePortalNav();
            }

            document.querySelectorAll('[data-user-menu]').forEach(function (menu) {
                const trigger = menu.querySelector('[data-user-menu-trigger]');
                const clickedTrigger = trigger && trigger.contains(event.target);

                if (clickedTrigger) {
                    closePortalNav();
                    closeMessageMenus();
                    closeNotificationMenus();
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

            document.querySelectorAll('[data-notification-menu]').forEach(function (menu) {
                const trigger = menu.querySelector('[data-notification-menu-trigger]');
                const clickedTrigger = trigger && trigger.contains(event.target);

                if (clickedTrigger) {
                    closePortalNav();
                    closeMessageMenus();
                    document.querySelectorAll('[data-user-menu].is-open').forEach(function (openMenu) {
                        openMenu.classList.remove('is-open');
                        openMenu.querySelector('[data-user-menu-trigger]')?.setAttribute('aria-expanded', 'false');
                    });
                    const willOpen = !menu.classList.contains('is-open');
                    closeNotificationMenus();
                    menu.classList.toggle('is-open', willOpen);
                    trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    return;
                }

                if (!menu.contains(event.target)) {
                    menu.classList.remove('is-open');
                    trigger?.setAttribute('aria-expanded', 'false');
                }
            });

            document.querySelectorAll('[data-message-menu]').forEach(function (menu) {
                const trigger = menu.querySelector('[data-message-menu-trigger]');
                const clickedTrigger = trigger && trigger.contains(event.target);

                if (clickedTrigger) {
                    if (Number(menu.dataset.initialUnreadCount || 0) <= 0) return;

                    event.preventDefault();
                    closePortalNav();
                    closeNotificationMenus();
                    document.querySelectorAll('[data-user-menu].is-open').forEach(function (openMenu) {
                        openMenu.classList.remove('is-open');
                        openMenu.querySelector('[data-user-menu-trigger]')?.setAttribute('aria-expanded', 'false');
                    });
                    const willOpen = !menu.classList.contains('is-open');
                    closeMessageMenus();
                    menu.classList.toggle('is-open', willOpen);
                    trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    if (willOpen) {
                        window.MundoYuriMessages?.refresh?.({ render: true });
                    }
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
            const navWasOpen = portalNavLinks?.classList.contains('active');
            closePortalNav();
            closeMessageMenus();
            closeNotificationMenus();
            if (navWasOpen) portalNavToggler?.focus();
            document.querySelectorAll('[data-user-menu].is-open').forEach(function (menu) {
                menu.classList.remove('is-open');
                const trigger = menu.querySelector('[data-user-menu-trigger]');
                trigger?.setAttribute('aria-expanded', 'false');
                trigger?.focus();
            });
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 960) closePortalNav();
        });

        document.addEventListener('submit', function (event) {
            const form = event.target.closest?.('[data-email-preference-form]');
            const message = form?.dataset.confirmDisable;

            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });

        (function () {
            const menu = document.querySelector('[data-message-menu]');
            if (!menu) return;

            const statusUrl = menu.dataset.messageStatusUrl;
            const indexUrl = menu.dataset.messageIndexUrl || '/mensajes';
            const trigger = menu.querySelector('[data-message-menu-trigger]');
            const navBadge = menu.querySelector('[data-message-badge]');
            const userBadge = document.querySelector('[data-message-user-badge]');
            const headingStatus = menu.querySelector('[data-message-heading-status]');
            const list = menu.querySelector('[data-message-list]');
            let unreadCount = Number(menu.dataset.initialUnreadCount || 0);
            let latestUnreadId = Number(menu.dataset.latestUnreadId || 0);
            let audioContext = null;

            const badgeLabel = (count) => count > 99 ? '99+' : String(count);

            const setBadge = (count) => {
                [navBadge, userBadge].forEach((badge) => {
                    if (!badge) return;
                    badge.textContent = badgeLabel(count);
                    badge.hidden = count <= 0;
                });

                menu.dataset.initialUnreadCount = String(count);
                trigger?.setAttribute('aria-label', count > 0 ? `Mensajes: ${count} sin leer` : 'Mensajes');
                if (headingStatus) headingStatus.textContent = count > 0 ? `${count} sin leer` : 'Mensajes recientes';
            };

            const avatarMarkup = (conversation) => {
                const onlineClass = conversation.is_online ? ' is-online' : '';
                if (conversation.user_avatar) {
                    return `<span class="portal-message-avatar-wrap${onlineClass}"><img src="${escapeHtml(conversation.user_avatar)}" alt=""></span>`;
                }

                return `<span class="portal-message-avatar-wrap${onlineClass}"><span class="portal-message-avatar-fallback" aria-hidden="true">${escapeHtml(conversation.user_initials || 'MY')}</span></span>`;
            };

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[character]));

            const renderConversations = (conversations) => {
                if (!list || !Array.isArray(conversations)) return;

                if (conversations.length === 0) {
                    list.innerHTML = '<p class="portal-notification-empty">No tienes conversaciones todavía.</p>';
                    return;
                }

                list.innerHTML = conversations.map((conversation) => {
                    const unread = Number(conversation.unread_count || 0);
                    return `
                        <a class="portal-message-item${unread > 0 ? ' is-unread' : ''}" href="${escapeHtml(conversation.url || indexUrl)}">
                            ${avatarMarkup(conversation)}
                            <span>
                                <strong>${escapeHtml(conversation.user_name || 'Chat')}</strong>
                                <small>${escapeHtml(conversation.preview || 'Sin mensajes')}</small>
                            </span>
                            <span class="portal-message-meta">
                                ${conversation.time ? `<time datetime="${escapeHtml(conversation.datetime || '')}">${escapeHtml(conversation.time)}</time>` : ''}
                                ${unread > 0 ? `<i aria-label="${unread} mensajes sin leer">${badgeLabel(unread)}</i>` : ''}
                            </span>
                        </a>
                    `;
                }).join('');
            };

            const unlockAudio = () => {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                audioContext ||= new AudioContext();
                if (audioContext.state === 'suspended') audioContext.resume().catch(() => {});
            };

            const playSound = () => {
                try {
                    unlockAudio();
                    if (!audioContext || audioContext.state !== 'running') return;

                    const oscillator = audioContext.createOscillator();
                    const gain = audioContext.createGain();
                    oscillator.type = 'sine';
                    oscillator.frequency.setValueAtTime(760, audioContext.currentTime);
                    oscillator.frequency.exponentialRampToValueAtTime(980, audioContext.currentTime + 0.08);
                    gain.gain.setValueAtTime(0.0001, audioContext.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.12, audioContext.currentTime + 0.015);
                    gain.gain.exponentialRampToValueAtTime(0.0001, audioContext.currentTime + 0.22);
                    oscillator.connect(gain);
                    gain.connect(audioContext.destination);
                    oscillator.start();
                    oscillator.stop(audioContext.currentTime + 0.24);
                } catch (error) {
                    // Audio may remain blocked until a user gesture.
                }
            };

            const showBrowserNotification = (message) => {
                if (!message || !('Notification' in window) || Notification.permission !== 'granted') return;

                try {
                    const notification = new Notification(message.title || 'Nuevo mensaje', {
                        body: message.body || 'Tienes un mensaje nuevo.',
                        icon: message.avatar || '/favicon.ico',
                        tag: `mundo-yuri-message-${message.id || 'latest'}`,
                    });
                    notification.onclick = () => {
                        window.focus();
                        window.location.href = message.url || indexUrl;
                    };
                } catch (error) {
                    // Some browsers only allow notifications through the service worker.
                }
            };

            const refresh = async ({ render = false, notify = false } = {}) => {
                if (!statusUrl) return null;

                const response = await fetch(statusUrl, {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) return null;

                const data = await response.json();
                const nextUnreadCount = Number(data.unread_count || 0);
                const nextLatestUnreadId = Number(data.latest_unread_message_id || 0);
                const hasNewUnread = nextUnreadCount > unreadCount && nextLatestUnreadId !== latestUnreadId;

                setBadge(nextUnreadCount);
                if (render || menu.classList.contains('is-open')) renderConversations(data.conversations || []);

                const latestUnreadPath = data.latest_unread?.url
                    ? new URL(data.latest_unread.url, window.location.origin).pathname
                    : '';
                const activeVisibleChatHasThisMessage = /^\/mensajes\/[^/]+/.test(window.location.pathname)
                    && latestUnreadPath === window.location.pathname
                    && !document.hidden;

                if (notify && hasNewUnread && !activeVisibleChatHasThisMessage) {
                    playSound();
                    showBrowserNotification(data.latest_unread);
                }

                unreadCount = nextUnreadCount;
                latestUnreadId = nextLatestUnreadId;
                menu.dataset.latestUnreadId = String(latestUnreadId);

                return data;
            };

            window.MundoYuriMessages = { refresh };
            setBadge(unreadCount);
            document.addEventListener('pointerdown', unlockAudio, { once: true });
            document.addEventListener('keydown', unlockAudio, { once: true });

            const interval = window.setInterval(() => refresh({ notify: true }).catch(() => {}), 15000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) refresh({ render: menu.classList.contains('is-open') }).catch(() => {});
            });
            window.addEventListener('beforeunload', () => window.clearInterval(interval));
        })();
    </script>
@endonce
