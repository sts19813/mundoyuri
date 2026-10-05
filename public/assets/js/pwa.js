(function () {
    const configNode = document.getElementById('mundo-yuri-pwa-config');
    const config = configNode ? JSON.parse(configNode.textContent || '{}') : {};
    const controls = Array.from(document.querySelectorAll('[data-push-toggle]'));
    const installButtons = Array.from(document.querySelectorAll('[data-pwa-install]'));
    const deviceNotice = document.querySelector('[data-device-notice]');
    const noticeDismissalKey = 'mundo-yuri-device-notice-dismissed-date';
    const appDisplayModes = ['standalone', 'window-controls-overlay', 'minimal-ui']
        .map((mode) => window.matchMedia(`(display-mode: ${mode})`));
    let deferredInstallPrompt = null;
    let appInstalled = isAppWindow();
    let noticeDismissedOn = deviceNotice ? readNoticeDismissal() : null;
    let pushBusy = false;
    let pushState = { active: false, disabled: true, status: 'Comprobando...' };

    const supported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;

    const serviceWorkerReady = registerServiceWorker();
    bindInstallPrompt();
    bindPushControls();
    deviceNotice?.querySelector('[data-device-dismiss]')?.addEventListener('click', () => {
        noticeDismissedOn = localDate();
        try {
            window.localStorage.setItem(noticeDismissalKey, noticeDismissedOn);
        } catch (error) {
            // Keep the dismissal for this page when browser storage is unavailable.
        }
        updateDeviceNotice();
    });
    if (deviceNotice) {
        window.addEventListener('storage', (event) => {
            if (event.key === noticeDismissalKey || event.key === null) {
                noticeDismissedOn = readNoticeDismissal();
                updateDeviceNotice();
            }
        });
    }

    function localDate() {
        const date = new Date();
        return [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
    }

    function readNoticeDismissal() {
        try {
            return window.localStorage.getItem(noticeDismissalKey);
        } catch (error) {
            return null;
        }
    }

    async function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            return null;
        }

        try {
            await navigator.serviceWorker.register('/sw.js');
            return await navigator.serviceWorker.ready;
        } catch (error) {
            return null;
        }
    }

    function bindInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredInstallPrompt = event;
            updateInstallControls();
        });

        window.addEventListener('appinstalled', () => {
            appInstalled = true;
            deferredInstallPrompt = null;
            updateInstallControls();
            updateDeviceNotice();
        });
        appDisplayModes.forEach((displayMode) => {
            displayMode.addEventListener('change', () => {
                appInstalled = isAppWindow();
                updateInstallControls();
                updateDeviceNotice();
            });
        });

        installButtons.forEach((button) => {
            button.addEventListener('click', async () => {
                if (!deferredInstallPrompt) {
                    return;
                }

                const prompt = deferredInstallPrompt;
                try {
                    await prompt.prompt();
                    await prompt.userChoice;
                } finally {
                    deferredInstallPrompt = null;
                    updateInstallControls();
                }
            });
        });
        updateInstallControls();
    }

    function isAppWindow() {
        return appDisplayModes.some((displayMode) => displayMode.matches) || navigator.standalone === true;
    }

    function updateInstallControls() {
        installButtons.forEach((button) => button.hidden = appInstalled || !deferredInstallPrompt);
        const help = deviceNotice?.querySelector('[data-pwa-install-help]');
        if (!help) return;
        help.hidden = appInstalled || Boolean(deferredInstallPrompt);
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent)
            || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        help.querySelector('[data-pwa-install-instructions]').textContent = isIOS
            ? 'En Safari, toca Compartir y luego Agregar a pantalla de inicio. Abre MundoYuri desde su icono para activar las notificaciones.'
            : 'En el menú del navegador, busca Instalar aplicación o Agregar a pantalla de inicio. Si ya la instalaste, abre MundoYuri desde su icono.';
    }

    function bindPushControls() {
        if (!controls.length) {
            return;
        }

        refreshPushState();

        controls.forEach((control) => {
            control.addEventListener('click', async (event) => {
                event.preventDefault();
                if (pushBusy || pushState.disabled) return;
                setPushControlsBusy(true);

                try {
                    if (pushState.active && !control.hasAttribute('data-push-enable')) {
                        const subscription = await currentSubscription();
                        if (!subscription) {
                            await refreshPushState();
                            return;
                        }
                        await deleteSubscription(subscription);
                        await subscription.unsubscribe();
                        updatePushControls({ active: false, status: 'Desactivadas' });
                        return;
                    }

                    await subscribeCurrentBrowser();
                } catch (error) {
                    updatePushControls({
                        active: false,
                        disabled: !supported || window.Notification?.permission === 'denied',
                        status: error.message || 'No disponible',
                    });
                } finally {
                    setPushControlsBusy(false);
                }
            });
        });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && !pushBusy) refreshPushState();
        });
    }

    async function refreshPushState() {
        if (!supported || !config.pushPublicKey || !config.routes?.pushStore) {
            updatePushControls({ disabled: true, status: 'No disponible', active: false });
            return;
        }

        try {
            const subscription = await currentSubscription();
            const active = Boolean(subscription && config.pushEnabled && Notification.permission === 'granted');
            updatePushControls({
                active,
                disabled: Notification.permission === 'denied',
                status: active ? 'Activadas' : (Notification.permission === 'denied' ? 'Permiso bloqueado' : 'Activar'),
            });
        } catch (error) {
            updatePushControls({ active: false, disabled: true, status: 'No disponible' });
        }
    }

    async function subscribeCurrentBrowser() {
        if (!supported) {
            throw new Error('Tu navegador no permite notificaciones web.');
        }

        if (!config.pushPublicKey) {
            throw new Error('Las notificaciones aún no están configuradas.');
        }

        const permission = await Notification.requestPermission();

        if (permission !== 'granted') {
            throw new Error(permission === 'denied' ? 'Permiso bloqueado' : 'Permiso pendiente');
        }

        const registration = await serviceWorkerReady;
        if (!registration) throw new Error('No se pudo preparar este dispositivo. Recarga e intenta de nuevo.');
        const subscription = await registration.pushManager.getSubscription() || await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(config.pushPublicKey),
        });

        await saveSubscription(subscription);
        updatePushControls({ active: true, status: 'Activadas' });
    }

    async function currentSubscription() {
        if (!supported) {
            return null;
        }

        const registration = await serviceWorkerReady;
        if (!registration) throw new Error('No disponible');

        return registration.pushManager.getSubscription();
    }

    async function saveSubscription(subscription) {
        const payload = subscription.toJSON();
        payload.contentEncoding = 'aes128gcm';

        const response = await fetch(config.routes.pushStore, {
            method: 'POST',
            headers: jsonHeaders(),
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            throw new Error('No se pudo guardar este dispositivo.');
        }

        config.pushEnabled = true;
    }

    async function deleteSubscription(subscription) {
        const response = await fetch(config.routes.pushDestroy, {
            method: 'DELETE',
            headers: jsonHeaders(),
            body: JSON.stringify({ endpoint: subscription.endpoint }),
        });

        if (!response.ok) {
            throw new Error('No se pudo desactivar este dispositivo.');
        }

        const data = await response.json();
        config.pushEnabled = Boolean(data.enabled);
    }

    function jsonHeaders() {
        return {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
        };
    }

    function updatePushControls({ active, status, disabled = false }) {
        pushState = { active: Boolean(active), status, disabled };
        controls.forEach((control) => {
            control.disabled = disabled || pushBusy;
            control.classList.toggle('is-active', Boolean(active));
            control.setAttribute('aria-pressed', active ? 'true' : 'false');

            const switchControl = control.querySelector('[data-push-switch]');
            switchControl?.classList.toggle('is-active', Boolean(active));

            const statusNode = control.querySelector('[data-push-status]');
            if (statusNode && status) {
                statusNode.textContent = status;
            }
        });
        updateDeviceNotice();
    }

    function setPushControlsBusy(busy) {
        pushBusy = busy;
        controls.forEach((control) => {
            control.disabled = busy || pushState.disabled;
            control.classList.toggle('is-loading', busy);
        });
        const enableButton = deviceNotice?.querySelector('[data-push-enable]');
        if (enableButton) enableButton.textContent = busy ? 'Activando...' : 'Activar notificaciones';
    }

    function updateDeviceNotice() {
        if (!deviceNotice) return;
        const { active, status } = pushState;
        deviceNotice.hidden = noticeDismissedOn === localDate() || (appInstalled && active);
        deviceNotice.querySelector('[data-device-title]').textContent = active
            ? 'Lleva MundoYuri contigo'
            : 'Que no se te pase ningún mensaje';
        deviceNotice.querySelector('[data-device-description]').textContent = active
            ? 'Ya tienes las notificaciones activadas. Instala la app para tener tus conversaciones y tu comunidad GL/yuri siempre a mano.'
            : (appInstalled
                ? 'Activa las notificaciones para enterarte de tus mensajes y novedades GL/yuri, incluso cuando la app esté cerrada.'
                : 'Instala la app de MundoYuri o activa las notificaciones para enterarte de tus mensajes y novedades GL/yuri, incluso cuando no estés aquí.');
        deviceNotice.querySelector('[data-push-enable]').hidden = active;
        const feedback = deviceNotice.querySelector('[data-device-status]');
        feedback.hidden = !active && ['Activar', 'Desactivadas', 'Comprobando...'].includes(status);
        feedback.textContent = active ? 'Notificaciones activadas en este dispositivo.'
            : (window.Notification?.permission === 'denied'
                ? 'Las notificaciones están bloqueadas. Permítelas en los ajustes del navegador o del dispositivo y vuelve a esta página.'
                : (status === 'No disponible'
                    ? 'Este navegador no puede activar los avisos ahora. Prueba desde la app instalada o desde un navegador compatible.'
                    : status));
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; i += 1) {
            outputArray[i] = rawData.charCodeAt(i);
        }

        return outputArray;
    }
})();
