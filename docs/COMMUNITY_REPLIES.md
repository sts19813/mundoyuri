# Respuestas dirigidas y comentarios del catálogo

Foros y preguntas conservan `ForumPost` y sus contadores. `reply_to_post_id`
identifica el mensaje al que se responde, dentro de la misma conversación.
La lista conserva su paginación; el contexto muestra el destinatario y un extracto
escapado. No se genera un árbol de sangrías ilimitadas. Los mensajes ocultos o
eliminados no se reproducen en el contexto.

En comentarios de episodios y series, `parent_id` sigue apuntando al comentario
principal. `reply_to_comment_id` conserva el destinatario concreto, incluso cuando
se responde a otra respuesta. Las respuestas antiguas siguen siendo válidas.
Se reutilizan las rutas de publicación actuales y la moderación existente.

El componente `community.author-card` se comparte entre catálogo y comunidad.
El selector de reacciones reutiliza la relación polimórfica existente, con enlaces
`#comment-ID` para sus notificaciones. Las fichas respetan privacidad y bloqueos;
las reacciones rechazan comentarios ocultos y contenido no publicado.

## Despliegue

Aplicar la migración antes de servir la nueva versión de PHP:

```sh
php artisan migrate --path=database/migrations/2026_09_05_000001_add_reply_targets_to_community_messages.php --force
```

Solo añade dos claves foráneas opcionales, indexadas y con `ON DELETE SET NULL`.
No cambia textos, fechas, usuarios ni la estructura anterior de comentarios.
Puede ejecutarse mientras la versión anterior sigue activa. El rollback elimina
las referencias nuevas, no los mensajes; se pierde únicamente el contexto nuevo
de a quién respondía cada mensaje. Revertir primero el código antes de quitar
esas columnas.
