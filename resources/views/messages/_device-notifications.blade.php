<aside class="messenger-device-notice" data-device-notice hidden aria-labelledby="device-notice-title">
    <img class="messenger-device-icon" src="{{ asset('assets/img/pwa/icon-192.png') }}" width="40" height="40" alt="">
    <div class="messenger-device-copy">
        <h2 id="device-notice-title" data-device-title>Que no se te pase ningún mensaje</h2>
        <p data-device-description>Instala la app de MundoYuri o activa las notificaciones para enterarte de tus mensajes y novedades GL/yuri, incluso cuando no estés aquí.</p>
        <p class="messenger-device-promise">MundoYuri jamás te enviará spam ni avisos ajenos al GL/yuri y a tu actividad en la comunidad. Tú decides: puedes desactivarlos cuando quieras.</p>
        <p class="messenger-device-status" data-device-status role="status" hidden></p>
        <details class="messenger-device-help" data-pwa-install-help hidden>
            <summary>Cómo instalar MundoYuri</summary>
            <p data-pwa-install-instructions></p>
        </details>
    </div>
    <div class="messenger-device-actions">
        <button type="button" class="messenger-device-button messenger-device-primary" data-push-toggle data-push-enable aria-pressed="false" disabled>Activar notificaciones</button>
        <button type="button" class="messenger-device-button" data-pwa-install hidden>Instalar app</button>
    </div>
    <button type="button" class="messenger-device-dismiss" data-device-dismiss aria-label="Cerrar recomendación" title="Cerrar recomendación">&times;</button>
</aside>
