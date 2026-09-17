(function () {
    const loadMedia = (media) => {
        if (!media || media.dataset.lazyLoaded === '1') {
            return;
        }

        if (media.tagName === 'IMG') {
            const source = media.dataset.src;

            if (source) {
                media.src = source;
                media.removeAttribute('data-src');
            }
        }

        if (media.tagName === 'VIDEO') {
            media.querySelectorAll('source[data-src]').forEach((source) => {
                source.src = source.dataset.src;
                source.removeAttribute('data-src');
            });
            media.load();
        }

        media.dataset.lazyLoaded = '1';
        media.classList.add('is-lazy-loaded');
    };

    const bootLazyMedia = () => {
        const media = Array.from(document.querySelectorAll('.js-lazy-media'));

        if (!('IntersectionObserver' in window)) {
            media.forEach(loadMedia);
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                loadMedia(entry.target);
                observer.unobserve(entry.target);
            });
        }, {
            rootMargin: '420px 0px',
            threshold: 0.01,
        });

        media.forEach((item) => observer.observe(item));
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootLazyMedia, { once: true });
    } else {
        bootLazyMedia();
    }
})();
