@extends('layouts.portal')

@section('head')

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Conversación con {{ $otherUser->alias ?: $otherUser->name }} · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">


@endsection

@section('body_attributes')
class="messenger-body"


@endsection

@section('body')

<x-navbar />

    <main class="messenger-page">
        <div class="messenger-ambient messenger-ambient-one"></div>
        <div class="messenger-ambient messenger-ambient-two"></div>
        <div class="container-xl messenger-container">
            @if(session('success'))
                <div class="portal-alert portal-alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="portal-alert portal-alert-error" role="alert">{{ session('error') }}</div>
            @endif

            @include('messages._device-notifications')

            <section class="messenger-shell has-active-chat">
                @include('messages._sidebar')

                <section class="messenger-chat" aria-label="Conversación con {{ $otherUser->alias ?: $otherUser->name }}">
                    <header class="messenger-chat-header">
                        <a href="{{ route('messages.index') }}" class="messenger-mobile-back" aria-label="Volver a conversaciones">
                            <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        </a>
                        <a class="messenger-chat-person" href="{{ $otherUser->publicProfileUrl() }}">
                            <span @class(['messenger-avatar-wrap', 'is-online' => $otherUser->isOnlineForMessages()])>
                                @if($otherUser->hasProfileAvatar())
                                    <img class="messenger-avatar" src="{{ $otherUser->avatarUrl() }}" alt="">
                                @else
                                    <span class="messenger-avatar messenger-avatar-fallback" aria-hidden="true">{{ $otherUser->initials() }}</span>
                                @endif
                            </span>
                            <span>
                                <strong>{{ $otherUser->alias ?: $otherUser->name }}</strong>
                                <small>{{ $otherUser->isOnlineForMessages() ? 'Conectada ahora' : ($otherUser->is_active ? 'Ver perfil' : 'Cuenta no disponible') }}</small>
                            </span>
                        </a>

                        <details class="messenger-chat-actions">
                            <summary aria-label="Opciones de la conversación">•••</summary>
                            <div>
                                <a href="{{ $otherUser->publicProfileUrl() }}">Ver perfil</a>
                                <button type="button" data-media-panel-open>Multimedia, archivos y enlaces</button>
                                @if($viewerHasBlocked)
                                    <form method="POST" action="{{ route('users.block.destroy', $otherUser) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit">Desbloquear</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('users.block.store', $otherUser) }}" onsubmit="return confirm('¿Quieres bloquear a esta persona? Ya no podrán seguirse ni enviarse mensajes.')">
                                        @csrf
                                        <button type="submit">Bloquear</button>
                                    </form>
                                @endif
                            </div>
                        </details>
                    </header>

                    <div class="messenger-message-pane" id="conversationMessages">
                        @if($messages->nextPageUrl())
                            <div class="messenger-older-link" data-older-messages>
                                <button type="button" data-load-older-messages data-older-url="{{ route('messages.older', $otherUser) }}">Cargar mensajes anteriores</button>
                                <noscript><a href="{{ $messages->nextPageUrl() }}">Ver mensajes anteriores</a></noscript>
                            </div>
                        @endif

                        @forelse($messages as $message)
                            @include('messages._message', ['message' => $message, 'viewer' => $viewer, 'otherUser' => $otherUser])
                        @empty
                            <div class="messenger-chat-empty">
                                <span @class(['messenger-avatar-wrap', 'is-online' => $otherUser->isOnlineForMessages()])>
                                    @if($otherUser->hasProfileAvatar())
                                        <img class="messenger-avatar" src="{{ $otherUser->avatarUrl() }}" alt="">
                                    @else
                                        <span class="messenger-avatar messenger-avatar-fallback" aria-hidden="true">{{ $otherUser->initials() }}</span>
                                    @endif
                                </span>
                                <h2>{{ $otherUser->alias ?: $otherUser->name }}</h2>
                                <p>Este es el comienzo de su conversación privada.</p>
                            </div>
                        @endforelse
                    </div>

                    <footer class="messenger-composer-area">
                        @if($interactionBlocked)
                            <div class="messenger-disabled">{{ $viewerHasBlocked ? 'Desbloquea a esta persona para volver a enviar mensajes.' : 'Esta conversación no admite nuevos mensajes.' }}</div>
                        @elseif(!$otherUser->is_active)
                            <div class="messenger-disabled">Esta cuenta ya no está disponible.</div>
                        @else
                            <div class="messenger-reply-composer is-hidden" data-reply-composer-preview>
                                <div>
                                    <img data-reply-image alt="" hidden>
                                    <strong data-reply-author></strong>
                                    <span data-reply-preview></span>
                                </div>
                                <button type="button" data-clear-reply aria-label="Cancelar respuesta">×</button>
                            </div>
                            <form method="POST" action="{{ route('messages.store', $otherUser) }}" class="messenger-composer" enctype="multipart/form-data" data-message-composer>
                                @csrf
                                <input type="hidden" name="reply_to_message_id" value="" data-reply-input>
                                <input type="hidden" name="is_sticker" value="0" data-sticker-input>
                                <input type="hidden" name="sticker_id" value="" data-sticker-id-input>
                                <input id="message-attachment" type="file" name="attachment" accept="image/jpeg,image/png,image/webp,image/gif,.pdf,.txt,.csv,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.odp">
                                <label for="message-attachment" class="messenger-attach-button" aria-label="Adjuntar imagen o documento" title="Adjuntar imagen o documento">
                                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m21.4 11.6-8.9 8.9a6 6 0 0 1-8.5-8.5l9.5-9.5a4 4 0 0 1 5.7 5.7l-9.6 9.6a2 2 0 0 1-2.8-2.8l8.9-8.9"/></svg>
                                </label>
                                <button class="messenger-sticker-button" type="button" data-sticker-toggle aria-label="Abrir stickers" title="Stickers">
                                    <span class="brand-heart messenger-sticker-heart" aria-hidden="true"></span>
                                </button>
                                <div class="messenger-compose-input">
                                    <label class="visually-hidden" for="message-body">Escribe un mensaje</label>
                                    <textarea id="message-body" name="body" rows="1" maxlength="2000" placeholder="Aa">{{ old('body') }}</textarea>
                                    <small data-message-attachment-hint>Imagen o documento · máximo 20 MB</small>
                                </div>
                                <button class="messenger-send-button" type="submit" aria-label="Enviar mensaje">
                                    <svg width="21" height="21" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.8 2.5a1 1 0 0 0-1.05-.18L2.9 10.15a1 1 0 0 0 .1 1.87l7.12 2.37 2.37 7.12a1 1 0 0 0 .9.68h.05a1 1 0 0 0 .91-.59l7.63-17.9a1 1 0 0 0-.18-1.2ZM12 13.06 6.12 11.1l11.9-5.22Z"/></svg>
                                </button>
                            </form>
                            <section class="messenger-sticker-panel" data-sticker-panel hidden aria-label="Stickers">
                                <header><strong>Stickers</strong><button type="button" data-sticker-close aria-label="Cerrar stickers">×</button></header>
                                <div class="messenger-media-tabs" role="tablist" aria-label="Tipo de stickers">
                                    <button type="button" class="is-active" data-sticker-tab="general">Generales</button>
                                    <button type="button" data-sticker-tab="mine">Mis stickers</button>
                                </div>
                                <div class="messenger-sticker-grid" data-sticker-grid aria-live="polite"></div>
                                <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" data-sticker-create-file hidden>
                                <div class="messenger-sticker-create" data-sticker-create-controls hidden>
                                    <button type="button" class="messenger-sticker-create-button" data-sticker-create>Crear</button>
                                </div>
                            </section>
                            @error('body')<p class="messenger-form-error">{{ $message }}</p>@enderror
                            @error('attachment')<p class="messenger-form-error">{{ $message }}</p>@enderror
                        @endif
                    </footer>
                </section>
            </section>
        </div>
    </main>

    <div class="messenger-image-viewer" data-image-viewer hidden role="dialog" aria-modal="true" aria-label="Visor de imagen">
        <div class="messenger-image-viewer-backdrop" data-image-viewer-close></div>
        <header class="messenger-image-viewer-header">
            <strong data-image-viewer-title>Imagen</strong>
            <button type="button" data-image-viewer-close aria-label="Cerrar visor">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </header>
        <div class="messenger-image-viewer-stage" data-image-viewer-stage>
            <button class="messenger-image-viewer-nav is-previous" type="button" data-image-viewer-previous aria-label="Imagen anterior">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <img data-image-viewer-image alt="" draggable="false">
            <button class="messenger-image-viewer-nav is-next" type="button" data-image-viewer-next aria-label="Imagen siguiente">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
        <footer class="messenger-image-viewer-controls">
            <button type="button" data-image-viewer-zoom-out aria-label="Alejar">−</button>
            <button type="button" data-image-viewer-reset aria-label="Restablecer zoom"><span data-image-viewer-zoom>100%</span></button>
            <button type="button" data-image-viewer-zoom-in aria-label="Acercar">+</button>
            <small>Pellizca, arrastra o toca dos veces para ampliar</small>
        </footer>
    </div>

    <section class="messenger-media-panel" data-media-panel hidden role="dialog" aria-modal="true" aria-label="Multimedia, archivos y enlaces" data-media-url="{{ route('messages.media', $otherUser) }}">
        <div class="messenger-media-panel-backdrop" data-media-panel-close></div>
        <div class="messenger-media-panel-content">
            <header>
                <div><span>Conversación con {{ $otherUser->alias ?: $otherUser->name }}</span><h2>Multimedia</h2></div>
                <button type="button" data-media-panel-close aria-label="Cerrar multimedia">×</button>
            </header>
            <div class="messenger-media-tabs" role="tablist" aria-label="Tipo de contenido">
                <button type="button" role="tab" aria-selected="true" data-media-tab="images">Fotos</button>
                <button type="button" role="tab" aria-selected="false" data-media-tab="documents">Archivos</button>
                <button type="button" role="tab" aria-selected="false" data-media-tab="links">Enlaces</button>
            </div>
            <div class="messenger-media-results" data-media-results aria-live="polite"></div>
            <button class="messenger-media-more" type="button" data-media-more hidden>Mostrar más</button>
        </div>
    </section>

    <script>
        const syncMessengerViewport = () => {
            const viewport = window.visualViewport;
            const messagePane = document.getElementById('conversationMessages');
            const wasPinnedToLatest = messagePane
                ? messagePane.scrollHeight - messagePane.scrollTop - messagePane.clientHeight < 120
                : false;
            const height = Math.max(
                320,
                Math.floor(viewport?.height || window.innerHeight || document.documentElement.clientHeight)
            );

            document.body.style.setProperty('--messenger-viewport-height', `${height}px`);

            if (wasPinnedToLatest) {
                requestAnimationFrame(() => {
                    messagePane.scrollTop = messagePane.scrollHeight;
                });
            }
        };

        syncMessengerViewport();
        window.visualViewport?.addEventListener('resize', syncMessengerViewport);
        window.visualViewport?.addEventListener('scroll', syncMessengerViewport);
        window.addEventListener('resize', syncMessengerViewport);
        window.addEventListener('orientationchange', () => window.setTimeout(syncMessengerViewport, 250));

        const conversation = document.getElementById('conversationMessages');
        const imageViewer = document.querySelector('[data-image-viewer]');
        const imageViewerStage = imageViewer?.querySelector('[data-image-viewer-stage]');
        const imageViewerImage = imageViewer?.querySelector('[data-image-viewer-image]');
        const imageViewerTitle = imageViewer?.querySelector('[data-image-viewer-title]');
        const imageViewerZoom = imageViewer?.querySelector('[data-image-viewer-zoom]');
        const imageViewerPrevious = imageViewer?.querySelector('[data-image-viewer-previous]');
        const imageViewerNext = imageViewer?.querySelector('[data-image-viewer-next]');
        const imageViewerPointers = new Map();
        let imageViewerIndex = 0;
        let imageViewerScale = 1;
        let imageViewerTranslateX = 0;
        let imageViewerTranslateY = 0;
        let imageViewerDragStart = null;
        let imageViewerPinchStart = null;
        let imageViewerTouchStart = null;
        let imageViewerTouchPinched = false;
        let imageViewerLastTap = 0;
        let imageViewerReturnFocus = null;

        const imageViewerItems = () => Array.from(conversation?.querySelectorAll('[data-image-lightbox]') || []);
        const clampImageViewerScale = (scale) => Math.min(5, Math.max(1, scale));

        const clampImageViewerPosition = () => {
            if (!imageViewerStage || !imageViewerImage) return;
            const maxX = Math.max(0, (imageViewerImage.clientWidth * imageViewerScale - imageViewerStage.clientWidth) / 2);
            const maxY = Math.max(0, (imageViewerImage.clientHeight * imageViewerScale - imageViewerStage.clientHeight) / 2);
            imageViewerTranslateX = Math.min(maxX, Math.max(-maxX, imageViewerTranslateX));
            imageViewerTranslateY = Math.min(maxY, Math.max(-maxY, imageViewerTranslateY));
        };

        const renderImageViewerTransform = () => {
            if (!imageViewerImage) return;
            clampImageViewerPosition();
            imageViewerImage.style.transform = `translate3d(${imageViewerTranslateX}px, ${imageViewerTranslateY}px, 0) scale(${imageViewerScale})`;
            if (imageViewerZoom) imageViewerZoom.textContent = `${Math.round(imageViewerScale * 100)}%`;
            imageViewerStage?.classList.toggle('is-zoomed', imageViewerScale > 1);
        };

        const resetImageViewerTransform = () => {
            imageViewerScale = 1;
            imageViewerTranslateX = 0;
            imageViewerTranslateY = 0;
            renderImageViewerTransform();
        };

        const setImageViewerScale = (scale) => {
            imageViewerScale = clampImageViewerScale(scale);
            if (imageViewerScale === 1) {
                imageViewerTranslateX = 0;
                imageViewerTranslateY = 0;
            }
            renderImageViewerTransform();
        };

        const showImageViewerItem = (index) => {
            const items = imageViewerItems();
            if (!imageViewerImage || items.length === 0) return;
            imageViewerIndex = (index + items.length) % items.length;
            const item = items[imageViewerIndex];
            const alt = item.dataset.imageAlt || item.querySelector('img')?.alt || 'Imagen adjunta';
            resetImageViewerTransform();
            imageViewerImage.src = item.href;
            imageViewerImage.alt = alt;
            if (imageViewerTitle) imageViewerTitle.textContent = alt;
            const hasMultipleImages = items.length > 1;
            if (imageViewerPrevious) imageViewerPrevious.hidden = !hasMultipleImages;
            if (imageViewerNext) imageViewerNext.hidden = !hasMultipleImages;
        };

        const openImageViewer = (item) => {
            if (!imageViewer) return;
            const items = imageViewerItems();
            const index = items.indexOf(item);
            if (index < 0) return;
            imageViewerReturnFocus = item;
            imageViewer.hidden = false;
            document.body.classList.add('messenger-image-viewer-open');
            showImageViewerItem(index);
            imageViewer.querySelector('[data-image-viewer-close]:not(.messenger-image-viewer-backdrop)')?.focus({ preventScroll: true });
        };

        const closeImageViewer = () => {
            if (!imageViewer || imageViewer.hidden) return;
            imageViewer.hidden = true;
            imageViewerImage?.removeAttribute('src');
            imageViewerPointers.clear();
            imageViewerDragStart = null;
            imageViewerPinchStart = null;
            imageViewerTouchStart = null;
            imageViewerTouchPinched = false;
            document.body.classList.remove('messenger-image-viewer-open');
            imageViewerReturnFocus?.focus({ preventScroll: true });
        };

        imageViewer?.querySelectorAll('[data-image-viewer-close]').forEach((button) => {
            button.addEventListener('click', closeImageViewer);
        });
        imageViewerPrevious?.addEventListener('click', () => showImageViewerItem(imageViewerIndex - 1));
        imageViewerNext?.addEventListener('click', () => showImageViewerItem(imageViewerIndex + 1));
        imageViewer?.querySelector('[data-image-viewer-zoom-in]')?.addEventListener('click', () => setImageViewerScale(imageViewerScale + .5));
        imageViewer?.querySelector('[data-image-viewer-zoom-out]')?.addEventListener('click', () => setImageViewerScale(imageViewerScale - .5));
        imageViewer?.querySelector('[data-image-viewer-reset]')?.addEventListener('click', resetImageViewerTransform);

        imageViewerStage?.addEventListener('wheel', (event) => {
            event.preventDefault();
            setImageViewerScale(imageViewerScale + (event.deltaY < 0 ? .35 : -.35));
        }, { passive: false });

        imageViewerStage?.addEventListener('pointerdown', (event) => {
            if (event.target.closest('button')) return;
            imageViewerStage.setPointerCapture?.(event.pointerId);
            imageViewerPointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

            if (imageViewerPointers.size === 1) {
                if (event.pointerType === 'touch') {
                    imageViewerTouchStart = { x: event.clientX, y: event.clientY, moved: false };
                    imageViewerTouchPinched = false;
                }
                imageViewerDragStart = {
                    x: event.clientX,
                    y: event.clientY,
                    translateX: imageViewerTranslateX,
                    translateY: imageViewerTranslateY,
                };
            } else if (imageViewerPointers.size === 2) {
                imageViewerTouchPinched = true;
                const [first, second] = Array.from(imageViewerPointers.values());
                imageViewerPinchStart = {
                    distance: Math.hypot(second.x - first.x, second.y - first.y),
                    scale: imageViewerScale,
                    centerX: (first.x + second.x) / 2,
                    centerY: (first.y + second.y) / 2,
                    translateX: imageViewerTranslateX,
                    translateY: imageViewerTranslateY,
                };
            }
        });

        imageViewerStage?.addEventListener('pointermove', (event) => {
            if (!imageViewerPointers.has(event.pointerId)) return;
            imageViewerPointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
            if (event.pointerType === 'touch' && imageViewerTouchStart
                && Math.hypot(event.clientX - imageViewerTouchStart.x, event.clientY - imageViewerTouchStart.y) > 10) {
                imageViewerTouchStart.moved = true;
            }

            if (imageViewerPointers.size === 2 && imageViewerPinchStart) {
                const [first, second] = Array.from(imageViewerPointers.values());
                const distance = Math.hypot(second.x - first.x, second.y - first.y);
                const centerX = (first.x + second.x) / 2;
                const centerY = (first.y + second.y) / 2;
                imageViewerScale = clampImageViewerScale(imageViewerPinchStart.scale * (distance / Math.max(1, imageViewerPinchStart.distance)));
                imageViewerTranslateX = imageViewerPinchStart.translateX + centerX - imageViewerPinchStart.centerX;
                imageViewerTranslateY = imageViewerPinchStart.translateY + centerY - imageViewerPinchStart.centerY;
                renderImageViewerTransform();
                return;
            }

            if (imageViewerPointers.size === 1 && imageViewerDragStart && imageViewerScale > 1) {
                imageViewerTranslateX = imageViewerDragStart.translateX + event.clientX - imageViewerDragStart.x;
                imageViewerTranslateY = imageViewerDragStart.translateY + event.clientY - imageViewerDragStart.y;
                renderImageViewerTransform();
            }
        });

        const releaseImageViewerPointer = (event) => {
            const isTouchTap = event.pointerType === 'touch'
                && imageViewerPointers.size === 1
                && imageViewerTouchStart
                && !imageViewerTouchStart.moved
                && !imageViewerTouchPinched;
            imageViewerPointers.delete(event.pointerId);
            imageViewerDragStart = null;
            imageViewerPinchStart = null;

            if (isTouchTap) {
                const now = Date.now();
                if (now - imageViewerLastTap < 300) setImageViewerScale(imageViewerScale > 1 ? 1 : 2.5);
                imageViewerLastTap = now;
            }

            if (imageViewerPointers.size === 1) {
                const remaining = Array.from(imageViewerPointers.values())[0];
                imageViewerDragStart = {
                    x: remaining.x,
                    y: remaining.y,
                    translateX: imageViewerTranslateX,
                    translateY: imageViewerTranslateY,
                };
            } else if (imageViewerPointers.size === 0) {
                imageViewerTouchStart = null;
                imageViewerTouchPinched = false;
            }
        };

        imageViewerStage?.addEventListener('pointerup', releaseImageViewerPointer);
        imageViewerStage?.addEventListener('pointercancel', releaseImageViewerPointer);
        imageViewerStage?.addEventListener('dblclick', () => {
            if (Date.now() - imageViewerLastTap < 500) return;
            setImageViewerScale(imageViewerScale > 1 ? 1 : 2.5);
        });
        window.addEventListener('resize', renderImageViewerTransform);

        let latestMessageId = conversation
            ? Math.max(0, ...Array.from(conversation.querySelectorAll('[data-message-id]')).map((message) => Number(message.dataset.messageId) || 0))
            : 0;
        let oldestMessageId = conversation
            ? Math.min(...Array.from(conversation.querySelectorAll('[data-message-id]')).map((message) => Number(message.dataset.messageId) || Number.MAX_SAFE_INTEGER))
            : 0;

        const shouldStayPinnedToLatest = () => {
            if (!conversation) return false;
            return conversation.scrollHeight - conversation.scrollTop - conversation.clientHeight < 120;
        };

        const appendMessages = (messages, { playSound = false, forceScroll = false } = {}) => {
            if (!conversation || !Array.isArray(messages) || messages.length === 0) return;

            const wasPinned = shouldStayPinnedToLatest();
            conversation.querySelector('.messenger-chat-empty')?.remove();

            messages.forEach((message) => {
                if (!message?.id || conversation.querySelector(`[data-message-id="${message.id}"]`)) return;
                conversation.insertAdjacentHTML('beforeend', message.html);
                latestMessageId = Math.max(latestMessageId, Number(message.id) || 0);
            });

            if (forceScroll || wasPinned) {
                const scrollToLatest = () => {
                    conversation.scrollTop = conversation.scrollHeight;
                };

                requestAnimationFrame(() => {
                    scrollToLatest();
                    conversation.querySelectorAll('[data-message-id] img').forEach((image) => {
                        if (!image.complete) image.addEventListener('load', scrollToLatest, { once: true });
                    });
                });
            }

            if (playSound && messages.some((message) => message.incoming)) {
                playIncomingMessageSound();
            }
        };

        const markOutgoingAsRead = (messageIds) => {
            if (!conversation || !Array.isArray(messageIds)) return;

            messageIds.forEach((messageId) => {
                const status = conversation.querySelector(`[data-message-id="${messageId}"] [data-message-read-status]`);
                if (!status) return;
                status.textContent = '✓✓';
                status.setAttribute('aria-label', 'Leído');
            });
        };

        const olderMessages = conversation?.querySelector('[data-older-messages]');
        const olderMessagesButton = olderMessages?.querySelector('[data-load-older-messages]');
        let loadingOlderMessages = false;

        const prependMessages = (messages) => {
            if (!conversation || !Array.isArray(messages) || messages.length === 0) return;

            const previousHeight = conversation.scrollHeight;
            const previousTop = conversation.scrollTop;
            const fragment = document.createDocumentFragment();

            messages.forEach((message) => {
                if (!message?.id || conversation.querySelector(`[data-message-id="${message.id}"]`)) return;
                const template = document.createElement('template');
                template.innerHTML = message.html || '';
                const item = template.content.firstElementChild;
                if (!item) return;
                fragment.append(item);
                oldestMessageId = Math.min(oldestMessageId || Number.MAX_SAFE_INTEGER, Number(message.id) || Number.MAX_SAFE_INTEGER);
            });

            if (fragment.childNodes.length) {
                if (olderMessages) olderMessages.after(fragment);
                else conversation.prepend(fragment);
                conversation.scrollTop = previousTop + conversation.scrollHeight - previousHeight;
            }
        };

        olderMessagesButton?.addEventListener('click', async () => {
            if (loadingOlderMessages || !oldestMessageId) return;
            loadingOlderMessages = true;
            olderMessagesButton.disabled = true;
            olderMessagesButton.textContent = 'Cargando mensajes…';

            try {
                const url = new URL(olderMessagesButton.dataset.olderUrl, window.location.origin);
                url.searchParams.set('before_id', oldestMessageId);
                const response = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('No se pudieron cargar los mensajes.');
                const data = await response.json();
                prependMessages(data.messages || []);
                if (!data.has_more) olderMessages?.remove();
            } catch (error) {
                olderMessagesButton.textContent = 'Reintentar cargar mensajes';
                return;
            } finally {
                loadingOlderMessages = false;
                if (olderMessages?.isConnected) {
                    olderMessagesButton.disabled = false;
                    if (olderMessagesButton.textContent !== 'Reintentar cargar mensajes') olderMessagesButton.textContent = 'Cargar mensajes anteriores';
                }
            }
        });

        let audioContext = null;
        const unlockMessageAudio = () => {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            audioContext ||= new AudioContext();
            if (audioContext.state === 'suspended') audioContext.resume().catch(() => {});
        };

        const playIncomingMessageSound = () => {
            try {
                unlockMessageAudio();
                if (!audioContext || audioContext.state !== 'running') return;

                const oscillator = audioContext.createOscillator();
                const gain = audioContext.createGain();
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(760, audioContext.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(980, audioContext.currentTime + 0.08);
                gain.gain.setValueAtTime(0.0001, audioContext.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.12, audioContext.currentTime + 0.015);
                gain.gain.exponentialRampToValueAtTime(0.0001, audioContext.currentTime + 0.22);
                oscillator.connect(gain);
                gain.connect(audioContext.destination);
                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.24);
            } catch (error) {
                // Browsers can block audio until the first user gesture.
            }
        };

        document.addEventListener('pointerdown', unlockMessageAudio, { once: true });
        document.addEventListener('keydown', unlockMessageAudio, { once: true });

        if (conversation && !new URLSearchParams(window.location.search).has('messages_page')) {
            const scrollToLatestMessage = () => {
                conversation.scrollTop = conversation.scrollHeight;
            };

            requestAnimationFrame(scrollToLatestMessage);
            window.addEventListener('load', scrollToLatestMessage, { once: true });
            conversation.querySelectorAll('img').forEach((image) => {
                if (!image.complete) image.addEventListener('load', scrollToLatestMessage, { once: true });
            });
        }

        const attachmentInput = document.getElementById('message-attachment');
        const stickerInput = document.querySelector('[data-sticker-input]');
        const stickerIdInput = document.querySelector('[data-sticker-id-input]');
        const stickerPanel = document.querySelector('[data-sticker-panel]');
        const stickerToggle = document.querySelector('[data-sticker-toggle]');
        const attachmentHint = document.querySelector('[data-message-attachment-hint]');
        const stickerGrid = document.querySelector('[data-sticker-grid]');
        const stickerCreateFile = document.querySelector('[data-sticker-create-file]');
        const stickerCreateControls = document.querySelector('[data-sticker-create-controls]');
        let stickerType = 'general';
        let stickerCatalog = { general: [], mine: [] };
        const clearStickerSelection = () => {
            if (stickerInput) stickerInput.value = '0';
            if (stickerIdInput) stickerIdInput.value = '';
            stickerToggle?.classList.remove('is-active');
        };
        const sendSticker = (sticker) => {
            if (!composer || stickerGrid?.dataset.sending === 'true') return;
            if (attachmentInput) attachmentInput.value = '';
            if (messageBody) messageBody.value = '';
            if (stickerInput) stickerInput.value = '1';
            if (stickerIdInput) stickerIdInput.value = sticker.id;
            clearReplyTarget();
            closeStickerPanel();
            stickerGrid?.setAttribute('data-sending', 'true');
            composer.requestSubmit();
        };
        const renderStickerCatalog = () => {
            if (!stickerGrid) return;
            if (stickerCreateControls) stickerCreateControls.hidden = stickerType !== 'mine';
            stickerGrid.replaceChildren();
            const stickers = stickerCatalog[stickerType] || [];
            if (!stickers.length) {
                const empty = document.createElement('small');
                empty.className = 'messenger-sticker-empty';
                empty.textContent = stickerType === 'mine' ? 'Aún no tienes stickers personales.' : 'Todavía no hay stickers generales.';
                stickerGrid.append(empty);
                return;
            }
            stickers.forEach((sticker) => {
                const item = document.createElement('span');
                item.className = 'messenger-sticker-item';
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.stickerSelect = String(sticker.id);
                button.setAttribute('aria-label', `Enviar sticker ${sticker.name}`);
                const image = document.createElement('img');
                image.src = sticker.url;
                image.alt = sticker.name;
                image.loading = 'eager';
                image.decoding = 'async';
                button.append(image);
                item.append(button);
                if (stickerType === 'mine') {
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'messenger-sticker-delete';
                    remove.dataset.stickerDelete = String(sticker.id);
                    remove.setAttribute('aria-label', `Eliminar ${sticker.name}`);
                    remove.textContent = '×';
                    item.append(remove);
                }
                stickerGrid.append(item);
            });
        };
        let stickerCatalogLoaded = false;
        const preloadStickerImages = () => {
            Object.values(stickerCatalog).flat().forEach((sticker) => {
                const image = new Image();
                image.decoding = 'async';
                image.src = sticker.url;
            });
        };
        const loadStickerCatalog = async ({ force = false, render = true } = {}) => {
            if (!stickerPanel) return;
            if (stickerCatalogLoaded && !force) {
                if (render) renderStickerCatalog();
                return;
            }
            try {
                const response = await fetch(@js(route('stickers.index')), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error();
                stickerCatalog = await response.json();
                stickerCatalogLoaded = true;
                preloadStickerImages();
                if (render) renderStickerCatalog();
            } catch (error) {
                if (render && stickerGrid) stickerGrid.textContent = 'No se pudieron cargar los stickers.';
            }
        };
        const closeStickerPanel = () => { if (stickerPanel) stickerPanel.hidden = true; };

        const preloadStickerCatalog = () => loadStickerCatalog({ render: false });
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(preloadStickerCatalog, { timeout: 2500 });
        } else {
            window.setTimeout(preloadStickerCatalog, 900);
        }

        stickerToggle?.addEventListener('click', async () => {
            stickerPanel.hidden = !stickerPanel.hidden;
            if (!stickerPanel.hidden) {
                await loadStickerCatalog();
                stickerPanel.querySelector('button')?.focus();
            }
        });
        document.querySelector('[data-sticker-close]')?.addEventListener('click', closeStickerPanel);
        document.querySelector('.messenger-attach-button')?.addEventListener('click', clearStickerSelection);
        stickerPanel?.querySelectorAll('[data-sticker-tab]').forEach((tab) => tab.addEventListener('click', () => {
            stickerType = tab.dataset.stickerTab;
            stickerPanel.querySelectorAll('[data-sticker-tab]').forEach((candidate) => candidate.classList.toggle('is-active', candidate === tab));
            renderStickerCatalog();
        }));
        stickerGrid?.addEventListener('click', async (event) => {
            const select = event.target.closest('[data-sticker-select]');
            if (select) {
                const sticker = (stickerCatalog[stickerType] || []).find((item) => item.id === Number(select.dataset.stickerSelect));
                if (sticker) sendSticker(sticker);
                return;
            }
            const remove = event.target.closest('[data-sticker-delete]');
            if (!remove || !window.confirm('¿Eliminar este sticker personal?')) return;
            try {
                const response = await fetch(`${@js(url('/stickers'))}/${remove.dataset.stickerDelete}`, {
                    method: 'DELETE',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error();
                clearStickerSelection();
                await loadStickerCatalog({ force: true });
            } catch (error) {
                showComposerError('No se pudo eliminar el sticker. Inténtalo de nuevo.');
            }
        });
        document.querySelector('[data-sticker-create]')?.addEventListener('click', () => {
            stickerCreateFile?.click();
        });
        stickerCreateFile?.addEventListener('change', async () => {
            const file = stickerCreateFile.files?.[0];
            if (!file) return;
            const data = new FormData();
            data.append('sticker', file);
            try {
                const response = await fetch(@js(route('stickers.store')), {
                    method: 'POST',
                    body: data,
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error();
                const result = await response.json();
                stickerType = 'mine';
                stickerPanel?.querySelectorAll('[data-sticker-tab]').forEach((tab) => tab.classList.toggle('is-active', tab.dataset.stickerTab === 'mine'));
                await loadStickerCatalog({ force: true });
                sendSticker(result.sticker);
            } catch (error) {
                showComposerError('No se pudo crear el sticker. Usa una imagen de hasta 10 MB e inténtalo de nuevo.');
            } finally {
                stickerCreateFile.value = '';
            }
        });
        attachmentInput?.addEventListener('change', () => {
            if (attachmentHint) attachmentHint.textContent = attachmentInput.files?.[0]?.name || 'Imagen o documento · máximo 20 MB';
            if (attachmentInput.files?.[0]) clearStickerSelection();
            closeStickerPanel();
        });

        const messageBody = document.getElementById('message-body');
        const resizeMessageBody = () => {
            if (!messageBody) return;
            messageBody.style.height = 'auto';
            messageBody.style.height = `${Math.min(messageBody.scrollHeight, 120)}px`;
        };
        messageBody?.addEventListener('input', resizeMessageBody);
        messageBody?.addEventListener('focus', syncMessengerViewport);
        messageBody?.addEventListener('blur', () => window.setTimeout(syncMessengerViewport, 120));
        messageBody?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
                event.preventDefault();
                messageBody.form?.requestSubmit();
            }
        });
        resizeMessageBody();

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const pollUrl = @js(route('messages.poll', $otherUser));
        let polling = false;

        const closeMessageMenus = (except = null) => {
            document.querySelectorAll('.messenger-message-menu[open]').forEach((menu) => {
                if (menu !== except) menu.open = false;
            });
        };

        const positionMessageMenu = (menu) => {
            const panel = menu?.querySelector('.messenger-message-menu-panel');
            const trigger = menu?.querySelector('summary');
            const message = menu?.closest('[data-message-id]');
            if (!panel || !trigger || !message || !conversation) return;

            const chatBounds = conversation.getBoundingClientRect();
            const triggerBounds = trigger.getBoundingClientRect();
            const panelWidth = panel.offsetWidth;
            const panelHeight = panel.offsetHeight;
            const padding = 12;
            const opensToLeft = message.classList.contains('is-outgoing');
            const desiredLeft = opensToLeft
                ? triggerBounds.left - panelWidth - 10
                : triggerBounds.right + 10;
            const left = Math.min(
                Math.max(desiredLeft, chatBounds.left + padding),
                chatBounds.right - panelWidth - padding
            );
            const desiredTop = triggerBounds.top + (triggerBounds.height - panelHeight) / 2;
            const top = Math.min(
                Math.max(desiredTop, chatBounds.top + padding),
                chatBounds.bottom - panelHeight - padding
            );

            panel.style.left = `${left}px`;
            panel.style.top = `${top}px`;
            panel.style.transform = 'none';
        };

        const replyInput = document.querySelector('[data-reply-input]');
        const replyPreview = document.querySelector('[data-reply-composer-preview]');
        const replyAuthor = document.querySelector('[data-reply-author]');
        const replyPreviewText = document.querySelector('[data-reply-preview]');
        const replyImage = document.querySelector('[data-reply-image]');

        const clearReplyTarget = () => {
            if (replyInput) replyInput.value = '';
            replyPreview?.classList.add('is-hidden');
            if (replyAuthor) replyAuthor.textContent = '';
            if (replyPreviewText) replyPreviewText.textContent = '';
            if (replyImage) {
                replyImage.hidden = true;
                replyImage.removeAttribute('src');
            }
        };

        const setReplyTarget = (message) => {
            if (!message || !replyInput || !replyPreview) return;
            replyInput.value = message.dataset.messageId || '';
            if (replyAuthor) replyAuthor.textContent = `Responder a ${message.dataset.messageAuthor || 'mensaje'}`;
            if (replyPreviewText) replyPreviewText.textContent = message.dataset.messagePreview || '';
            if (replyImage) {
                const imageUrl = message.dataset.messageImageUrl;
                replyImage.hidden = !imageUrl;
                if (imageUrl) replyImage.src = imageUrl;
                else replyImage.removeAttribute('src');
            }
            replyPreview.classList.remove('is-hidden');
            messageBody?.focus({ preventScroll: true });
            message.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        };

        document.querySelector('[data-clear-reply]')?.addEventListener('click', clearReplyTarget);

        const mediaPanel = document.querySelector('[data-media-panel]');
        const mediaResults = mediaPanel?.querySelector('[data-media-results]');
        const mediaMore = mediaPanel?.querySelector('[data-media-more]');
        let mediaType = 'images';
        let mediaPage = 1;
        let loadingMedia = false;

        const renderMediaItems = (items, replace = false) => {
            if (!mediaResults) return;
            if (replace) mediaResults.replaceChildren();
            if (!items.length && replace) {
                const empty = document.createElement('p');
                empty.className = 'messenger-media-empty';
                empty.textContent = mediaType === 'images' ? 'Aún no hay fotos en esta conversación.' : (mediaType === 'documents' ? 'Aún no hay archivos en esta conversación.' : 'Aún no hay enlaces en esta conversación.');
                mediaResults.append(empty);
                return;
            }

            items.forEach((item) => {
                const link = document.createElement('a');
                link.href = item.url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';

                if (mediaType === 'images') {
                    link.className = 'messenger-media-image';
                    const image = document.createElement('img');
                    image.src = item.url;
                    image.alt = item.name || 'Imagen compartida';
                    image.loading = 'lazy';
                    link.append(image);
                } else {
                    link.className = 'messenger-media-item';
                    const title = document.createElement('strong');
                    title.textContent = mediaType === 'links' ? (item.title || item.url) : (item.name || 'Archivo');
                    const detail = document.createElement('small');
                    detail.textContent = mediaType === 'links' ? item.url : `${item.size || ''} · ${item.sent_at || ''}`;
                    link.append(title, detail);
                }
                mediaResults.append(link);
            });
        };

        const loadMedia = async ({ reset = false } = {}) => {
            if (!mediaPanel || loadingMedia) return;
            loadingMedia = true;
            if (reset) {
                mediaPage = 1;
                mediaResults.replaceChildren();
            }
            mediaResults?.classList.add('is-loading');

            try {
                const url = new URL(mediaPanel.dataset.mediaUrl, window.location.origin);
                url.searchParams.set('type', mediaType);
                url.searchParams.set('page', mediaPage);
                const response = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error();
                const data = await response.json();
                renderMediaItems(data.items || [], mediaPage === 1);
                mediaMore.hidden = !data.has_more;
                if (data.has_more) mediaPage += 1;
            } catch (error) {
                renderMediaItems([], true);
                if (mediaResults?.firstChild) mediaResults.firstChild.textContent = 'No se pudo cargar el contenido. Inténtalo otra vez.';
                mediaMore.hidden = true;
            } finally {
                loadingMedia = false;
                mediaResults?.classList.remove('is-loading');
            }
        };

        const closeMediaPanel = () => {
            if (!mediaPanel || mediaPanel.hidden) return;
            mediaPanel.hidden = true;
            document.body.classList.remove('messenger-media-panel-open');
            document.querySelector('[data-media-panel-open]')?.focus({ preventScroll: true });
        };
        document.querySelector('[data-media-panel-open]')?.addEventListener('click', () => {
            mediaPanel.hidden = false;
            document.body.classList.add('messenger-media-panel-open');
            document.querySelector('.messenger-chat-actions[open]')?.removeAttribute('open');
            loadMedia({ reset: true });
            mediaPanel.querySelector('[data-media-panel-close]')?.focus({ preventScroll: true });
        });
        mediaPanel?.querySelectorAll('[data-media-panel-close]').forEach((button) => button.addEventListener('click', closeMediaPanel));
        mediaPanel?.querySelectorAll('[data-media-tab]').forEach((tab) => tab.addEventListener('click', () => {
            if (tab.dataset.mediaTab === mediaType) return;
            mediaType = tab.dataset.mediaTab;
            mediaPanel.querySelectorAll('[data-media-tab]').forEach((candidate) => candidate.setAttribute('aria-selected', candidate === tab ? 'true' : 'false'));
            loadMedia({ reset: true });
        }));
        mediaMore?.addEventListener('click', () => loadMedia());

        document.addEventListener('click', (event) => {
            const menu = event.target.closest('.messenger-message-menu');
            if (menu) {
                closeMessageMenus(menu);
                return;
            }

            closeMessageMenus();
        });

        document.addEventListener('toggle', (event) => {
            const menu = event.target.closest?.('.messenger-message-menu');
            if (!menu?.open) return;

            closeMessageMenus(menu);
            requestAnimationFrame(() => positionMessageMenu(menu));
        }, true);

        conversation?.addEventListener('scroll', () => closeMessageMenus(), { passive: true });
        window.addEventListener('resize', () => {
            document.querySelectorAll('.messenger-message-menu[open]').forEach(positionMessageMenu);
        });

        document.addEventListener('keydown', (event) => {
            if (!imageViewer?.hidden) {
                if (event.key === 'Escape') closeImageViewer();
                if (event.key === 'ArrowLeft') showImageViewerItem(imageViewerIndex - 1);
                if (event.key === 'ArrowRight') showImageViewerItem(imageViewerIndex + 1);
                if (['Escape', 'ArrowLeft', 'ArrowRight'].includes(event.key)) event.preventDefault();
                return;
            }
            if (mediaPanel && !mediaPanel.hidden && event.key === 'Escape') {
                closeMediaPanel();
                event.preventDefault();
                return;
            }
            if (event.key !== 'Escape') return;
            closeMessageMenus();
            clearReplyTarget();
        });

        conversation?.addEventListener('click', async (event) => {
            const imageAttachment = event.target.closest('[data-image-lightbox]');
            if (imageAttachment) {
                event.preventDefault();
                openImageViewer(imageAttachment);
                closeMessageMenus();
                return;
            }

            const replyButton = event.target.closest('[data-message-reply]');
            if (replyButton) {
                event.preventDefault();
                const message = replyButton.closest('[data-message-id]');
                setReplyTarget(message);
                closeMessageMenus();
                return;
            }

            const jump = event.target.closest('[data-jump-message]');
            if (jump) {
                const target = conversation.querySelector(`[data-message-id="${jump.dataset.jumpMessage}"]`);
                if (target) {
                    event.preventDefault();
                    target.scrollIntoView({ block: 'center', behavior: 'smooth' });
                    target.classList.add('is-highlighted');
                    window.setTimeout(() => target.classList.remove('is-highlighted'), 1200);
                }
                return;
            }

            const reactionForm = event.target.closest('[data-message-reaction-form]');
            if (!reactionForm) return;
            event.preventDefault();

            const article = reactionForm.closest('[data-message-id]');
            const menu = reactionForm.closest('.messenger-message-menu');
            const buttons = menu?.querySelectorAll('button');
            buttons?.forEach((button) => button.disabled = true);

            try {
                const response = await fetch(reactionForm.action, {
                    method: 'POST',
                    body: new FormData(reactionForm),
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                if (!response.ok) return;
                const data = await response.json();
                const template = document.createElement('template');
                template.innerHTML = data.html || '';
                const updated = template.content.querySelector('[data-message-id]');
                if (article && updated) {
                    article.replaceWith(updated);
                }
            } finally {
                buttons?.forEach((button) => button.disabled = false);
            }
        });

        let longPressTimer = null;
        conversation?.addEventListener('pointerdown', (event) => {
            if (!['touch', 'pen'].includes(event.pointerType)) return;
            if (event.target.closest('a, button, input, textarea, summary, details, form')) return;

            const message = event.target.closest('[data-message-id]');
            if (!message) return;

            longPressTimer = window.setTimeout(() => {
                const menu = message.querySelector('.messenger-message-menu');
                if (menu) {
                    closeMessageMenus(menu);
                    menu.open = true;
                    navigator.vibrate?.(12);
                }
            }, 520);
        });

        ['pointerup', 'pointercancel', 'pointermove', 'scroll'].forEach((eventName) => {
            conversation?.addEventListener(eventName, () => {
                if (longPressTimer) {
                    window.clearTimeout(longPressTimer);
                    longPressTimer = null;
                }
            }, { passive: true });
        });

        const pollMessages = async () => {
            if (!conversation || polling || document.hidden) return;
            polling = true;

            try {
                const url = new URL(pollUrl, window.location.origin);
                url.searchParams.set('after_id', latestMessageId);

                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) return;
                const data = await response.json();
                appendMessages(data.messages || [], { playSound: true });
                markOutgoingAsRead(data.read_outgoing_ids || []);
                latestMessageId = Math.max(latestMessageId, Number(data.latest_message_id) || 0);
            } finally {
                polling = false;
            }
        };

        const pollInterval = window.setInterval(pollMessages, 3500);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) pollMessages();
        });
        window.addEventListener('beforeunload', () => window.clearInterval(pollInterval));

        const composer = document.querySelector('[data-message-composer]');
        const showComposerError = (message) => {
            if (!composer) return;
            composer.parentElement?.querySelectorAll('[data-ajax-message-error]').forEach((error) => error.remove());
            const error = document.createElement('p');
            error.className = 'messenger-form-error';
            error.dataset.ajaxMessageError = '';
            error.textContent = message;
            composer.after(error);
        };

        const clearComposerError = () => {
            composer?.parentElement?.querySelectorAll('[data-ajax-message-error]').forEach((error) => error.remove());
        };

        composer?.addEventListener('submit', async (event) => {
            event.preventDefault();
            unlockMessageAudio();
            clearComposerError();

            const submitButton = composer.querySelector('button[type="submit"]');
            submitButton?.setAttribute('disabled', 'disabled');

            try {
                const response = await fetch(composer.action, {
                    method: 'POST',
                    body: new FormData(composer),
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                if (!response.ok) {
                    if (response.status === 429) {
                        showComposerError('Espera unos segundos y vuelve a intentar. La app recibió demasiadas solicitudes seguidas.');
                        return;
                    }

                    if (response.status === 422) {
                        const data = await response.json();
                        const firstError = Object.values(data.errors || {}).flat()[0] || data.message || 'No se pudo enviar el mensaje.';
                        showComposerError(firstError);
                        return;
                    }

                    showComposerError('No se pudo enviar el mensaje. Revisa tu conexión e inténtalo de nuevo.');
                    return;
                }

                const data = await response.json();
                appendMessages(data.messages || [], { forceScroll: true });
                latestMessageId = Math.max(latestMessageId, Number(data.latest_message_id) || 0);
                composer.reset();
                clearStickerSelection();
                closeStickerPanel();
                clearReplyTarget();
                if (attachmentInput) {
                    if (attachmentHint) attachmentHint.textContent = 'Imagen o documento · máximo 20 MB';
                }
                resizeMessageBody();
                syncMessengerViewport();
                messageBody?.focus({ preventScroll: true });
            } finally {
                submitButton?.removeAttribute('disabled');
                stickerGrid?.removeAttribute('data-sending');
            }
        });
    </script>
@endsection
