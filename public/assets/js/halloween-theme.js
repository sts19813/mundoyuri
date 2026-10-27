(() => {
    const layer = document.querySelector('[data-season-theme="halloween"]');
    if (!layer) return;

    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const compact = window.matchMedia('(max-width: 600px)');
    const walkers = [...layer.querySelectorAll('[data-halloween-walker]')];
    const glitch = document.querySelector('.halloween-glitch');
    const animations = new Map();
    const sessionKey = 'mundo-yuri-halloween-glitch-v2';
    const maxGlitchesPerSession = 4;
    let disposed = false;
    let glitchTimer;
    let hideTimer;
    let glitchCount = 0;
    let nextGlitchAt = Date.now() + 25000 + Math.random() * 15000;

    try {
        const saved = JSON.parse(sessionStorage.getItem(sessionKey) || 'null');
        if (saved && Number.isFinite(saved.count) && Number.isFinite(saved.nextAt)) {
            glitchCount = saved.count;
            nextGlitchAt = saved.nextAt;
        }
    } catch { /* Storage may be unavailable; the page still has a limit. */ }

    const saveGlitch = () => {
        try {
            sessionStorage.setItem(sessionKey, JSON.stringify({ count: glitchCount, nextAt: nextGlitchAt }));
        } catch { /* Keep the in-memory limit. */ }
    };
    const paused = () => disposed || document.hidden || motion.matches || Boolean(document.fullscreenElement);
    const visibleWalker = (element) => !compact.matches
        || !(element.matches('.halloween-creature--spider.halloween-creature--two, .halloween-creature--ant.halloween-creature--three, .halloween-creature--ant.halloween-creature--five'));
    const randomPoint = (element) => {
        const size = element.dataset.halloweenWalker === 'ant' ? 20 : 32;
        return {
            x: 8 + Math.random() * Math.max(0, window.innerWidth - size - 16),
            y: 8 + Math.random() * Math.max(0, window.innerHeight - size - 16),
        };
    };

    // The browser interpolates transforms. JS only runs when a long path finishes.
    const walk = (element, start = randomPoint(element)) => {
        if (disposed || motion.matches || !visibleWalker(element) || !element.animate) return;
        const end = randomPoint(element);
        const angle = Math.atan2(end.y - start.y, end.x - start.x) * 180 / Math.PI + 90;
        const distance = Math.hypot(end.x - start.x, end.y - start.y);
        const speed = element.dataset.halloweenWalker === 'ant' ? 9 : 12;
        const duration = Math.max(12000, Math.min(110000, distance / speed * 1000));
        element.querySelector('.halloween-creature__body').style.transform = `rotate(${angle}deg)`;
        const from = `translate(${start.x}px, ${start.y}px)`;
        const to = `translate(${end.x}px, ${end.y}px)`;
        const animation = element.animate([
            { transform: from, offset: 0 },
            { transform: from, offset: .08 },
            { transform: to, offset: .94 },
            { transform: to, offset: 1 },
        ], { duration, easing: 'linear', fill: 'forwards' });
        animations.set(element, animation);
        if (paused()) animation.pause();
        animation.finished.then(() => {
            if (disposed || animations.get(element) !== animation) return;
            element.style.transform = to;
            animations.delete(element);
            animation.cancel();
            walk(element, end);
        }).catch(() => {});
    };

    const hideGlitch = () => {
        window.clearTimeout(hideTimer);
        if (glitch) glitch.hidden = true;
    };
    const scheduleGlitch = () => {
        window.clearTimeout(glitchTimer);
        if (paused() || !glitch || glitchCount >= maxGlitchesPerSession) return;
        // Avoid a surprise immediately on returning to a background tab.
        const delay = Math.max(20000, nextGlitchAt - Date.now());
        glitchTimer = window.setTimeout(() => {
            if (paused()) return;
            if (document.activeElement?.matches('input, textarea, select, [contenteditable="true"]')
                || [...document.querySelectorAll('dialog[open], [aria-modal="true"]')].some((dialog) => dialog.getClientRects().length)) {
                nextGlitchAt = Date.now() + 30000;
                scheduleGlitch();
                return;
            }
            glitchCount += 1;
            nextGlitchAt = Date.now() + 50000 + Math.random() * 25000;
            saveGlitch();
            glitch.hidden = false;
            hideTimer = window.setTimeout(hideGlitch, 1100);
            scheduleGlitch();
        }, delay);
    };
    const sync = () => {
        const stopped = paused();
        layer.classList.toggle('is-paused', stopped);
        if (motion.matches) {
            animations.forEach((animation) => animation.cancel());
            animations.clear();
        } else {
            walkers.forEach((element) => {
                if (!visibleWalker(element)) {
                    animations.get(element)?.cancel();
                    animations.delete(element);
                    return;
                }
                if (!animations.has(element)) walk(element);
                const animation = animations.get(element);
                if (animation) stopped ? animation.pause() : animation.play();
            });
        }
        hideGlitch();
        scheduleGlitch();
    };

    document.addEventListener('visibilitychange', sync);
    document.addEventListener('fullscreenchange', sync);
    motion.addEventListener?.('change', sync);
    compact.addEventListener?.('change', sync);
    window.addEventListener('pagehide', () => {
        disposed = true;
        animations.forEach((animation) => animation.cancel());
        animations.clear();
        window.clearTimeout(glitchTimer);
        hideGlitch();
    });
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            disposed = false;
            sync();
        }
    });
    saveGlitch();
    sync();
})();
