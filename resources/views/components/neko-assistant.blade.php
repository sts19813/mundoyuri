@php
    $assistantMessages = [
        [
            'text' => '¡Hola! Soy tu asistente Miyu.',
            'peek' => '¡Hola! Soy tu asistente Miyu.',
        ],
        [
            'text' => '¿Encontraste un problema en la página? Ayúdanos a mejorar.',
            'peek' => '¿Encontraste un problema en la página? Ayúdanos a mejorar.',
            'label' => 'Reportar un problema',
            'formType' => 'report',
        ],
        [
            'text' => '¿Hay alguna serie o película que te encantaría ver aquí? Coméntanos para subirla.',
            'peek' => '¿Hay alguna serie o película que te gustaría ver aquí?',
            'label' => 'Coméntanos cuál',
            'formType' => 'request',
        ],
    ];

    if (auth()->guest()) {
        $assistantMessages[] = [
            'text' => 'Crea una cuenta gratis para preparar tu lista de favoritas y descubrir las próximas novedades.',
            'peek' => 'Crea una cuenta gratis para guardar tus favoritas y descubrir novedades.',
            'label' => 'Crear cuenta gratis',
            'url' => route('register'),
        ];
    }

    $assistantMessages[] = [
        'text' => '¿Quieres mandarnos un mensaje? Puedes hacerlo sin salir de esta página.',
        'peek' => '¿Quieres mandarnos un mensaje?',
        'label' => 'Escribir mensaje',
        'formType' => 'message',
    ];

    $assistantMessages[] = [
        'text' => '¿No sabes qué ver? Explora nuestras series y películas disponibles.',
        'peek' => '¿Buscas algo nuevo para ver?',
        'label' => 'Explorar el catálogo',
        'url' => route('catalog.series.index'),
    ];
@endphp

<aside class="miyu-assistant" data-miyu-assistant aria-label="Miyu, asistente de Mundo Yuri">
    <div class="miyu-assistant__stage">
        <section class="miyu-assistant__bubble" data-miyu-bubble aria-live="polite">
            <button class="miyu-assistant__dismiss" type="button" data-miyu-minimize
                aria-label="Minimizar a Miyu" title="Minimizar asistente">×</button>

            <div class="miyu-assistant__message" data-miyu-message-view>
                <span class="miyu-assistant__eyebrow">
                    <span aria-hidden="true">✦</span> Miyu · asistente
                </span>
                <p data-miyu-message></p>
                <a class="miyu-assistant__action" data-miyu-action href="#"></a>
            </div>

            <form class="miyu-assistant__form" data-miyu-form hidden>
                <input type="hidden" name="type" data-miyu-form-type>
                <input type="hidden" name="page_url" data-miyu-page-url>

                <button class="miyu-assistant__back" type="button" data-miyu-back>
                    ← Volver
                </button>
                <label for="miyu-contact-email">Tu correo <span>(opcional)</span></label>
                <input id="miyu-contact-email" name="contact_email" type="email"
                    autocomplete="email" maxlength="255" placeholder="Para poder responderte">

                <label for="miyu-contact-message" data-miyu-form-label>Cuéntanos</label>
                <textarea id="miyu-contact-message" name="message" rows="4" minlength="10"
                    maxlength="1500" required placeholder="Escribe aquí tu mensaje…"></textarea>

                <p class="miyu-assistant__form-status" data-miyu-form-status aria-live="polite"></p>
                <button class="miyu-assistant__submit" type="submit">Enviar al equipo</button>
            </form>
        </section>

        <button class="miyu-assistant__mascot" type="button" data-miyu-mascot
            aria-label="Abrir asistente Miyu" aria-expanded="false">
            <span class="miyu-assistant__character" aria-hidden="true">
                <img class="miyu-assistant__frame miyu-assistant__frame--open"
                    src="{{ asset('assets/img/assistant/yuri-neko-open.webp') }}"
                    width="382" height="640" alt="">
                <img class="miyu-assistant__frame miyu-assistant__frame--blink"
                    src="{{ asset('assets/img/assistant/yuri-neko-blink.webp') }}"
                    width="382" height="640" alt="">
                <span class="miyu-assistant__gaze">
                    <span class="miyu-assistant__pupil miyu-assistant__pupil--left"></span>
                    <span class="miyu-assistant__pupil miyu-assistant__pupil--right"></span>
                </span>
            </span>
        </button>
    </div>

    <button class="miyu-assistant__restore" type="button" data-miyu-restore
        aria-label="Mostrar a Miyu" title="Mostrar asistente">
        <span aria-hidden="true">🐾</span>
    </button>

    <button class="miyu-assistant__peek" type="button" data-miyu-peek
        aria-label="Abrir mensaje de Miyu" aria-live="polite">
        <span data-miyu-peek-text></span>
    </button>
</aside>

<style>
    .miyu-assistant {
        --miyu-rose: #f43f8e;
        --miyu-wine: #7f294b;
        --miyu-ink: #351d2a;
        --miyu-paper: #fff9fc;
        position: fixed;
        z-index: 2147483000;
        right: 12px;
        bottom: max(4px, env(safe-area-inset-bottom));
        width: 150px;
        height: 240px;
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease;
        font-family: "DM Sans", Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        line-height: 1.45;
        color: var(--miyu-ink);
        isolation: isolate;
    }

    .miyu-assistant[data-ready] { opacity: 1; }
    .miyu-assistant *, .miyu-assistant *::before, .miyu-assistant *::after { box-sizing: border-box; }
    .miyu-assistant__stage { position: absolute; inset: 0; }

    .miyu-assistant__bubble {
        position: absolute;
        z-index: 2;
        right: 82px;
        bottom: 155px;
        width: min(270px, calc(100vw - 102px));
        padding: 16px 17px 15px;
        border: 1px solid rgba(244, 63, 142, .22);
        border-radius: 22px 22px 8px 22px;
        background:
            radial-gradient(circle at 14% 2%, rgba(244, 63, 142, .1), transparent 37%),
            rgba(255, 249, 252, .97);
        box-shadow: 0 20px 55px rgba(31, 12, 22, .25), 0 3px 12px rgba(31, 12, 22, .12);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        pointer-events: auto;
        transform-origin: 100% 100%;
        transition: opacity .22s ease, transform .22s cubic-bezier(.2, .8, .2, 1), visibility .22s;
    }

    .miyu-assistant__bubble::after {
        content: "";
        position: absolute;
        right: -14px;
        bottom: 20px;
        width: 26px;
        height: 26px;
        border-right: 1px solid rgba(244, 63, 142, .2);
        border-bottom: 1px solid rgba(244, 63, 142, .2);
        background: var(--miyu-paper);
        transform: rotate(-45deg);
    }

    .miyu-assistant:not(.is-speaking) .miyu-assistant__bubble {
        opacity: 0;
        visibility: hidden;
        transform: translate(10px, 12px) scale(.92);
        pointer-events: none;
    }

    .miyu-assistant__dismiss {
        position: absolute;
        z-index: 2;
        top: 8px;
        right: 9px;
        width: 30px;
        height: 30px;
        padding: 0 0 2px;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: #8d7180;
        font: 400 23px/1 ui-sans-serif, system-ui, sans-serif;
        cursor: pointer;
        transition: color .16s ease, background .16s ease, transform .16s ease;
    }

    .miyu-assistant__dismiss:hover,
    .miyu-assistant__dismiss:focus-visible {
        color: var(--miyu-wine);
        background: rgba(244, 63, 142, .1);
        transform: rotate(5deg);
        outline: none;
    }

    .miyu-assistant__eyebrow {
        display: block;
        margin: 0 32px 8px 0;
        color: var(--miyu-rose);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .miyu-assistant__message p {
        margin: 0 0 12px;
        color: var(--miyu-ink);
        font-size: 13px;
        font-weight: 600;
        line-height: 1.5;
    }

    .miyu-assistant__message p[role="button"] { cursor: pointer; }
    .miyu-assistant__message p[role="button"]:focus-visible {
        border-radius: 6px;
        outline: 2px solid rgba(244, 63, 142, .45);
        outline-offset: 3px;
    }

    .miyu-assistant__action,
    .miyu-assistant__submit {
        display: inline-flex;
        min-height: 37px;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        border: 0;
        border-radius: 999px;
        background: linear-gradient(135deg, #f65398, #d92d78);
        box-shadow: 0 8px 20px rgba(217, 45, 120, .24);
        color: #fff !important;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.2;
        text-decoration: none !important;
        cursor: pointer;
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .miyu-assistant__action[hidden] { display: none; }

    .miyu-assistant__action:hover,
    .miyu-assistant__action:focus-visible,
    .miyu-assistant__submit:hover,
    .miyu-assistant__submit:focus-visible {
        color: #fff !important;
        transform: translateY(-1px);
        box-shadow: 0 11px 24px rgba(217, 45, 120, .3);
        outline: none;
    }

    .miyu-assistant__back {
        border: 0;
        background: transparent;
        color: #9d6b82;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }

    .miyu-assistant__back { margin: -5px 0 10px; padding: 3px 0; }
    .miyu-assistant__back:hover { color: var(--miyu-wine); }

    .miyu-assistant__form label {
        display: block;
        margin: 7px 0 4px;
        color: var(--miyu-ink);
        font-size: 11px;
        font-weight: 800;
    }

    .miyu-assistant__form label span { color: #a98d9a; font-weight: 600; }

    .miyu-assistant__form input,
    .miyu-assistant__form textarea {
        display: block;
        width: 100%;
        padding: 8px 10px;
        border: 1px solid rgba(127, 41, 75, .18);
        border-radius: 10px;
        outline: none;
        background: rgba(255, 255, 255, .82);
        color: var(--miyu-ink);
        font-family: inherit;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.4;
        resize: vertical;
    }

    .miyu-assistant__form textarea { min-height: 74px; max-height: 150px; }
    .miyu-assistant__form input:focus, .miyu-assistant__form textarea:focus {
        border-color: rgba(244, 63, 142, .62);
        box-shadow: 0 0 0 3px rgba(244, 63, 142, .09);
    }

    .miyu-assistant__form-status {
        min-height: 16px;
        margin: 5px 0;
        color: #a62c59;
        font-size: 11px;
        font-weight: 700;
    }

    .miyu-assistant__submit { width: 100%; }

    .miyu-assistant__mascot {
        position: absolute;
        z-index: 1;
        right: 0;
        bottom: 0;
        width: 140px;
        height: 230px;
        padding: 0;
        overflow: visible;
        border: 0;
        outline: none;
        background: transparent;
        pointer-events: auto;
        cursor: pointer;
        filter: drop-shadow(0 14px 15px rgba(28, 8, 17, .24));
    }

    .miyu-assistant__mascot:focus-visible .miyu-assistant__character {
        filter: drop-shadow(0 0 9px rgba(244, 63, 142, .72));
    }

    .miyu-assistant__character {
        position: absolute;
        inset: 0;
        transition: filter .18s ease;
    }

    .miyu-assistant__frame {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center bottom;
        user-select: none;
        -webkit-user-drag: none;
        transition: opacity 28ms linear;
    }

    .miyu-assistant__frame--blink { opacity: 0; }
    .miyu-assistant.is-blinking .miyu-assistant__frame--open { opacity: 0; }
    .miyu-assistant.is-blinking .miyu-assistant__frame--blink { opacity: 1; }

    .miyu-assistant__gaze {
        position: absolute;
        inset: 0;
        z-index: 2;
        pointer-events: none;
        transition: opacity 28ms linear;
    }

    .miyu-assistant__pupil {
        position: absolute;
        width: 5px;
        height: 7px;
        border-radius: 50%;
        background:
            radial-gradient(circle at 34% 25%, #fff 0 12%, transparent 15%),
            linear-gradient(180deg, #164f68, #082f43);
        box-shadow: 0 0 0 1px rgba(5, 37, 51, .12);
        opacity: .88;
        transform: translate3d(var(--miyu-gaze-x, 0px), var(--miyu-gaze-y, 0px), 0);
        transition: transform 80ms ease-out;
        will-change: transform;
    }

    .miyu-assistant__pupil--left { left: 65px; top: 59px; }
    .miyu-assistant__pupil--right { left: 85.5px; top: 53px; }
    .miyu-assistant.is-blinking .miyu-assistant__gaze { opacity: 0; }

    .miyu-assistant__restore {
        position: absolute;
        right: 0;
        bottom: 10px;
        display: grid;
        width: 50px;
        height: 50px;
        place-items: center;
        padding: 0;
        border: 1px solid rgba(244, 63, 142, .28);
        border-radius: 50% 50% 8px 50%;
        background: linear-gradient(145deg, #fff9fc, #ffe5f0);
        box-shadow: 0 12px 30px rgba(31, 12, 22, .2);
        color: var(--miyu-wine);
        font-size: 21px;
        cursor: pointer;
        pointer-events: auto;
        opacity: 0;
        visibility: hidden;
        transform: scale(.75);
        transition: opacity .2s ease, transform .2s ease, visibility .2s;
    }

    .miyu-assistant.is-minimized { width: 58px; height: 68px; }
    .miyu-assistant.is-minimized .miyu-assistant__stage { display: none; }
    .miyu-assistant.is-minimized .miyu-assistant__restore {
        opacity: 1;
        visibility: visible;
        transform: scale(1);
    }

    .miyu-assistant__peek {
        position: absolute;
        right: 8px;
        bottom: 67px;
        width: min(230px, calc(100vw - 34px));
        padding: 11px 14px;
        border: 1px solid rgba(244, 63, 142, .2);
        border-radius: 15px 15px 4px 15px;
        background: rgba(255, 249, 252, .97);
        box-shadow: 0 12px 30px rgba(31, 12, 22, .2);
        color: var(--miyu-ink);
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
        text-align: left;
        cursor: pointer;
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        transform: translateY(7px) scale(.96);
        transform-origin: 100% 100%;
        transition: opacity .18s ease, transform .18s ease, visibility .18s;
    }

    .miyu-assistant__peek::after {
        content: "";
        position: absolute;
        right: 17px;
        bottom: -6px;
        width: 12px;
        height: 12px;
        border-right: 1px solid rgba(244, 63, 142, .2);
        border-bottom: 1px solid rgba(244, 63, 142, .2);
        background: #fff9fc;
        transform: rotate(45deg);
    }

    .miyu-assistant.is-minimized.is-peeking .miyu-assistant__peek {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }

    .miyu-assistant__peek:hover,
    .miyu-assistant__peek:focus-visible {
        border-color: rgba(244, 63, 142, .48);
        outline: none;
    }

    [data-bs-theme="dark"] .miyu-assistant__bubble {
        border-color: rgba(244, 113, 168, .25);
        background:
            radial-gradient(circle at 14% 2%, rgba(244, 63, 142, .14), transparent 37%),
            rgba(35, 19, 29, .96);
        box-shadow: 0 20px 55px rgba(0, 0, 0, .42);
    }

    [data-bs-theme="dark"] .miyu-assistant__bubble::after { background: #23131d; }
    [data-bs-theme="dark"] .miyu-assistant__peek {
        border-color: rgba(244, 113, 168, .25);
        background: rgba(35, 19, 29, .97);
        color: #fff5fa;
    }
    [data-bs-theme="dark"] .miyu-assistant__peek::after {
        border-color: rgba(244, 113, 168, .25);
        background: #23131d;
    }
    [data-bs-theme="dark"] .miyu-assistant__message p,
    [data-bs-theme="dark"] .miyu-assistant__form label { color: #fff5fa; }
    [data-bs-theme="dark"] .miyu-assistant__form input,
    [data-bs-theme="dark"] .miyu-assistant__form textarea {
        border-color: rgba(255, 255, 255, .13);
        background: rgba(255, 255, 255, .075);
        color: #fff;
    }

    @media (max-width: 600px) {
        .miyu-assistant__bubble {
            right: 72px;
            bottom: 151px;
            width: min(260px, calc(100vw - 92px));
            padding: 15px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .miyu-assistant *, .miyu-assistant *::before, .miyu-assistant *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
</style>

<script>
    (() => {
        const root = document.currentScript?.previousElementSibling?.previousElementSibling;

        if (!root || !root.matches('[data-miyu-assistant]')) {
            return;
        }

        if (window.__miyuAssistantLoaded) {
            root.remove();
            return;
        }

        window.__miyuAssistantLoaded = true;

        const messages = @js($assistantMessages);
        const storageKey = 'mundoyuri.miyu.minimized';
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const mascot = root.querySelector('[data-miyu-mascot]');
        const messageView = root.querySelector('[data-miyu-message-view]');
        const messageText = root.querySelector('[data-miyu-message]');
        const action = root.querySelector('[data-miyu-action]');
        const form = root.querySelector('[data-miyu-form]');
        const formType = root.querySelector('[data-miyu-form-type]');
        const formLabel = root.querySelector('[data-miyu-form-label]');
        const formStatus = root.querySelector('[data-miyu-form-status]');
        const pageUrl = root.querySelector('[data-miyu-page-url]');
        const peek = root.querySelector('[data-miyu-peek]');
        const peekText = root.querySelector('[data-miyu-peek-text]');
        let index = -1;
        let bubbleTimer;
        let blinkTimer;
        let messageTimer;
        let peekTimer;
        let pointerFrame;
        let peekMessage;

        const setSpeaking = (speaking) => {
            root.classList.toggle('is-speaking', speaking);
            mascot.setAttribute('aria-expanded', speaking ? 'true' : 'false');
            window.clearTimeout(bubbleTimer);

            if (speaking && form.hidden) {
                bubbleTimer = window.setTimeout(() => setSpeaking(false), 7000);
            }
        };

        const showMessage = (nextIndex = index) => {
            index = (nextIndex + messages.length) % messages.length;
            const message = messages[index];

            form.hidden = true;
            messageView.hidden = false;
            messageText.textContent = message.text;
            action.hidden = !message.label;
            action.textContent = message.label || '';

            if (message.formType) {
                action.href = '#';
                action.setAttribute('role', 'button');
                action.dataset.formType = message.formType;
                messageText.setAttribute('role', 'button');
                messageText.tabIndex = 0;
            } else if (message.url) {
                action.href = message.url;
                action.removeAttribute('role');
                delete action.dataset.formType;
                messageText.removeAttribute('role');
                messageText.removeAttribute('tabindex');
            } else {
                action.removeAttribute('href');
                action.removeAttribute('role');
                delete action.dataset.formType;
                messageText.removeAttribute('role');
                messageText.removeAttribute('tabindex');
            }

            setSpeaking(true);
        };

        const hidePeek = () => {
            window.clearTimeout(peekTimer);
            root.classList.remove('is-peeking');
        };

        const showPeek = (nextIndex) => {
            index = (nextIndex + messages.length) % messages.length;
            peekMessage = messages[index];
            peekText.textContent = peekMessage.peek || peekMessage.text;
            peek.dataset.messageIndex = String(index);
            peek.dataset.formType = peekMessage.formType || '';
            root.classList.add('is-peeking');
            window.clearTimeout(peekTimer);
            peekTimer = window.setTimeout(hidePeek, 7000);
        };

        const showForm = (type) => {
            const labels = {
                report: '¿Qué problema encontraste?',
                request: '¿Qué serie o película buscas?',
                message: '¿Qué quieres decirnos?',
            };

            window.clearTimeout(bubbleTimer);
            messageView.hidden = true;
            form.hidden = false;
            form.reset();
            formType.value = type;
            pageUrl.value = window.location.href;
            formLabel.textContent = labels[type] || 'Cuéntanos';
            formStatus.textContent = '';
            setSpeaking(true);
            form.querySelector('textarea').focus();
        };

        const minimize = () => {
            root.classList.add('is-minimized');
            root.classList.remove('is-speaking');
            localStorage.setItem(storageKey, '1');
            window.clearTimeout(bubbleTimer);
            hidePeek();
            scheduleMessage();
        };

        const expandAssistant = () => {
            hidePeek();
            root.classList.remove('is-minimized');
            localStorage.removeItem(storageKey);
            setSpeaking(false);
            scheduleBlink();
            scheduleMessage();
        };

        const restore = () => expandAssistant();

        const scheduleBlink = () => {
            window.clearTimeout(blinkTimer);

            if (reducedMotion.matches || root.classList.contains('is-minimized')) {
                return;
            }

            blinkTimer = window.setTimeout(() => {
                root.classList.add('is-blinking');
                window.setTimeout(() => root.classList.remove('is-blinking'), 145);
                scheduleBlink();
            }, 3600 + Math.random() * 4200);
        };

        const followPointer = (event) => {
            if (reducedMotion.matches || root.classList.contains('is-minimized')) {
                return;
            }

            window.cancelAnimationFrame(pointerFrame);
            pointerFrame = window.requestAnimationFrame(() => {
                const box = mascot.getBoundingClientRect();
                const centerX = box.left + box.width * .52;
                const centerY = box.top + box.height * .33;
                const x = Math.max(-1, Math.min(1, (event.clientX - centerX) / (window.innerWidth * .55)));
                const y = Math.max(-1, Math.min(1, (event.clientY - centerY) / (window.innerHeight * .55)));
                root.style.setProperty('--miyu-gaze-x', `${(x * 1.8).toFixed(1)}px`);
                root.style.setProperty('--miyu-gaze-y', `${(y * 1.2).toFixed(1)}px`);
            });
        };

        const scheduleMessage = () => {
            window.clearTimeout(messageTimer);
            messageTimer = window.setTimeout(() => {
                if (root.classList.contains('is-minimized')) {
                    showPeek(index + 1);
                } else if (!root.classList.contains('is-speaking')) {
                    showMessage(index + 1);
                }

                scheduleMessage();
            }, 20000);
        };

        root.querySelector('[data-miyu-minimize]').addEventListener('click', minimize);
        root.querySelector('[data-miyu-restore]').addEventListener('click', restore);
        root.querySelector('[data-miyu-back]').addEventListener('click', () => showMessage(index));
        peek.addEventListener('click', () => {
            const selectedIndex = Number.parseInt(peek.dataset.messageIndex || '', 10);
            const selectedType = peek.dataset.formType;

            if (!Number.isFinite(selectedIndex)) {
                return;
            }

            expandAssistant();

            window.requestAnimationFrame(() => {
                if (selectedType) {
                    showForm(selectedType);
                    return;
                }

                showMessage(selectedIndex);
            });
        });

        mascot.addEventListener('click', () => {
            if (root.classList.contains('is-speaking')) {
                setSpeaking(false);
            } else {
                showMessage(index + 1);
            }
        });

        const openSelectedForm = () => {
            if (action.dataset.formType) {
                showForm(action.dataset.formType);
            }
        };

        messageText.addEventListener('click', openSelectedForm);
        messageText.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openSelectedForm();
            }
        });

        action.addEventListener('click', (event) => {
            if (action.dataset.formType) {
                event.preventDefault();
                showForm(action.dataset.formType);
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const submit = form.querySelector('[type="submit"]');
            submit.disabled = true;
            submit.textContent = 'Enviando…';
            formStatus.textContent = '';

            try {
                const response = await fetch(@js(route('assistant-messages.store')), {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @js(csrf_token()),
                    },
                    body: JSON.stringify(Object.fromEntries(new FormData(form))),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || 'No pude enviar el mensaje. Inténtalo de nuevo.');
                }

                formStatus.textContent = payload.message || '¡Listo! Tu mensaje llegó al equipo.';
                form.reset();
                window.setTimeout(() => setSpeaking(false), 2400);
            } catch (error) {
                formStatus.textContent = error.message;
            } finally {
                submit.disabled = false;
                submit.textContent = 'Enviar al equipo';
            }
        });

        document.addEventListener('pointermove', followPointer, { passive: true });
        reducedMotion.addEventListener?.('change', scheduleBlink);

        const startsMinimized = localStorage.getItem(storageKey) === '1';

        if (startsMinimized) {
            root.classList.add('is-minimized');
        }

        root.setAttribute('data-ready', '');
        startsMinimized ? showPeek(0) : showMessage(0);
        scheduleBlink();
        scheduleMessage();
    })();
</script>
