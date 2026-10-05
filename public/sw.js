const CACHE_VERSION = 'mundo-yuri-pwa-v2';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const OFFLINE_URL = '/offline.html';
const STATIC_ASSETS = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/assets/img/pwa/icon-192.png',
    '/assets/img/pwa/icon-512.png',
    '/assets/img/pwa/badge-96.png',
    '/assets/img/logos/mundo-yuri-logo.svg',
    '/assets/img/logos/mundo-yuri-logo-blanco.svg',
    '/favicon.svg',
    '/favicon.ico',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => !key.startsWith(CACHE_VERSION)).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    if (url.origin === self.location.origin && shouldCacheStatic(url.pathname)) {
        event.respondWith(staleWhileRevalidate(request));
    }
});

self.addEventListener('push', (event) => {
    const payload = parsePushPayload(event);
    const title = payload.title || 'Mundo Yuri';
    const options = {
        body: payload.body || 'Tienes una nueva notificación.',
        icon: payload.icon || '/assets/img/pwa/icon-192.png',
        badge: payload.badge || '/assets/img/pwa/badge-96.png',
        image: payload.image,
        tag: payload.tag || payload.data?.kind || 'mundo-yuri-notification',
        data: payload.data || {},
        actions: payload.actions || [],
        vibrate: payload.vibrate || [80, 40, 80],
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const destination = event.notification.data?.url || '/notificaciones';
    const destinationUrl = new URL(destination, self.location.origin).href;

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            for (const client of clients) {
                if (client.url === destinationUrl && 'focus' in client) {
                    return client.focus();
                }
            }

            return self.clients.openWindow(destinationUrl);
        })
    );
});

function shouldCacheStatic(pathname) {
    return pathname.startsWith('/assets/')
        || pathname.startsWith('/build/')
        || pathname === '/manifest.webmanifest'
        || pathname === '/offline.html';
}

async function staleWhileRevalidate(request) {
    const cache = await caches.open(STATIC_CACHE);
    const cached = await cache.match(request);
    const network = fetch(request)
        .then((response) => {
            if (response.ok) {
                cache.put(request, response.clone());
            }

            return response;
        })
        .catch(() => cached);

    return cached || network;
}

function parsePushPayload(event) {
    if (!event.data) {
        return {};
    }

    try {
        return event.data.json();
    } catch (error) {
        return { title: 'Mundo Yuri', body: event.data.text() };
    }
}
