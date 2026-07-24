@props([
    'url',
    'title',
    'text',
])

<div class="episode-share" data-episode-share>
    <button
        type="button"
        class="episode-share__button episode-share__button--primary"
        data-native-share
        data-share-url="{{ $url }}"
        data-share-title="{{ $title }}"
        data-share-text="{{ $text }}"
    >
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="18" cy="5" r="3" />
            <circle cx="6" cy="12" r="3" />
            <circle cx="18" cy="19" r="3" />
            <path d="m8.6 10.5 6.8-4M8.6 13.5l6.8 4" />
        </svg>
        Compartir episodio
    </button>

    <a
        href="https://wa.me/?text={{ urlencode($text.' '.$url) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="episode-share__button episode-share__button--whatsapp"
        aria-label="Compartir episodio por WhatsApp"
    >
        WhatsApp
    </a>
    <a
        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="episode-share__button episode-share__button--channel"
        aria-label="Compartir episodio en Facebook"
    >
        Facebook
    </a>
    <button
        type="button"
        class="episode-share__button episode-share__button--channel"
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
                const nativeButton = sharePanel.querySelector('[data-native-share]');
                const copyButton = sharePanel.querySelector('[data-copy-share]');

                const showFeedback = (message) => {
                    if (!feedback) return;

                    feedback.textContent = message;
                    window.setTimeout(() => {
                        feedback.textContent = '';
                    }, 3000);
                };

                const copyUrl = async (url) => {
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

                    showFeedback('Enlace copiado');
                };

                nativeButton?.addEventListener('click', async () => {
                    const shareData = {
                        title: nativeButton.dataset.shareTitle,
                        text: nativeButton.dataset.shareText,
                        url: nativeButton.dataset.shareUrl,
                    };

                    try {
                        if (navigator.share) {
                            await navigator.share(shareData);
                        } else {
                            await copyUrl(shareData.url);
                        }
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            showFeedback('No se pudo compartir. Intenta copiar el enlace.');
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
