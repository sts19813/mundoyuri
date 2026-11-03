const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const sandbox = { module: { exports: {} } };
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/assets/js/chat-presence.js'), 'utf8'), sandbox);
const presence = sandbox.module.exports;

test('formats each heartbeat in the viewer timezone and local calendar day', () => {
    const instant = '2026-11-02T03:40:00Z';
    const now = '2026-11-02T05:10:00Z';
    process.env.TZ = 'America/Merida';
    assert.equal(presence.format(instant, now), 'Última vez hoy a las 9:40pm');
    process.env.TZ = 'America/Bogota';
    assert.equal(presence.format(instant, now), 'Última vez ayer a las 10:40pm');
    process.env.TZ = 'Europe/Madrid';
    assert.equal(presence.format(instant, now), 'Última vez hoy a las 4:40am');
});

test('uses the offset in effect at each heartbeat across daylight saving', () => {
    process.env.TZ = 'Europe/Madrid';
    const now = '2026-10-25T04:00:00Z';
    assert.equal(presence.format('2026-10-25T00:30:00Z', now), 'Última vez hoy a las 2:30am');
    assert.equal(presence.format('2026-10-25T01:30:00Z', now), 'Última vez hoy a las 2:30am');
    assert.equal(presence.format('not-a-date', now), 'Última conexión');
});
