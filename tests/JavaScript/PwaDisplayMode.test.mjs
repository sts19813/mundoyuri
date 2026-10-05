import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { test } from 'node:test';
import { strict as assert } from 'node:assert';

const script = readFileSync(new URL('../../public/assets/js/pwa.js', import.meta.url), 'utf8');

async function renderNotice({ mode = 'browser', enabled = true, standalone = false, storage = new Map(), storageBlocked = false, now = new Date(2026, 9, 5, 12).getTime() } = {}) {
    const fields = new Map();
    const dismiss = { addEventListener(event, listener) { this.click = listener; } };
    const windowListeners = new Map();
    const control = {
        classList: { toggle() {} },
        setAttribute() {},
        addEventListener() {},
        querySelector: () => null,
    };
    const notice = {
        hidden: true,
        querySelector(selector) {
            if (selector === '[data-pwa-install-help]') return null;
            if (selector === '[data-device-dismiss]') return dismiss;
            if (selector === '[data-push-enable]') return control;
            if (!fields.has(selector)) fields.set(selector, {});
            return fields.get(selector);
        },
    };
    const displayModes = new Map();
    const notification = { permission: 'granted' };
    const navigator = {
        standalone,
        serviceWorker: {
            register: async () => ({}),
            ready: Promise.resolve({ pushManager: { getSubscription: async () => enabled ? {} : null } }),
        },
    };
    const window = {
        Notification: notification,
        PushManager: function () {},
        addEventListener(event, listener) { windowListeners.set(event, listener); },
        localStorage: {
            getItem(key) {
                if (storageBlocked) throw new Error('Storage unavailable');
                return storage.get(key) ?? null;
            },
            setItem(key, value) {
                if (storageBlocked) throw new Error('Storage unavailable');
                storage.set(key, value);
            },
        },
        matchMedia(query) {
            const displayMode = { matches: query === `(display-mode: ${mode})`, addEventListener(event, listener) { this.listener = listener; } };
            displayModes.set(query, displayMode);
            return displayMode;
        },
    };
    const config = { pushPublicKey: 'test-key', pushEnabled: enabled, routes: { pushStore: '/subscription' } };
    const document = {
        getElementById: () => ({ textContent: JSON.stringify(config) }),
        querySelector: () => notice,
        querySelectorAll: selector => selector === '[data-push-toggle]' ? [control] : [],
        addEventListener() {},
    };
    class TestDate extends Date {
        constructor(...args) { super(...(args.length ? args : [now])); }
    }
    runInNewContext(script, { window, navigator, document, Notification: notification, Date: TestDate });
    await new Promise(setImmediate);
    return { notice, fields, displayModes, dismiss, storage, windowListeners };
}

for (const mode of ['standalone', 'window-controls-overlay', 'minimal-ui']) {
    test(`installed ${mode} app with notifications hides the recommendation`, async () => {
        const { notice } = await renderNotice({ mode });
        assert.equal(notice.hidden, true);
    });
    test(`installed ${mode} app without notifications only recommends enabling them`, async () => {
        const { notice, fields } = await renderNotice({ mode, enabled: false });
        assert.equal(notice.hidden, false);
        assert.match(fields.get('[data-device-description]').textContent, /^Activa las notificaciones/);
    });
}

test('iOS standalone apps hide the recommendation with notifications enabled', async () => {
    const { notice } = await renderNotice({ standalone: true });
    assert.equal(notice.hidden, true);
});

test('a regular browser still recommends installation', async () => {
    const { notice, fields } = await renderNotice();
    assert.equal(notice.hidden, false);
    assert.match(fields.get('[data-device-description]').textContent, /Instala la app/);
});

test('switching between installed display modes keeps the recommendation hidden', async () => {
    const { notice, displayModes } = await renderNotice({ mode: 'window-controls-overlay' });
    displayModes.get('(display-mode: window-controls-overlay)').matches = false;
    displayModes.get('(display-mode: standalone)').matches = true;
    displayModes.get('(display-mode: standalone)').listener();
    assert.equal(notice.hidden, true);
});

test('closing the notice suppresses it across page loads for the rest of the local day', async () => {
    const now = new Date(2026, 9, 5, 23, 50).getTime();
    const firstPage = await renderNotice({ now });
    assert.equal(firstPage.notice.hidden, false);
    firstPage.dismiss.click();
    assert.equal(firstPage.notice.hidden, true);
    assert.equal(firstPage.storage.get('mundo-yuri-device-notice-dismissed-date'), '2026-10-05');

    const nextPage = await renderNotice({ storage: firstPage.storage, now: new Date(2026, 9, 5, 23, 59).getTime() });
    assert.equal(nextPage.notice.hidden, true);
    const nextDay = await renderNotice({ storage: firstPage.storage, now: new Date(2026, 9, 6, 0, 1).getTime() });
    assert.equal(nextDay.notice.hidden, false);
});

test('closing the notice in another tab hides it here too', async () => {
    const page = await renderNotice();
    page.storage.set('mundo-yuri-device-notice-dismissed-date', '2026-10-05');
    page.windowListeners.get('storage')({ key: 'mundo-yuri-device-notice-dismissed-date' });
    assert.equal(page.notice.hidden, true);
});

test('blocked browser storage still allows closing the notice for the current page', async () => {
    const page = await renderNotice({ storageBlocked: true });
    assert.equal(page.notice.hidden, false);
    page.dismiss.click();
    assert.equal(page.notice.hidden, true);
    page.displayModes.get('(display-mode: standalone)').listener();
    assert.equal(page.notice.hidden, true);
});
