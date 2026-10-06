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
                            <div class="messenger-older-link"><a href="{{ $messages->nextPageUrl() }}">Ver mensajes anteriores</a></div>
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
                                    <strong data-reply-author></strong>
                                    <span data-reply-preview></span>
                                </div>
                                <button type="button" data-clear-reply aria-label="Cancelar respuesta">×</button>
                            </div>
                            <form method="POST" action="{{ route('messages.store', $otherUser) }}" class="messenger-composer" enctype="multipart/form-data" data-message-composer>
                                @csrf
                                <input type="hidden" name="reply_to_message_id" value="" data-reply-input>
                                <input id="message-attachment" type="file" name="attachment" accept="image/jpeg,image/png,image/webp,image/gif,.pdf,.txt,.csv,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.odp">
                                <label for="message-attachment" class="messenger-attach-button" aria-label="Adjuntar imagen o documento" title="Adjuntar imagen o documento">
                                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m21.4 11.6-8.9 8.9a6 6 0 0 1-8.5-8.5l9.5-9.5a4 4 0 0 1 5.7 5.7l-9.6 9.6a2 2 0 0 1-2.8-2.8l8.9-8.9"/></svg>
                                </label>
                                <div class="messenger-compose-input">
                                    <label class="visually-hidden" for="message-body">Escribe un mensaje</label>
                                    <textarea id="message-body" name="body" rows="1" maxlength="2000" placeholder="Aa">{{ old('body') }}</textarea>
                                    <small data-message-attachment-hint>Imagen o documento · máximo 20 MB</small>
                                </div>
                                <button class="messenger-send-button" type="submit" aria-label="Enviar mensaje">
                                    <svg width="21" height="21" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.8 2.5a1 1 0 0 0-1.05-.18L2.9 10.15a1 1 0 0 0 .1 1.87l7.12 2.37 2.37 7.12a1 1 0 0 0 .9.68h.05a1 1 0 0 0 .91-.59l7.63-17.9a1 1 0 0 0-.18-1.2ZM12 13.06 6.12 11.1l11.9-5.22Z"/></svg>
                                </button>
                            </form>
                            @error('body')<p class="messenger-form-error">{{ $message }}</p>@enderror
                            @error('attachment')<p class="messenger-form-error">{{ $message }}</p>@enderror
                        @endif
                    </footer>
                </section>
            </section>
        </div>
    </main>

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
        let latestMessageId = conversation
            ? Math.max(0, ...Array.from(conversation.querySelectorAll('[data-message-id]')).map((message) => Number(message.dataset.messageId) || 0))
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
        attachmentInput?.addEventListener('change', () => {
            const hint = document.querySelector('[data-message-attachment-hint]');
            if (hint) hint.textContent = attachmentInput.files?.[0]?.name || 'Imagen o documento · máximo 20 MB';
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

        const clearReplyTarget = () => {
            if (replyInput) replyInput.value = '';
            replyPreview?.classList.add('is-hidden');
            if (replyAuthor) replyAuthor.textContent = '';
            if (replyPreviewText) replyPreviewText.textContent = '';
        };

        const setReplyTarget = (message) => {
            if (!message || !replyInput || !replyPreview) return;
            replyInput.value = message.dataset.messageId || '';
            if (replyAuthor) replyAuthor.textContent = `Responder a ${message.dataset.messageAuthor || 'mensaje'}`;
            if (replyPreviewText) replyPreviewText.textContent = message.dataset.messagePreview || '';
            replyPreview.classList.remove('is-hidden');
            messageBody?.focus({ preventScroll: true });
            message.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        };

        document.querySelector('[data-clear-reply]')?.addEventListener('click', clearReplyTarget);

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
            if (event.key !== 'Escape') return;
            closeMessageMenus();
            clearReplyTarget();
        });

        conversation?.addEventListener('click', async (event) => {
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
                    if (response.status === 422) {
                        const data = await response.json();
                        const firstError = Object.values(data.errors || {}).flat()[0] || data.message || 'No se pudo enviar el mensaje.';
                        showComposerError(firstError);
                        return;
                    }

                    HTMLFormElement.prototype.submit.call(composer);
                    return;
                }

                const data = await response.json();
                appendMessages(data.messages || [], { forceScroll: true });
                latestMessageId = Math.max(latestMessageId, Number(data.latest_message_id) || 0);
                composer.reset();
                clearReplyTarget();
                if (attachmentInput) {
                    const hint = document.querySelector('[data-message-attachment-hint]');
                    if (hint) hint.textContent = 'Imagen o documento · máximo 20 MB';
                }
                resizeMessageBody();
                syncMessengerViewport();
                messageBody?.focus({ preventScroll: true });
            } finally {
                submitButton?.removeAttribute('disabled');
            }
        });
    </script>
@endsection
