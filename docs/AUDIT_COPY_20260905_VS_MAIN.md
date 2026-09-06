# Comparación de copy-20260905 con main

Fecha: 5 de septiembre de 2026. Base: `copy-20260905` (`df7aafa`). Versión revisada: `main` (`eb9d4de`).

**Conclusión:** conservar la comunidad y estabilizarla. La mayoría de las incorporaciones sí tiene consumidores. Los problemas más importantes son inconsistencias entre preguntas y foros, autorización incompleta y consultas sin límites. Eliminar archivos por cantidad no resolvería esos problemas.

**Alcance y evidencia**

- Comparación de las referencias locales; no se consultó producción ni se hizo fetch. La referencia local `origin/main` también apunta a `eb9d4de`.
- `copy-20260905` es ancestro de `main`: 0 commits exclusivos de la copia y 50 exclusivos de main.
- Diferencia: 223 archivos, 16.432 líneas añadidas y 423 eliminadas. De las añadidas, 3.216 son pruebas y 1.101 documentación.
- `composer.json`, `composer.lock`, `package.json` y `package-lock.json` no cambiaron entre estas ramas.
- Suite existente: **212 pruebas correctas, 1.302 aserciones**. También pasó la comprobación sintáctica de `public/assets/js/forum.js`.
- Siete escenarios adicionales reproducen siete defectos o inconsistencias. Se ejecutaron con SQLite en memoria, fuera de la suite del repositorio. No se modificó código de aplicación ni se hicieron migraciones contra la base de desarrollo o producción.
- «Sin uso» significa sin consumidores internos encontrados en código/rutas/vistas; no demuestra ausencia de integraciones externas ni de visitas reales. No hubo mediciones de carga ni validación visual en navegador.

**Fallos que corregir primero**

1. **P1 — Los bloqueos no se respetan al reaccionar a publicaciones comunitarias.** `app/Http/Controllers/CommunityReactionController.php:48` retorna después de comprobar las policies de temas y mensajes; esas policies no revisan `cannotInteractWith`. La comprobación de bloqueo solo se aplica al caso de comentarios del catálogo. Reproducción: A bloquea a B; B reacciona al mensaje de A; se guarda la reacción y A recibe una notificación. Aplicar una regla común de interacción a temas, respuestas y comentarios, tanto en autorización como en las notificaciones pertinentes.

2. **P1 — Se admiten respuestas en foros desactivados.** `app/Policies/ForumThreadPolicy.php:28` revisa cierres, ocultación y rol, pero omite `forum.is_active` y la actividad de su categoría. Reproducción: crear tema, desactivar foro y enviar POST de respuesta con su URL conocida; el mensaje se persiste. La lectura y la creación de temas sí comprueban actividad, por lo que la escritura queda inconsistente. Unificar las condiciones de disponibilidad pública y de escritura.

3. **P2 — Eliminar una pregunta produce un 500 después de borrarla.** `app/Http/Controllers/ForumPostController.php:63` elimina el mensaje inicial y el tema, y después redirige a `forums.show` con un foro nulo. Las preguntas independientes tienen `forum_id = null`. Reproducido mediante la misma ruta que usa el botón Eliminar: la pregunta queda borrada y la respuesta HTTP falla por falta del parámetro `forum`. Elegir `questions.index` para preguntas y revisar también `ForumThreadController::destroy`, que hace la misma suposición.

4. **P2 — No se puede ocultar un mensaje cuyo autor eliminó su cuenta.** `app/Services/ForumPostService.php:65` llama a `synchronizeUser(User $user)` con un autor nulo. Las claves foráneas permiten conservar mensajes sin autor. Reproducción: crear mensaje, eliminar cuenta y ocultarlo como moderador; se produce TypeError y se revierte la transacción. Condicionar el recuento a la existencia del autor, como ya hace el flujo de eliminación.

5. **P2 — Repetir el título de un tema borrado produce un 500.** `app/Services/ForumThreadService.php:57` busca slugs mediante una consulta que excluye soft deletes, aunque la restricción UNIQUE de la base sigue incluyendo esas filas. Reproducido creando, borrando y volviendo a crear «Mismo título». Considerar `withTrashed()` al reservar slugs y resolver también posibles colisiones concurrentes con la restricción de base de datos.

6. **P2 — Los enlaces a respuestas posteriores a las primeras 100 no llegan al mensaje.** `app/Http/Controllers/ForumThreadController.php:76` carga el mensaje inicial y 100 respuestas; `QuestionController::show` usa la misma estrategia. Sin embargo, los enlaces de actividad, reacciones y redirecciones usan `#post-ID` sin resolver dónde se carga ese mensaje. El fragmento no llega al servidor y el post puede no existir en el HTML. Reproducido con 101 respuestas. El enlace `?all=1` permite encontrarlo manualmente, pero carga toda la conversación. Resolver el destino mediante una página/cursor o un parámetro de mensaje, conservando el contexto del árbol.

7. **P2 — El filtro de rangos puede contradecir el rango mostrado.** `CommunityRankResolver.php:20` cae al rango automático cuando el especial asignado está inactivo. `CommunityController.php:205` solo considera automáticos a quienes tienen `community_rank_id` nulo. Reproducción: asignar un rango especial, desactivarlo y filtrar por el rango automático que ahora muestra el perfil; el miembro desaparece de los resultados. Compartir la definición de rango efectivo entre presentación y filtrado.

**Qué sobra o quedó a medio integrar**

| Pieza | Evidencia | Acción sugerida |
|---|---|---|
| `app/Models/CommunityBadge.php` | Alias de `Badge`, marcado deprecated, sin consumidores internos encontrados. | Retirar si no hay integraciones externas que aún importen ese nombre. |
| `app/Services/ForumMentionService.php` | Alias de `MentionService`, marcado deprecated, sin consumidores internos encontrados. | Mismo criterio; no eliminar `MentionService`. |
| `QuestionTag`, `ForumThread::questionTags()`, `question_tags` y `forum_thread_question_tag` | Se referencian entre sí y desde la migración; no encontré un flujo que cree, asigne, filtre o muestre etiquetas. | Funcionalidad incompleta, candidata a retirar si no se va a desarrollar. Comprobar datos antes de eliminar tablas y hacerlo mediante una migración nueva si ya se desplegaron. |
| `comments.reply_to_comment_id` y `Comment::replyTo()` | Al crear respuestas se copia el mismo ID a `parent_id` y `reply_to_comment_id`; el árbol y la presentación se basan en `parent_id`. No encontré lectores funcionales de la segunda relación. | Elegir una sola relación mientras ambas signifiquen lo mismo; planificar la retirada del campo redundante. |
| `previousUserId` en `components/forum/post.blade.php` | Se declara y se propaga desde `thread-post`, pero `post` no lo utiliza. | Retirar la propagación inútil o volver a conectar su comportamiento. El equivalente del catálogo sí tiene uso. |
| Siete clases CSS antiguas | Sin referencias encontradas fuera de sus reglas CSS: `forum-thread-list`, `forum-thread-row`, `forum-thread-status`, `forum-post-author-meta`, `forum-post-actions`, `forum-forum-header-compact`, `forum-thread-header-compact`. | Candidatas a limpieza con revisión visual. En reglas con varios selectores, conservar los selectores que sí se usan. |

No confundir ausencia de referencias directas con código muerto: `UserPolicy` se descubre por convención y los comandos de importación/recuento se registran en Artisan. Los votos de preguntas sí se usan: los botones «Me ayudó» llaman a sus rutas. Los componentes nuevos de comunidad y foro tienen consumidores internos encontrados.

**Mejoras prioritarias para crecer**

- **Paginar el directorio desde SQL.** `CommunityController.php:130` obtiene todos los perfiles históricos y todos los usuarios modernos, carga sus insignias, concatena y ordena en PHP; `community/index.blade.php` los renderiza todos. El coste crece con el total de miembros aunque solo se necesite una página. Construir una consulta común de IDs/tipo/orden mediante UNION o equivalente, paginarla y cargar las relaciones únicamente para la página visible. Mantener un desempate estable.
- **Limitar las conversaciones.** Sustituir `?all=1` por carga incremental que preserve padres y destinos. Los comentarios del catálogo ya se cargaban sin paginar antes; la comunidad añade árboles y más relaciones a ese coste. Separar esa deuda heredada del problema nuevo de enlaces después de 100 respuestas.
- **Agregar reacciones en la base.** `CommunityReactionService.php:66` evita una consulta por mensaje, pero recupera todas las filas de reacciones de todos los mensajes visibles y las cuenta en PHP. Usar GROUP BY por destino/tipo y una consulta acotada para la reacción del visitante. Una sola consulta no garantiza poco volumen de datos.
- **Sacar el reparto de notificaciones de la transacción de publicación.** `ForumPostService.php:100` carga todos los suscriptores y escribe notificaciones sincrónicamente; además consulta bloqueos por destinatario. Usar un trabajo después del commit, procesamiento por lotes e idempotencia. Medir tiempos de publicación antes y después.
- **Revisar contadores bajo concurrencia.** Actualmente se recalculan COUNT/MAX por tema y COUNT por autor al publicar. El recuento sigue siendo útil para reparación; separar ese proceso del camino frecuente cuando las mediciones justifiquen contadores incrementales o diferidos. No se ejecutó una prueba de concurrencia y no se afirma una pérdida de contadores reproducida.
- **Definir un layout Blade compartido para el portal.** Las nuevas páginas repiten documento HTML, fuentes, Bootstrap, CSS, navbar y footer. Concentrar cargas comunes y permitir scripts por página reduce cambios repetidos y divergencias.
- **Ordenar el CSS por componentes.** `style.css` pasa de 3.143 a 4.805 líneas y de 72.148 a 146.024 bytes. Las reglas de diseños anteriores y las sobreescrituras dificultan el mantenimiento. Limpiar por componente y verificar móvil/escritorio; dividir el archivo por sí solo no reduce lo descargado.

**Peso heredado de la copia**

`resources/views/metronic` conserva 185 archivos de referencia, aproximadamente 66 MB, sin referencias de vistas encontradas en la aplicación. Esa carpeta no cambió entre las ramas: no es peso introducido por la comunidad. Es candidata a salir del árbol de ejecución hacia un archivo de referencias si todavía se necesita.

`public/metronic` ocupa aproximadamente 75 MB y tampoco cambió en este intervalo, pero el panel utiliza sus assets. No procede borrar esa carpeta completa: inventariar qué bundles e imágenes carga el panel y retirar únicamente los sobrantes. Tamaño en disco no equivale a tamaño descargado por cada página.

`docs/COMMUNITY_ARCHITECTURE.md` describe una propuesta sobre `df7aafa`, no el estado actual. Conservarlo como antecedente y distinguirlo de la arquitectura implementada; sus entidades propuestas no son una razón para crear más tablas automáticamente.

**Orden de ejecución recomendado**

1. Corregir bloqueos, escritura en espacios desactivados y los tres errores 500; añadir regresiones de esos escenarios a la suite normal.
2. Resolver enlaces a mensajes, paginación del directorio y coherencia de rangos; cubrir páginas sucesivas y cambios administrativos de rango.
3. Retirar aliases, parámetros y CSS sin uso; decidir el futuro de etiquetas y del segundo campo de respuesta. Evitar reescribir migraciones ya aplicadas.
4. Consolidar layouts y reglas de autorización. Medir consultas, memoria y tiempos para priorizar agregaciones y trabajos en cola.

Mantendría Laravel/Blade y la separación existente de modelos, servicios, requests y policies. El uso de `ForumThread` para discusiones y preguntas permite reutilizar respuestas, reacciones y moderación; hay que corregir las suposiciones de foro obligatorio en sus consumidores. No se encontró evidencia que justifique una reescritura o introducir microservicios.

**Reproducciones de esta auditoría**

Archivo temporal: `/tmp/CommunityAuditRegressionTest.php`. Log: `/tmp/mundoyuri-audit-regressions.log`. Ejecutado desde la raíz mediante:

```sh
vendor/bin/phpunit --configuration phpunit.xml --colors=never --no-progress /tmp/CommunityAuditRegressionTest.php
```

Resultado: 7 escenarios, 14 aserciones ejecutadas y 7 fallos esperados al exigir el comportamiento corregido. Son reproducciones diagnósticas externas, no fallos de las 212 pruebas existentes. Algunas aserciones intermedias verifican expresamente el efecto incorrecto actual —reacción/notificación, escritura y borrado persistidos— para confirmar la causa; al convertirlas en regresiones permanentes hay que expresar únicamente el comportamiento deseado.
