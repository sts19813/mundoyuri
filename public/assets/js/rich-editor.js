(() => {
    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value
            || '';
    }

    function youtubeEmbedUrl(url) {
        try {
            const parsed = new URL(url);
            const host = parsed.hostname.replace(/^www\./, '').toLowerCase();
            let id = '';
            if (host === 'youtu.be') {
                id = parsed.pathname.slice(1);
            } else if (host === 'youtube.com' || host === 'm.youtube.com') {
                if (parsed.pathname.startsWith('/embed/')) id = parsed.pathname.split('/')[2] || '';
                else if (parsed.pathname.startsWith('/shorts/')) id = parsed.pathname.split('/')[2] || '';
                else id = parsed.searchParams.get('v') || '';
            } else if (host === 'youtube-nocookie.com' && parsed.pathname.startsWith('/embed/')) {
                id = parsed.pathname.split('/')[2] || '';
            }
            return /^[A-Za-z0-9_-]{6,32}$/.test(id) ? `https://www.youtube-nocookie.com/embed/${id}` : null;
        } catch {
            return null;
        }
    }

    function syncSource(quill, source) {
        const html = quill.root.innerHTML.trim();
        source.value = html === '<p><br></p>' ? '' : html;
    }

    async function uploadImage(file, uploadUrl, status) {
        if (!file || !file.type.startsWith('image/')) return null;
        const data = new FormData();
        data.append('image', file);
        status.textContent = 'Optimizando imagen...';
        const response = await fetch(uploadUrl, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.url) {
            throw new Error(result.message || result.errors?.image?.[0] || 'No se pudo subir la imagen.');
        }
        status.textContent = 'Imagen insertada.';
        return result.url;
    }

    function insertEmbed(quill, type, value) {
        const range = quill.getSelection(true) || { index: quill.getLength(), length: 0 };
        quill.insertEmbed(range.index, type, value, 'user');
        quill.setSelection(range.index + 1, 0, 'silent');
    }

    function initEditor(wrapper) {
        if (wrapper.dataset.ready === 'true' || !window.Quill) return;
        const source = wrapper.querySelector('[data-rich-editor-source]');
        const field = wrapper.querySelector('[data-rich-editor-field]');
        const toolbar = wrapper.querySelector('[data-rich-editor-toolbar]');
        const status = wrapper.querySelector('[data-rich-editor-status]');
        const uploadUrl = wrapper.dataset.uploadUrl;
        if (!source || !field || !toolbar || !uploadUrl) return;

        const quill = new Quill(field, {
            theme: 'snow',
            placeholder: wrapper.dataset.placeholder || '',
            modules: {
                toolbar: {
                    container: toolbar,
                    handlers: {
                        image() {
                            const input = document.createElement('input');
                            input.type = 'file';
                            input.accept = 'image/jpeg,image/png,image/webp';
                            input.addEventListener('change', async () => {
                                try {
                                    const url = await uploadImage(input.files?.[0], uploadUrl, status);
                                    if (url) insertEmbed(quill, 'image', url);
                                } catch (error) {
                                    status.textContent = error.message;
                                } finally {
                                    syncSource(quill, source);
                                }
                            });
                            input.click();
                        },
                        video() {
                            const raw = window.prompt('Pega un enlace de YouTube');
                            if (!raw) return;
                            const embed = youtubeEmbedUrl(raw);
                            if (!embed) {
                                status.textContent = 'Usa un enlace válido de YouTube.';
                                return;
                            }
                            insertEmbed(quill, 'video', embed);
                            syncSource(quill, source);
                        },
                    },
                },
            },
        });

        quill.root.innerHTML = source.value || '';
        syncSource(quill, source);
        source.classList.add('visually-hidden');

        quill.on('text-change', () => syncSource(quill, source));

        quill.root.addEventListener('drop', async (event) => {
            const files = Array.from(event.dataTransfer?.files || []).filter(file => file.type.startsWith('image/'));
            if (files.length === 0) return;
            event.preventDefault();
            for (const file of files) {
                try {
                    const url = await uploadImage(file, uploadUrl, status);
                    if (url) insertEmbed(quill, 'image', url);
                } catch (error) {
                    status.textContent = error.message;
                }
            }
            syncSource(quill, source);
        });

        quill.root.addEventListener('paste', async (event) => {
            const files = Array.from(event.clipboardData?.files || []).filter(file => file.type.startsWith('image/'));
            if (files.length === 0) return;
            event.preventDefault();
            for (const file of files) {
                try {
                    const url = await uploadImage(file, uploadUrl, status);
                    if (url) insertEmbed(quill, 'image', url);
                } catch (error) {
                    status.textContent = error.message;
                }
            }
            syncSource(quill, source);
        });

        wrapper.closest('form')?.addEventListener('submit', () => syncSource(quill, source));
        wrapper.dataset.ready = 'true';
    }

    function initAll() {
        document.querySelectorAll('[data-rich-editor]').forEach(initEditor);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
    window.addEventListener('load', initAll);
})();
