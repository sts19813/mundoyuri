(function () {
    const configNode = document.getElementById('mundo-yuri-pwa-config');
    const config = configNode ? JSON.parse(configNode.textContent || '{}') : {};
    const controls = Array.from(document.querySelectorAll('[data-push-toggle]'));
    const installButtons = Array.from(document.querySelectorAll('[data-pwa-install]'));
    let deferredInstallPrompt = null;
    let serviceWorkerRegistration = null;

    const supported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;

    registerServiceWorker();
    bindInstallPrompt();
    bindPushControls();

    async function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            return;
        }

        try {
            serviceWorkerRegistration = await navigator.serviceWorker.register('/sw.js');
            await navigator.serviceWorker.ready;
        } catch (error) {
            updatePushControls({ disabled: true, status: 'No disponible', active: false });
        }
    }

    function bindInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredInstallPrompt = event;
            installButtons.forEach((button) => button.hidden = false);
        });

        installButtons.forEach((button) => {
            button.hidden = true;
            button.addEventListener('click', async () => {
                if (!deferredInstallPrompt) {
                    return;
                }

                deferredInstallPrompt.prompt();
                await deferredInstallPrompt.userChoice;
                deferredInstallPrompt = null;
                installButtons.forEach((item) => item.hidden = true);
            });
        });
    }

    function bindPushControls() {
        if (!controls.length) {
            return;
        }

        refreshPushState();

        controls.forEach((control) => {
            control.addEventListener('click', async (event) => {
                event.preventDefault();
                setPushControlsBusy(true);

                try {
                    const subscription = await currentSubscription();

                    if (subscription) {
                        await subscription.unsubscribe();
                        await deleteSubscription(subscription);
                        updatePushControls({ active: false, status: 'Desactivadas' });
                        return;
                    }

                    await subscribeCurrentBrowser();
                } catch (error) {
                    updatePushControls({
                        active: false,
                        disabled: !supported || Notification.permission === 'denied',
                        status: error.message || 'No disponible',
                    });
                } finally {
                    setPushControlsBusy(false);
                }
            });
        });
    }

    async function refreshPushState() {
        if (!supported || !config.pushPublicKey || !config.routes?.pushStore) {
            updatePushControls({ disabled: true, status: 'No disponible', active: false });
            return;
        }

        const subscription = await currentSubscription();
        updatePushControls({
            active: Boolean(subscription && config.pushEnabled),
            disabled: Notification.permission === 'denied',
            status: subscription && config.pushEnabled ? 'Activadas' : 'Activar',
        });
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

        const registration = serviceWorkerRegistration || await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.subscribe({
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

        const registration = serviceWorkerRegistration || await navigator.serviceWorker.ready;

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

        config.pushEnabled = false;
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
        controls.forEach((control) => {
            control.disabled = disabled;
            control.classList.toggle('is-active', Boolean(active));
            control.setAttribute('aria-pressed', active ? 'true' : 'false');

            const switchControl = control.querySelector('[data-push-switch]');
            switchControl?.classList.toggle('is-active', Boolean(active));

            const statusNode = control.querySelector('[data-push-status]');
            if (statusNode && status) {
                statusNode.textContent = status;
            }
        });
    }

    function setPushControlsBusy(busy) {
        controls.forEach((control) => {
            control.disabled = busy;
            control.classList.toggle('is-loading', busy);
        });
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
