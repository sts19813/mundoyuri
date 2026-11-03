(function () {
    const selector = '[data-sticker-image]';
    const timers = new WeakMap();
    const timeoutMs = 20000;

    function clearTimer(container) {
        const timer = timers.get(container);
        if (timer) window.clearTimeout(timer);
        timers.delete(container);
    }

    function setState(container, state) {
        if (!container) return;
        clearTimer(container);
        container.classList.toggle('is-loading', state === 'loading');
        container.classList.toggle('is-error', state === 'error');
        const loading = container.querySelector('[data-sticker-loading]');
        const retry = container.querySelector('[data-sticker-retry]');
        const select = container.querySelector('[data-sticker-select]');
        if (loading) loading.hidden = state !== 'loading';
        if (retry) retry.hidden = state !== 'error';
        if (select) select.disabled = state !== 'loaded';
        if (state !== 'loading') observer?.unobserve(container);
    }

    function startTimer(container) {
        if (!container.classList.contains('is-loading') || timers.has(container)) return;
        timers.set(container, window.setTimeout(() => setState(container, 'error'), timeoutMs));
    }

    const observer = 'IntersectionObserver' in window ? new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                startTimer(entry.target);
            }
        });
    }, { rootMargin: '200px' }) : null;

    function initialize(container) {
        if (container.dataset.stickerImageReady === 'true') return;
        const image = container.querySelector('img');
        if (!image) return;
        container.dataset.stickerImageReady = 'true';
        if (image.complete) {
            setState(container, image.naturalWidth > 0 ? 'loaded' : 'error');
        } else {
            setState(container, 'loading');
            if (observer) observer.observe(container);
            else startTimer(container);
        }
    }

    function visit(node, action) {
        if (!(node instanceof Element)) return;
        if (node.matches(selector)) action(node);
        node.querySelectorAll(selector).forEach(action);
    }

    document.addEventListener('load', (event) => {
        if (event.target instanceof HTMLImageElement) setState(event.target.closest(selector), 'loaded');
    }, true);
    document.addEventListener('error', (event) => {
        if (event.target instanceof HTMLImageElement) setState(event.target.closest(selector), 'error');
    }, true);
    document.addEventListener('click', (event) => {
        const retry = event.target.closest('[data-sticker-retry]');
        if (!retry) return;
        const container = retry.closest(selector);
        const image = container?.querySelector('img');
        if (!image) return;
        setState(container, 'loading');
        startTimer(container);
        const url = new URL(image.currentSrc || image.src, window.location.href);
        url.searchParams.set('_sticker_retry', String(Date.now()));
        image.src = url.toString();
    });

    document.querySelectorAll(selector).forEach(initialize);
    new MutationObserver((mutations) => mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => visit(node, initialize));
        mutation.removedNodes.forEach((node) => visit(node, (container) => {
            clearTimer(container);
            observer?.unobserve(container);
            delete container.dataset.stickerImageReady;
        }));
    })).observe(document.body, { childList: true, subtree: true });
})();
