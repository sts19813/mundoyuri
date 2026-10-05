import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { test } from 'node:test';
import { strict as assert } from 'node:assert';

const script = readFileSync(new URL('../../public/assets/js/pwa.js', import.meta.url), 'utf8');

async function renderNotice({ mode = 'browser', enabled = true, standalone = false } = {}) {
    const fields = new Map();
    const control = {
        classList: { toggle() {} },
        setAttribute() {},
        addEventListener() {},
        querySelector: () => null,
    };
    const notice = {
        hidden: true,
        querySelector(selector) {
            if (selector === '[data-pwa-install-help]' || selector === '[data-device-dismiss]') return null;
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
        addEventListener() {},
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
    runInNewContext(script, { window, navigator, document, Notification: notification });
    await new Promise(setImmediate);
    return { notice, fields, displayModes };
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
