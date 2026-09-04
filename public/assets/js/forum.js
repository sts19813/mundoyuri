(() => {
    if (location.hash === '#moderacion') {
        const moderation = document.getElementById('moderacion');
        if (moderation) moderation.open = true;
    }
    const dialog = document.getElementById('forum-topic-dialog');
    const opener = document.querySelector('[data-open-topic-modal]');
    if (dialog && opener && typeof dialog.showModal === 'function') {
        opener.addEventListener('click', (event) => {
            event.preventDefault();
            dialog.showModal();
        });
        dialog.querySelector('[data-close-topic-modal]').addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });
        if (dialog.dataset.reopen === 'true') dialog.showModal();
    }

    const disclosures = '[data-author-card], .forum-post-menu, .forum-action-menu';
    document.addEventListener('click', (event) => {
        document.querySelectorAll(disclosures).forEach((element) => {
            if (!element.contains(event.target)) element.open = false;
        });
    });
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        document.querySelectorAll(disclosures).forEach((element) => {
            if (element.open && element.contains(document.activeElement)) element.querySelector('summary').focus();
            element.open = false;
        });
    });

    const timers = new WeakMap();
    document.addEventListener('pointerover', (event) => {
        if (!window.matchMedia('(hover: hover) and (min-width: 761px)').matches) return;
        const card = event.target.closest('[data-author-card]');
        if (!card) return;
        clearTimeout(timers.get(card));
        card.open = true;
    });
    document.addEventListener('pointerout', (event) => {
        const card = event.target.closest('[data-author-card]');
        if (!card || card.contains(event.relatedTarget)) return;
        timers.set(card, setTimeout(() => {
            if (!card.contains(document.activeElement)) card.open = false;
        }, 200));
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-feed-reply]');
        if (!form) return;
        event.preventDefault();
        if (form.dataset.sending === 'true') return;
        const button = form.querySelector('button[type="submit"]');
        const status = form.querySelector('[data-reply-status]');
        const topic = form.closest('.forum-feed-topic');
        const body = new FormData(form);
        form.dataset.sending = 'true';
        button.disabled = true;
        status.textContent = 'Publicando…';
        try {
            const response = await fetch(form.action, {
                method: 'POST', body, credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                status.textContent = data.errors?.body?.[0] || (response.status === 419 || response.status === 401
                    ? 'Tu sesión expiró. Recarga la página antes de responder.'
                    : response.status === 429 ? 'Espera un momento antes de responder de nuevo.'
                    : 'No se pudo publicar. Revisa tus permisos e inténtalo de nuevo.');
                return;
            }
            // This fragment is rendered by our Blade component; user text is escaped there.
            topic.querySelector('[data-feed-replies]').insertAdjacentHTML('beforeend', data.html);
            topic.querySelector('[data-reply-count]').textContent = new Intl.NumberFormat('es').format(data.replies_count);
            form.reset();
            status.textContent = 'Respuesta publicada.';
        } catch {
            status.textContent = 'No pudimos confirmar el envío. Comprueba tu conexión y recarga antes de reintentar.';
        } finally {
            form.dataset.sending = 'false';
            button.disabled = false;
        }
    });

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('[data-load-replies]');
        if (!link) return;
        event.preventDefault();
        if (link.dataset.loading === 'true') return;
        const label = link.textContent;
        link.dataset.loading = 'true';
        link.textContent = 'Cargando respuestas…';
        try {
            const response = await fetch(link.href, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!response.ok) throw new Error('Request failed');
            const data = await response.json();
            const replies = link.closest('.forum-feed-topic').querySelector('[data-feed-replies]');
            if (!link.dataset.expanded) replies.replaceChildren();
            const fragment = document.createRange().createContextualFragment(data.html);
            fragment.querySelectorAll('[id]').forEach((post) => {
                if (document.getElementById(post.id)) post.remove();
            });
            replies.append(fragment);
            link.dataset.expanded = 'true';
            if (data.next_page_url) {
                link.href = data.next_page_url;
                link.textContent = 'Ver más respuestas';
            } else {
                link.textContent = 'Todas las respuestas cargadas';
                link.removeAttribute('data-load-replies');
            }
        } catch {
            link.textContent = label + ' · Reintentar';
        } finally {
            link.dataset.loading = 'false';
        }
    });
})();
