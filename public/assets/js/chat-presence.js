(function (root) {
    function sameDate(first, second) {
        return first.getFullYear() === second.getFullYear()
            && first.getMonth() === second.getMonth()
            && first.getDate() === second.getDate();
    }

    function format(lastSeen, currentTime) {
        const seen = new Date(lastSeen);
        const now = currentTime ? new Date(currentTime) : new Date();
        if (Number.isNaN(seen.getTime()) || Number.isNaN(now.getTime())) return 'Última conexión';

        const yesterday = new Date(now);
        yesterday.setDate(yesterday.getDate() - 1);
        const date = sameDate(seen, now) ? 'hoy' : sameDate(seen, yesterday) ? 'ayer'
            : seen.toLocaleDateString('es-MX', { day: 'numeric', month: 'short', year: 'numeric' });
        const hours = seen.getHours();
        const time = `${hours % 12 || 12}:${String(seen.getMinutes()).padStart(2, '0')}${hours < 12 ? 'am' : 'pm'}`;
        return `Última vez ${date} a las ${time}`;
    }

    function render(presence) {
        const label = document.querySelector('[data-chat-presence-label]');
        const avatar = document.querySelector('[data-chat-presence-avatar]');
        if (label) {
            label.textContent = !presence.is_online && presence.last_seen_at
                ? format(presence.last_seen_at)
                : presence.label;
        }
        avatar?.classList.toggle('is-online', Boolean(presence.is_online));
    }

    const api = { format, render };
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    if (root) {
        root.MundoYuriChatPresence = api;
        const label = document.querySelector('[data-chat-presence-label]');
        if (label) render({
            label: label.textContent,
            is_online: label.dataset.isOnline === '1',
            last_seen_at: label.dataset.lastSeenAt || null,
        });
    }
})(typeof window !== 'undefined' ? window : null);
