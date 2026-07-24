@props([
    'url',
    'title',
    'text',
])

<div
    class="episode-share"
    data-episode-share
    data-share-url="{{ $url }}"
    data-share-title="{{ $title }}"
    data-share-text="{{ $text }}"
    aria-label="Compartir episodio"
>
    <span class="episode-share__sr-only">Compartir episodio</span>
    <ul class="episode-share__list" role="list">
        <li>
            <button type="button" class="episode-share__icon episode-share__icon--green" data-share-network="wa"
                aria-label="Compartir por WhatsApp" title="WhatsApp">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M20 11.5a8 8 0 0 1-11.8 7L4 20l1.5-4.1A8 8 0 1 1 20 11.5Z" />
                    <path d="M9 8.5c.4 2.3 2.2 4.1 4.5 4.5" />
                </svg>
            </button>
        </li>
        <li>
            <button type="button" class="episode-share__icon episode-share__icon--blue" data-share-network="fb"
                aria-label="Compartir en Facebook" title="Facebook">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M13.6 22v-8.2h2.8l.4-3.2h-3.2V8.5c0-.9.3-1.6 1.7-1.6H17V4.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.4H7.5v3.2h2.8V22h3.3Z" />
                </svg>
            </button>
        </li>
        <li>
            <button type="button" class="episode-share__icon episode-share__icon--black" data-share-network="x"
                aria-label="Compartir en X" title="X">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                    <path d="m5 4 14 16M19 4 5 20" />
                </svg>
            </button>
        </li>
        <li>
            <button type="button" class="episode-share__icon episode-share__icon--gradient" data-share-network="ig"
                aria-label="Compartir en Instagram" title="Instagram">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="5" />
                    <circle cx="12" cy="12" r="4" />
                    <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
                </svg>
            </button>
        </li>
        <li>
            <button type="button" class="episode-share__icon episode-share__icon--neutral" data-share-network="copy"
                aria-label="Copiar enlace del episodio" title="Copiar enlace">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="9" y="9" width="11" height="11" rx="2" />
                    <path d="M15 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h3" />
                </svg>
            </button>
        </li>
    </ul>
    <span class="episode-share__feedback" data-share-feedback role="status" aria-live="polite"></span>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-episode-share]').forEach((sharePanel) => {
                const feedback = sharePanel.querySelector('[data-share-feedback]');
                const networkButtons = sharePanel.querySelectorAll('[data-share-network]');
                const shareUrl = sharePanel.dataset.shareUrl;
                const shareTitle = sharePanel.dataset.shareTitle;
                const shareText = sharePanel.dataset.shareText;

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

                networkButtons.forEach((button) => {
                    button.addEventListener('click', async () => {
                        const network = button.dataset.shareNetwork;
                        const encodedUrl = encodeURIComponent(shareUrl);
                        const encodedText = encodeURIComponent(shareText);

                        if (network === 'copy') {
                            try {
                                await copyUrl(shareUrl);
                            } catch {
                                showFeedback('No se pudo copiar el enlace.');
                            }

                            return;
                        }

                        if (network === 'ig') {
                            try {
                                if (navigator.share) {
                                    await navigator.share({ title: shareTitle, text: shareText, url: shareUrl });
                                } else {
                                    window.open('https://www.instagram.com/', '_blank', 'noopener,noreferrer');
                                    await copyUrl(shareUrl, 'Enlace copiado. Pégalo en Instagram.');
                                }
                            } catch (error) {
                                if (error.name !== 'AbortError') {
                                    showFeedback('No se pudo abrir Instagram.');
                                }
                            }

                            return;
                        }

                        const targets = {
                            wa: `https://wa.me/?text=${encodeURIComponent(`${shareText} ${shareUrl}`)}`,
                            fb: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`,
                            x: `https://x.com/intent/post?url=${encodedUrl}&text=${encodedText}`,
                        };

                        window.open(targets[network], '_blank', 'noopener,noreferrer');
                    });
                });
            });
        });
    </script>
@endonce
