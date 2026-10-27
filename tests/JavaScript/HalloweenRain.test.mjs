import assert from 'node:assert/strict';
import fs from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../public/assets/js/halloween-theme.js', import.meta.url), 'utf8');

function harness({ compact = false, reduced = false } = {}) {
    let now = 0, nextId = 0, seed = 13;
    const timers = new Map(), listeners = {}, created = [];
    const motion = { matches: reduced, addEventListener: (_, fn) => listeners.motion = fn };
    const media = { matches: compact, addEventListener: (_, fn) => listeners.compact = fn };
    const slots = Array.from({ length: 12 }, () => ({
        hidden: true, style: {}, src: undefined,
        animate(frames, options) {
            let finish;
            const animation = {
                state: 'running', frames, options,
                finished: new Promise(resolve => { finish = resolve; }),
                pause() { this.state = 'paused'; },
                play() { this.state = 'running'; },
                cancel() { this.state = 'cancelled'; },
                finish() { finish(); },
            };
            this.animation = animation;
            created.push(animation);
            return animation;
        },
    }));
    const rain = { querySelectorAll: () => slots, dataset: {
        pumpkinSrc: '/assets/img/seasonal/pumpkin-anime-grin.webp',
        pumpkinAltSrc: '/assets/img/seasonal/pumpkin-anime-smile.webp',
    } };
    const layer = { querySelector: () => rain, querySelectorAll: () => [], classList: { toggle() {} } };
    const document = {
        hidden: false, fullscreenElement: null,
        querySelector: selector => selector.includes('data-season-theme') ? layer : null,
        addEventListener: (event, fn) => listeners[event] = fn,
    };
    const window = {
        innerWidth: compact ? 390 : 1440, innerHeight: 900,
        matchMedia: query => query.includes('reduced') ? motion : media,
        setTimeout(fn, delay) { timers.set(++nextId, { fn, at: now + delay }); return nextId; },
        clearTimeout(id) { timers.delete(id); },
        addEventListener: (event, fn) => listeners[event] = fn,
    };
    const random = Object.create(Math);
    random.random = () => { seed = seed * 16807 % 2147483647; return seed / 2147483647; };
    vm.runInNewContext(source, {
        document, window, Math: random, Date: { now: () => now },
        sessionStorage: { getItem: () => null, setItem() {} },
    });
    const advance = amount => {
        const end = now + amount;
        for (let guard = 0; guard < 1000; guard++) {
            const next = [...timers].filter(([, timer]) => timer.at <= end).sort((a, b) => a[1].at - b[1].at)[0];
            if (!next) break;
            now = next[1].at;
            timers.delete(next[0]);
            next[1].fn();
        }
        now = end;
    };
    return { slots, created, timers, listeners, document, motion, media, advance };
}

test('no theme does not start effects or touch window APIs', () => {
    vm.runInNewContext(source, { document: { querySelector: () => null } });
});

test('desktop rain loads lazily, varies paths and reuses twelve slots', async () => {
    const app = harness();
    assert.ok(app.slots.every(image => image.hidden && image.src === undefined));
    app.advance(30000);
    assert.ok(app.created.length > 0 && app.created.length <= 12);
    assert.equal(app.slots.filter(image => !image.hidden).length, app.created.length);
    for (const animation of app.created) {
        assert.ok(animation.options.duration >= 14000 && animation.options.duration <= 24000);
        assert.ok(animation.frames.every(frame => Object.keys(frame).every(key => ['transform', 'opacity', 'offset'].includes(key))));
    }
    assert.notEqual(app.created[0].frames[0].transform, app.created[1].frames[0].transform);
    app.slots[0].animation.finish();
    await Promise.resolve();
    assert.equal(app.slots[0].hidden, true);
    const beforeNextSpawn = app.created.length;
    app.advance(9000);
    assert.equal(app.slots.length, 12);
    assert.ok(app.created.length > beforeNextSpawn && app.created.length <= 13);
    assert.ok(app.slots.filter(image => !image.hidden).length <= 12);
    assert.ok(app.slots.every(image => parseFloat(image.style.width) >= 32 && parseFloat(image.style.width) <= 48));
});

test('mobile rain never allocates more than six active particles', () => {
    const app = harness({ compact: true });
    app.advance(60000);
    assert.ok(app.created.length > 0 && app.created.length <= 6);
    assert.equal(app.slots[6].src, undefined);
    assert.ok(app.slots.filter(image => !image.hidden).every(image => parseFloat(image.style.width) >= 26 && parseFloat(image.style.width) <= 36));
});

test('rain pauses in background and fullscreen, then cleans up on page exit', () => {
    const app = harness();
    app.advance(18000);
    app.document.hidden = true;
    app.listeners.visibilitychange();
    assert.ok(app.created.every(animation => animation.state === 'paused'));
    assert.equal(app.timers.size, 0);
    app.advance(30000);
    const count = app.created.length;
    app.document.hidden = false;
    app.listeners.visibilitychange();
    assert.ok(app.created.every(animation => animation.state === 'running'));
    assert.equal(app.created.length, count);
    app.document.fullscreenElement = {};
    app.listeners.fullscreenchange();
    assert.ok(app.created.every(animation => animation.state === 'paused'));
    assert.equal(app.timers.size, 0);
    app.listeners.pagehide();
    assert.ok(app.slots.every(image => image.hidden));
    assert.ok(app.created.every(animation => animation.state === 'cancelled'));
    assert.equal(app.timers.size, 0);
});

test('reduced motion does not request pumpkin images or schedule work', () => {
    const app = harness({ reduced: true });
    app.advance(60000);
    assert.equal(app.timers.size, 0);
    assert.equal(app.created.length, 0);
    assert.ok(app.slots.every(image => image.src === undefined && image.hidden));
});

test('switching to mobile or reduced motion cancels excess rain immediately', () => {
    const app = harness();
    app.advance(30000);
    app.media.matches = true;
    app.listeners.compact();
    assert.equal(app.slots[6].hidden, true);
    assert.equal(app.slots[6].animation.state, 'cancelled');
    app.motion.matches = true;
    app.listeners.motion();
    assert.ok(app.slots.every(image => image.hidden));
    assert.equal(app.timers.size, 0);
});
