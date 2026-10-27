(() => {
    const layer = document.querySelector('[data-season-theme="halloween"]');
    if (!layer) return;

    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const compact = window.matchMedia('(max-width: 600px)');
    const walkers = [...layer.querySelectorAll('[data-halloween-walker]')];
    const glitch = document.querySelector('.halloween-glitch');
    const animations = new Map();
    const pumpkinRain = layer.querySelector('[data-halloween-pumpkin-rain]');
    const pumpkinSlots = [...(pumpkinRain?.querySelectorAll('[data-halloween-pumpkin]') ?? [])];
    const pumpkinAnimations = new Map();
    const pumpkinSources = [pumpkinRain?.dataset.pumpkinSrc, pumpkinRain?.dataset.pumpkinAltSrc].filter(Boolean);
    let pumpkinTimer;
    let pumpkinStarted = false;
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
        || !(element.matches('.halloween-creature--spider.halloween-creature--two, .halloween-creature--ant.halloween-creature--three, .halloween-creature--ant.halloween-creature--five, .halloween-creature--ant.halloween-creature--seven, .halloween-creature--ant.halloween-creature--eight'));
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
        }).catch(() => { });
    };

    const hideGlitch = () => {
        window.clearTimeout(hideTimer);
        if (glitch) glitch.hidden = true;
    };

    const pumpkinLimit = () => compact.matches ? 6 : 12;
    const stopPumpkin = (image) => {
        pumpkinAnimations.get(image)?.cancel();
        pumpkinAnimations.delete(image);
        image.hidden = true;
    };
    const spawnPumpkin = () => {
        if (paused() || !pumpkinSources.length) return;
        const image = pumpkinSlots.slice(0, pumpkinLimit()).find(slot => !pumpkinAnimations.has(slot));
        if (!image?.animate) return;
        const size = compact.matches ? 26 + Math.random() * 10 : 32 + Math.random() * 16;
        const x = 12 + Math.random() * Math.max(0, window.innerWidth - size - 24);
        const drift = (Math.random() - .5) * (compact.matches ? 100 : 220);
        const endX = Math.max(4, Math.min(window.innerWidth - size - 4, x + drift));
        const midX = Math.max(4, Math.min(window.innerWidth - size - 4, (x + endX) / 2 + (Math.random() - .5) * 50));
        const angle = (Math.random() - .5) * 56;
        const turn = (Math.random() - .5) * 180;
        const opacity = .64 + Math.random() * .2;
        const travel = window.innerHeight + 112;

        // Assign src only when the first fall starts; reduced motion never downloads these assets.
        image.src = pumpkinSources[Math.floor(Math.random() * pumpkinSources.length)];
        image.style.width = `${size}px`;
        image.hidden = false;
        const animation = image.animate([
            { transform: `translate3d(${x}px, -56px, 0) rotate(${angle}deg)`, opacity: 0, offset: 0 },
            { opacity, offset: .06 },
            { transform: `translate3d(${midX}px, ${travel / 2 - 56}px, 0) rotate(${angle + turn / 2}deg)`, opacity, offset: .5 },
            { opacity, offset: .92 },
            { transform: `translate3d(${endX}px, ${travel - 56}px, 0) rotate(${angle + turn}deg)`, opacity: 0, offset: 1 },
        ], { duration: 14000 + Math.random() * 10000, easing: 'linear', fill: 'forwards' });
        pumpkinAnimations.set(image, animation);
        animation.finished.then(() => {
            if (pumpkinAnimations.get(image) === animation) stopPumpkin(image);
        }).catch(() => { });
    };
    const schedulePumpkins = () => {
        window.clearTimeout(pumpkinTimer);
        if (paused() || !pumpkinSlots.length || !pumpkinSources.length) return;
        const delay = !pumpkinStarted ? 500 + Math.random() * 900
            : compact.matches ? 1100 + Math.random() * 1300 : 700 + Math.random() * 1100;
        pumpkinTimer = window.setTimeout(() => {
            if (paused()) return;
            pumpkinStarted = true;
            spawnPumpkin();
            // Occasional pairs break the rhythm, but never exceed the particle pool.
            if (!compact.matches && Math.random() < .24) spawnPumpkin();
            schedulePumpkins();
        }, delay);
    };
    const syncPumpkins = () => {
        window.clearTimeout(pumpkinTimer);
        pumpkinSlots.forEach((image, index) => {
            if (disposed || motion.matches || index >= pumpkinLimit()) stopPumpkin(image);
            else {
                const animation = pumpkinAnimations.get(image);
                if (animation) paused() ? animation.pause() : animation.play();
            }
        });
        schedulePumpkins();
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
        syncPumpkins();
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
        window.clearTimeout(pumpkinTimer);
        pumpkinSlots.forEach(stopPumpkin);
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
