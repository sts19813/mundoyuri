(() => {
    const icons = { success: '✓', error: '!', info: 'i', danger: '!' };

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#039;',
        '"': '&quot;',
    })[character]);

    const toastStack = () => {
        let stack = document.querySelector('[data-mundo-toast-stack]');
        if (stack) return stack;

        stack = document.createElement('div');
        stack.className = 'mundo-toast-stack';
        stack.dataset.mundoToastStack = 'true';
        stack.setAttribute('aria-live', 'polite');
        stack.setAttribute('aria-atomic', 'true');
        document.body.append(stack);
        return stack;
    };

    const toast = (type = 'success', message = '') => {
        const item = document.createElement('div');
        item.className = 'mundo-toast';
        item.dataset.type = type;
        item.innerHTML = `<span class="mundo-toast-icon" aria-hidden="true">${icons[type] || icons.info}</span><strong>${escapeHtml(message)}</strong>`;
        toastStack().append(item);

        window.setTimeout(() => {
            item.classList.add('is-leaving');
            window.setTimeout(() => item.remove(), 200);
        }, type === 'error' ? 5200 : 3000);
    };

    const modal = ({ kind = 'info', title = '¿Continuar?', message = '', confirmText = 'Confirmar', cancelText = 'Cancelar', input = false, placeholder = '' } = {}) => new Promise((resolve) => {
        const layer = document.createElement('div');
        layer.className = 'mundo-dialog-layer';
        layer.innerHTML = `
            <div class="mundo-dialog-backdrop" data-mundo-dialog-cancel></div>
            <section class="mundo-dialog" data-kind="${escapeHtml(kind)}" role="dialog" aria-modal="true" aria-labelledby="mundo-dialog-title">
                <span class="mundo-dialog-icon" aria-hidden="true">${icons[kind] || icons.info}</span>
                <h2 id="mundo-dialog-title">${escapeHtml(title)}</h2>
                ${message ? `<p>${escapeHtml(message)}</p>` : ''}
                ${input ? `<input type="url" inputmode="url" placeholder="${escapeHtml(placeholder)}" data-mundo-dialog-input>` : ''}
                <div class="mundo-dialog-actions">
                    <button class="mundo-dialog-cancel" type="button" data-mundo-dialog-cancel>${escapeHtml(cancelText)}</button>
                    <button class="mundo-dialog-confirm" type="button" data-mundo-dialog-confirm>${escapeHtml(confirmText)}</button>
                </div>
            </section>`;
        document.body.append(layer);

        const inputElement = layer.querySelector('[data-mundo-dialog-input]');
        const finish = (result) => {
            layer.remove();
            resolve(result);
        };

        layer.querySelector('[data-mundo-dialog-confirm]')?.addEventListener('click', () => finish(input ? inputElement?.value.trim() || null : true));
        layer.querySelectorAll('[data-mundo-dialog-cancel]').forEach((button) => button.addEventListener('click', () => finish(input ? null : false)));
        layer.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') finish(input ? null : false);
            if (event.key === 'Enter' && input) finish(inputElement?.value.trim() || null);
        });
        window.setTimeout(() => (inputElement || layer.querySelector('[data-mundo-dialog-confirm]'))?.focus(), 0);
    });

    window.MundoYuriDialog = {
        toast,
        confirm: (options) => modal(typeof options === 'string' ? { message: options, kind: 'danger', title: '¿Estás segura?', confirmText: 'Sí, continuar' } : { kind: 'danger', title: '¿Estás segura?', confirmText: 'Sí, continuar', ...options }),
        prompt: (options) => modal(typeof options === 'string' ? { title: options, input: true, confirmText: 'Continuar' } : { input: true, confirmText: 'Continuar', ...options }),
    };

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest?.('form[data-dialog-confirm]');
        if (!form) return;
        if (form.dataset.dialogConfirmed === 'true') {
            delete form.dataset.dialogConfirmed;
            return;
        }

        event.preventDefault();
        const confirmed = await window.MundoYuriDialog.confirm({
            title: form.dataset.dialogTitle || '¿Estás segura?',
            message: form.dataset.dialogConfirm,
            confirmText: form.dataset.dialogConfirmText || 'Sí, continuar',
        });
        if (!confirmed) return;

        form.dataset.dialogConfirmed = 'true';
        form.requestSubmit(event.submitter);
    }, true);
})();
