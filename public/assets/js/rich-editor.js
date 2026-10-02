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

    const IMAGE_ALIGN_CLASSES = ['rich-image-align-left', 'rich-image-align-center', 'rich-image-align-right'];

    function cleanEditorHtml(root) {
        const clone = root.cloneNode(true);
        clone.querySelectorAll('.rich-editor-image-selected').forEach((image) => {
            image.classList.remove('rich-editor-image-selected');
            if (image.getAttribute('class') === '') image.removeAttribute('class');
        });

        return clone.innerHTML.trim();
    }

    function syncSource(quill, source) {
        const html = cleanEditorHtml(quill.root);
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

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
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
                                    if (url) {
                                        insertEmbed(quill, 'image', url);
                                        applyInsertedImageDefaults(url);
                                    }
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

        const imageTools = document.createElement('div');
        imageTools.className = 'rich-editor-image-tools';
        imageTools.hidden = true;
        imageTools.innerHTML = `
            <button type="button" data-rich-image-size="35" title="Imagen pequeña">35%</button>
            <button type="button" data-rich-image-size="55" title="Imagen mediana">55%</button>
            <button type="button" data-rich-image-size="75" title="Imagen grande">75%</button>
            <button type="button" data-rich-image-size="100" title="Ancho completo">100%</button>
            <span class="rich-editor-image-tools-divider" aria-hidden="true"></span>
            <button type="button" data-rich-image-align="left" title="Alinear a la izquierda">Izq.</button>
            <button type="button" data-rich-image-align="center" title="Centrar">Centro</button>
            <button type="button" data-rich-image-align="right" title="Alinear a la derecha">Der.</button>
            <span class="rich-editor-image-tools-divider" aria-hidden="true"></span>
            <button type="button" data-rich-image-move="up" title="Subir imagen">Arriba</button>
            <button type="button" data-rich-image-move="down" title="Bajar imagen">Abajo</button>
            <span class="rich-editor-image-resize" data-rich-image-resize title="Arrastra para cambiar el tamaño"></span>
        `;
        wrapper.appendChild(imageTools);

        let selectedImage = null;
        let resizeState = null;

        function editorImages() {
            return Array.from(quill.root.querySelectorAll('img'));
        }

        function imageBlock(image) {
            const block = image.closest('p, div, blockquote, li');

            if (!block || block === quill.root || !quill.root.contains(block)) {
                return image;
            }

            const text = block.textContent.trim();
            const images = block.querySelectorAll('img');

            return text === '' && images.length === 1 ? block : image;
        }

        function placeImageTools() {
            if (!selectedImage || !quill.root.contains(selectedImage)) {
                imageTools.hidden = true;

                return;
            }

            const imageRect = selectedImage.getBoundingClientRect();
            const wrapperRect = wrapper.getBoundingClientRect();
            const top = imageRect.top - wrapperRect.top - imageTools.offsetHeight - 8;
            const left = imageRect.left - wrapperRect.left;

            imageTools.hidden = false;
            imageTools.style.top = `${Math.max(8, top)}px`;
            imageTools.style.left = `${clamp(left, 8, Math.max(8, wrapperRect.width - imageTools.offsetWidth - 8))}px`;
        }

        function selectImage(image) {
            if (selectedImage && selectedImage !== image) {
                selectedImage.classList.remove('rich-editor-image-selected');
            }

            selectedImage = image;
            selectedImage.classList.add('rich-editor-image-selected');
            selectedImage.draggable = true;
            placeImageTools();
        }

        function hideImageTools() {
            if (selectedImage) selectedImage.classList.remove('rich-editor-image-selected');
            selectedImage = null;
            imageTools.hidden = true;
        }

        function setImageWidth(image, percent) {
            const width = clamp(Number(percent) || 75, 10, 100);
            image.style.width = `${Math.round(width)}%`;
            image.style.height = 'auto';
            image.removeAttribute('width');
            image.removeAttribute('height');
            quill.update('user');
            syncSource(quill, source);
            placeImageTools();
            status.textContent = `Imagen al ${Math.round(width)}%.`;
        }

        function alignImage(image, align) {
            image.classList.remove(...IMAGE_ALIGN_CLASSES);
            image.classList.add(`rich-image-align-${align}`);
            quill.update('user');
            syncSource(quill, source);
            placeImageTools();
        }

        function moveImage(image, direction) {
            const block = imageBlock(image);
            const sibling = direction === 'up' ? block.previousElementSibling : block.nextElementSibling;

            if (!sibling) return;

            if (direction === 'up') {
                sibling.before(block);
            } else {
                sibling.after(block);
            }

            quill.update('user');
            syncSource(quill, source);
            selectImage(image);
            status.textContent = direction === 'up' ? 'Imagen movida hacia arriba.' : 'Imagen movida hacia abajo.';
        }

        function applyInsertedImageDefaults(url) {
            requestAnimationFrame(() => {
                const images = editorImages().filter((image) => image.currentSrc === url || image.src === url || image.getAttribute('src') === url);
                const image = images.at(-1);
                if (!image) return;

                image.classList.add('rich-image-align-left');
                setImageWidth(image, 75);
                selectImage(image);
            });
        }

        quill.root.innerHTML = source.value || '';
        syncSource(quill, source);
        source.classList.add('visually-hidden');

        editorImages().forEach((image) => {
            image.draggable = true;
        });

        quill.on('text-change', () => {
            syncSource(quill, source);
            requestAnimationFrame(placeImageTools);
        });

        quill.root.addEventListener('click', (event) => {
            const image = event.target.closest?.('img');

            if (image && quill.root.contains(image)) {
                event.preventDefault();
                selectImage(image);

                return;
            }

            if (!imageTools.contains(event.target)) {
                hideImageTools();
            }
        });

        imageTools.addEventListener('mousedown', (event) => {
            event.preventDefault();
        });

        imageTools.addEventListener('click', (event) => {
            const button = event.target.closest('button');
            if (!button || !selectedImage) return;

            const size = button.dataset.richImageSize;
            const align = button.dataset.richImageAlign;
            const move = button.dataset.richImageMove;

            if (size) setImageWidth(selectedImage, size);
            if (align) alignImage(selectedImage, align);
            if (move) moveImage(selectedImage, move);
        });

        imageTools.querySelector('[data-rich-image-resize]')?.addEventListener('pointerdown', (event) => {
            if (!selectedImage) return;

            event.preventDefault();
            resizeState = {
                image: selectedImage,
                startX: event.clientX,
                startWidth: selectedImage.getBoundingClientRect().width,
                editorWidth: quill.root.getBoundingClientRect().width,
            };
            imageTools.classList.add('is-resizing');
            event.target.setPointerCapture?.(event.pointerId);
        });

        window.addEventListener('pointermove', (event) => {
            if (!resizeState) return;

            const nextWidth = resizeState.startWidth + (event.clientX - resizeState.startX);
            const percent = (nextWidth / resizeState.editorWidth) * 100;
            setImageWidth(resizeState.image, percent);
        });

        window.addEventListener('pointerup', () => {
            if (!resizeState) return;

            resizeState = null;
            imageTools.classList.remove('is-resizing');
        });

        document.addEventListener('click', (event) => {
            if (!wrapper.contains(event.target)) hideImageTools();
        });

        window.addEventListener('resize', placeImageTools);
        quill.root.addEventListener('scroll', placeImageTools);

        quill.root.addEventListener('drop', async (event) => {
            const files = Array.from(event.dataTransfer?.files || []).filter(file => file.type.startsWith('image/'));
            if (files.length === 0) return;
            event.preventDefault();
            for (const file of files) {
                try {
                    const url = await uploadImage(file, uploadUrl, status);
                    if (url) {
                        insertEmbed(quill, 'image', url);
                        applyInsertedImageDefaults(url);
                    }
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
                    if (url) {
                        insertEmbed(quill, 'image', url);
                        applyInsertedImageDefaults(url);
                    }
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
