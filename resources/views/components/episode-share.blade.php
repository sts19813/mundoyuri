@props([
    'url',
    'title',
    'text',
])

<div class="episode-share" data-episode-share>
    <span class="episode-share__label">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="18" cy="5" r="3" />
            <circle cx="6" cy="12" r="3" />
            <circle cx="18" cy="19" r="3" />
            <path d="m8.6 10.5 6.8-4M8.6 13.5l6.8 4" />
        </svg>
        Compartir episodio
    </span>

    <a
        href="https://wa.me/?text={{ urlencode($text.' '.$url) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="episode-share__button episode-share__button--whatsapp episode-share__button--platform"
        aria-label="Compartir episodio por WhatsApp"
    >
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M20 11.5a8 8 0 0 1-11.8 7L4 20l1.5-4.1A8 8 0 1 1 20 11.5Z" />
            <path d="M9 8.5c.4 2.3 2.2 4.1 4.5 4.5" />
        </svg>
        WhatsApp
    </a>
    <a
        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="episode-share__button episode-share__button--facebook episode-share__button--platform"
        aria-label="Compartir episodio en Facebook"
    >
        <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M13.6 22v-8.2h2.8l.4-3.2h-3.2V8.5c0-.9.3-1.6 1.7-1.6H17V4.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.4H7.5v3.2h2.8V22h3.3Z" />
        </svg>
        Facebook
    </a>
    <a
        href="https://x.com/intent/post?url={{ urlencode($url) }}&text={{ urlencode($text) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="episode-share__button episode-share__button--x episode-share__button--platform"
        aria-label="Compartir episodio en X"
    >
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
            <path d="m5 4 14 16M19 4 5 20" />
        </svg>
        X
    </a>
    <button
        type="button"
        class="episode-share__button episode-share__button--instagram episode-share__button--platform"
        data-instagram-share
        data-share-url="{{ $url }}"
        data-share-title="{{ $title }}"
        data-share-text="{{ $text }}"
        title="En móvil abre las opciones para compartir; en escritorio copia el enlace"
    >
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="5" />
            <circle cx="12" cy="12" r="4" />
            <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
        </svg>
        Instagram
    </button>
    <button
        type="button"
        class="episode-share__button episode-share__button--copy"
        data-copy-share
        data-share-url="{{ $url }}"
    >
        Copiar enlace
    </button>
    <span class="episode-share__feedback" data-share-feedback role="status" aria-live="polite"></span>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-episode-share]').forEach((sharePanel) => {
                const feedback = sharePanel.querySelector('[data-share-feedback]');
                const instagramButton = sharePanel.querySelector('[data-instagram-share]');
                const copyButton = sharePanel.querySelector('[data-copy-share]');

                const showFeedback = (message) => {
                    if (!feedback) return;

                    feedback.textContent = message;
                    window.setTimeout(() => {
                        feedback.textContent = '';
                    }, 3000);
                };

                const copyUrl = async (url, message = 'Enlace copiado') => {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(url);
                    } else {
                        const input = document.createElement('textarea');
                        input.value = url;
                        input.setAttribute('readonly', '');
                        input.style.position = 'fixed';
                        input.style.opacity = '0';
                        document.body.appendChild(input);
                        input.select();
                        document.execCommand('copy');
                        input.remove();
                    }

                    showFeedback(message);
                };

                instagramButton?.addEventListener('click', async () => {
                    const shareData = {
                        title: instagramButton.dataset.shareTitle,
                        text: instagramButton.dataset.shareText,
                        url: instagramButton.dataset.shareUrl,
                    };

                    try {
                        if (navigator.share) {
                            await navigator.share(shareData);
                        } else {
                            window.open('https://www.instagram.com/', '_blank', 'noopener,noreferrer');
                            await copyUrl(shareData.url, 'Enlace copiado. Pégalo en Instagram.');
                        }
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            showFeedback('No se pudo abrir Instagram. Intenta copiar el enlace.');
                        }
                    }
                });

                copyButton?.addEventListener('click', async () => {
                    try {
                        await copyUrl(copyButton.dataset.shareUrl);
                    } catch {
                        showFeedback('No se pudo copiar el enlace.');
                    }
                });
            });
        });
    </script>
@endonce
