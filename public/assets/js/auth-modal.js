(function () {
    const overlay = document.querySelector('[data-auth-overlay]');
    if (!overlay) return;

    const dialog = overlay.querySelector('[data-auth-dialog]');
    const navLogin = document.querySelector('.nav-login');
    const tabs = Array.from(dialog.querySelectorAll('[data-auth-tab]'));
    const panels = Array.from(dialog.querySelectorAll('[data-auth-panel]'));
    let previousFocus = null;

    function isMobile() {
        return window.matchMedia('(max-width: 700px)').matches;
    }

    function updatePlacement() {
        dialog.setAttribute('aria-modal', isMobile() ? 'true' : 'false');
        if (isMobile() || !navLogin) return;

        const anchor = navLogin.getBoundingClientRect();
        const top = Math.min(anchor.bottom + 12, window.innerHeight - 180);
        const right = Math.max(16, window.innerWidth - anchor.right);
        overlay.style.setProperty('--auth-anchor-top', Math.max(16, top) + 'px');
        overlay.style.setProperty('--auth-anchor-right', right + 'px');
    }

    function selectTab(intent) {
        tabs.forEach(function (tab) {
            const selected = tab.dataset.authTab === intent;
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.tabIndex = selected ? 0 : -1;
        });
        panels.forEach(function (panel) {
            panel.hidden = panel.dataset.authPanel !== intent;
        });
    }

    function open(intent) {
        previousFocus = document.activeElement;
        selectTab(intent === 'register' ? 'register' : 'login');
        updatePlacement();
        overlay.hidden = false;
        document.body.classList.add('portal-auth-open');
        navLogin?.setAttribute('aria-expanded', 'true');
        if (typeof closePortalNav === 'function') closePortalNav();
        dialog.querySelector('[data-auth-tab="' + (intent === 'register' ? 'register' : 'login') + '"]').focus();
    }

    function close(restoreFocus = true) {
        overlay.hidden = true;
        document.body.classList.remove('portal-auth-open');
        navLogin?.setAttribute('aria-expanded', 'false');
        if (restoreFocus) previousFocus?.focus?.();
    }

    document.addEventListener('click', function (event) {
        const opener = event.target.closest?.('a[href], [data-auth-open]');
        if (opener && !overlay.contains(opener)) {
            const url = opener.href ? new URL(opener.href, window.location.href) : null;
            const intent = opener.dataset.authOpen || (url?.origin === window.location.origin && url.pathname === '/register' ? 'register' : null)
                || (url?.origin === window.location.origin && url.pathname === '/login' ? 'login' : null);
            if (intent) {
                event.preventDefault();
                if (!overlay.hidden && opener === navLogin) close();
                else open(intent);
                return;
            }
        }

        if (event.target.closest?.('[data-auth-close]') && overlay.contains(event.target)) {
            close();
            return;
        }

        if (!overlay.hidden && !isMobile() && !dialog.contains(event.target)) close(false);
    });

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () { selectTab(tab.dataset.authTab); });
        tab.addEventListener('keydown', function (event) {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
            event.preventDefault();
            const next = tabs.find(function (item) { return item !== tab; });
            selectTab(next.dataset.authTab);
            next.focus();
        });
    });

    document.addEventListener('keydown', function (event) {
        if (overlay.hidden) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            close();
        }
        if (event.key !== 'Tab' || !isMobile()) return;
        const focusable = Array.from(dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])'))
            .filter(function (item) { return item.getClientRects().length > 0 && item.tabIndex >= 0; });
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    window.addEventListener('resize', function () {
        if (!overlay.hidden) updatePlacement();
    });

    dialog.querySelectorAll('[data-auth-form]').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const feedback = form.querySelector('[data-auth-feedback]');
            const submit = form.querySelector('[type="submit"]');
            feedback.hidden = true;
            feedback.textContent = '';
            submit.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const result = await response.json();
                if (response.ok && result.redirect) {
                    window.location.assign(result.redirect);
                    return;
                }
                const errors = result.errors ? Object.values(result.errors).flat() : [];
                feedback.textContent = errors[0] || result.message || 'No se pudo completar el acceso. Intenta de nuevo.';
            } catch (error) {
                feedback.textContent = 'No se pudo conectar. Comprueba tu conexión e intenta de nuevo.';
            } finally {
                feedback.hidden = false;
                submit.disabled = false;
            }
        });
    });

    if (dialog.dataset.flashIntent) {
        open(dialog.dataset.flashIntent);
        const feedback = dialog.querySelector('[data-auth-panel="' + dialog.dataset.flashIntent + '"] [data-auth-feedback]');
        if (feedback && dialog.dataset.flashError) {
            feedback.textContent = dialog.dataset.flashError;
            feedback.hidden = false;
        }
    }
})();
