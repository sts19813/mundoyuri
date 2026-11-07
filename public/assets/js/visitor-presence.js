(function () {
    const configElement = document.getElementById('mundo-yuri-visitor-presence-config');

    if (!configElement) return;

    let config;
    try { config = JSON.parse(configElement.textContent || '{}'); } catch (error) { return; }
    if (!config || !config.endpoint || !config.csrfToken) return;

    const storageKey = 'mundo_yuri_visitor_presence_session_id';
    const intervalMs = 30000;
    let intervalId = null;

    function uuid() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') return window.crypto.randomUUID();
        return '10000000-1000-4000-8000-100000000000'.replace(/[018]/g, function (value) {
            return (Number(value) ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> Number(value) / 4).toString(16);
        });
    }

    function sessionId() {
        try {
            const existing = window.sessionStorage.getItem(storageKey);
            if (existing) return existing;
            const next = uuid();
            window.sessionStorage.setItem(storageKey, next);
            return next;
        } catch (error) { return uuid(); }
    }

    const id = sessionId();
    function send() {
        if (document.visibilityState === 'hidden') return;
        fetch(config.endpoint, {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
            credentials: 'same-origin',
            body: JSON.stringify({ session_id: id, path: window.location.pathname + window.location.search, title: document.title || null }),
        }).catch(function () {});
    }
    function start() {
        if (intervalId || document.visibilityState === 'hidden') return;
        send();
        intervalId = window.setInterval(send, intervalMs);
    }
    function stop() {
        if (!intervalId) return;
        window.clearInterval(intervalId);
        intervalId = null;
    }
    document.addEventListener('visibilitychange', function () { document.visibilityState === 'hidden' ? stop() : start(); });
    start();
})();
