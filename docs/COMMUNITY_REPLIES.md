# Respuestas dirigidas y comentarios del catálogo

Foros y preguntas conservan `ForumPost` y sus contadores. `reply_to_post_id`
identifica el mensaje padre dentro de la misma conversación. La vista lo renderiza
como árbol, de forma que cada respuesta aparece inmediatamente bajo el mensaje al
que responde; no se duplica su contenido en una cita. Las respuestas antiguas sin
destinatario explícito se agrupan bajo el mensaje inicial. La sangría visual se
limita a seis niveles para proteger la lectura móvil, sin alterar la relación real.
Los hilos muestran las primeras 100 respuestas y solo ofrecen cargar todas cuando
existen más de 100.

En comentarios de episodios y series, `parent_id` apunta ahora al comentario padre
y `reply_to_comment_id` conserva el mismo destinatario para compatibilidad y
auditoría. Las respuestas antiguas siguen siendo válidas. Se reutilizan las rutas
de publicación actuales y la moderación existente.

Cada `ForumPost` puede llevar una imagen JPG, PNG o WebP. Se valida, normaliza a
WebP y comprime automáticamente por debajo de 800 KB; no se aceptan archivos que
no sean imágenes ni GIF animados, para no degradarlos de forma engañosa.

El componente `community.author-card` se comparte entre catálogo y comunidad.
El selector de reacciones reutiliza la relación polimórfica existente, con enlaces
`#comment-ID` para sus notificaciones. Las fichas respetan privacidad y bloqueos;
las reacciones rechazan comentarios ocultos y contenido no publicado.

## Despliegue

Aplicar la migración antes de servir la nueva versión de PHP:

```sh
php artisan migrate --path=database/migrations/2026_09_05_000001_add_reply_targets_to_community_messages.php --force
php artisan migrate --path=database/migrations/2026_09_05_000002_add_image_path_to_forum_posts.php --force
```

Solo añade referencias opcionales y una ruta de imagen opcional. No cambia textos,
fechas, usuarios ni la estructura anterior de comentarios.
Puede ejecutarse mientras la versión anterior sigue activa. El rollback elimina
las referencias nuevas, no los mensajes; se pierde únicamente el contexto nuevo
de a quién respondía cada mensaje. Revertir primero el código antes de quitar
esas columnas.
