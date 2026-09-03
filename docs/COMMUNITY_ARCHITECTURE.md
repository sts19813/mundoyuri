# Arquitectura comunitaria de Mundo Yuri

> Auditoría y plan técnico. No implementa funcionalidades ni modifica el comportamiento actual.

**Fecha de auditoría:** 3 de septiembre de 2026

**Código auditado:** rama `main`, commit `df7aafa`

**Entorno inspeccionado:** Laravel 13.6.0, PHP 8.5.7, MySQL, Spatie Laravel Permission 7.4.1

**Objetivo:** evolucionar el catálogo actual hacia una comunidad inspirada en el foro Mundo Yuri de 2007, conservando la arquitectura funcional y el diseño visual existente.

## 1. Estado actual

El proyecto es una aplicación Laravel monolítica, renderizada principalmente con Blade y orientada al catálogo de contenido. La comunidad ya no parte de cero: `User` es el centro de perfiles, relaciones sociales, favoritos, mensajes y notificaciones.

El repositorio contiene:

- 13 modelos en `app/Models`.
- 27 migraciones, todas aplicadas en el entorno local inspeccionado.
- 38 controladores, incluidos 14 bajo `Admin` y 10 de autenticación.
- 127 rutas de aplicación.
- 2 middleware propios (`admin` y `admin.panel`).
- 2 Form Requests.
- 2 notificaciones de base de datos.
- 3 servicios, además de clases de soporte para medios y video.
- 301 archivos bajo `resources/views`; 185 están bajo `resources/views/metronic` como páginas HTML de referencia sin conexión directa con las rutas actuales.
- 29 archivos de prueba.

La suite de pruebas previa a cualquier cambio arroja **99 pruebas correctas y 1 fallo existente**. El fallo está en `PanelPermissionsTest::test_regular_user_submission_is_always_pending_even_with_forged_moderation_fields`: el test no envía `catalog_section`, aunque el controlador ya lo exige. Esta discrepancia debe corregirse antes de usar la suite como puerta de despliegue, pero queda fuera de esta auditoría documental.

### 1.1 Modelos actuales

| Modelo | Responsabilidad y relaciones relevantes |
|---|---|
| `User` | Autenticación, roles Spatie, perfil, favoritos, seguidores, bloqueos, conversaciones, mensajes, comentarios y notificaciones. |
| `Comment` | Comentario polimórfico sobre `Series` o `Episode`; autor opcional y respuesta de un solo nivel mediante `parent_id`. |
| `Conversation` | Conversación única entre dos usuarios, ordenados por ID, con `last_message_at`. |
| `DirectMessage` | Mensaje privado con remitente, destinatario, conversación y `read_at`. |
| `Series` | Contenido catalogado; género, creador, aprobador, episodios, comentarios y usuarios que la marcaron favorita. |
| `Episode` | Episodio moderable; fuentes, comentarios, contador de vistas y notificación por correo. |
| `Genre` | Clasificación del catálogo. |
| `EpisodeSource` | Fuente de reproducción normalizada. |
| `EpisodeEmailNotification` | Bitácora/idempotencia del correo por episodio y destinatario. |
| `CatalogSection` | Secciones públicas configurables y contenido del hero. |
| `AssistantMessage` | Mensajes, solicitudes y reportes enviados mediante Miyu. No es el futuro sistema formal de reportes comunitarios. |
| `AssistantSetting` | Configuración singleton de Miyu. |
| `BackblazeB2Setting` | Configuración cifrada de Backblaze B2. |

### 1.2 Base de datos actual

Las tablas funcionales son:

- Infraestructura Laravel: `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `notifications`.
- Spatie: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`.
- Catálogo: `genres`, `series`, `episodes`, `episode_sources`, `catalog_sections`, `comments`, `series_favorites`, `episode_email_notifications`.
- Comunidad: `user_follows`, `user_blocks`, `conversations`, `direct_messages`.
- Operación: `assistant_messages`, `assistant_settings`, `backblaze_b2_settings`.

El esquema ya usa claves foráneas, índices compuestos y restricciones únicas útiles. Destacan:

- `users.email`, `users.alias` y `users.google_id` son únicos.
- `series.slug`, `episodes.slug`, `genres.slug` y `catalog_sections.slug` son únicos.
- Favoritos, follows y bloqueos tienen clave primaria compuesta, por lo que las operaciones son idempotentes.
- Una pareja ordenada de usuarios solo puede tener una conversación.
- `direct_messages` está indexada por conversación/fecha y destinatario/lectura.
- `notifications` usa el esquema estándar de notificaciones de Laravel.

### 1.3 Backend actual

- Las rutas están concentradas en `routes/web.php`; Breeze conserva las rutas de autenticación en `routes/auth.php`.
- La aplicación es server-rendered y no tiene una API pública ni una capa SPA.
- La validación se realiza mayormente dentro de controladores. Solo login y edición del perfil público tienen Form Requests propios.
- No existe ningún archivo en `app/Policies`; la autorización se reparte entre middleware, permisos `can:*`, condiciones de controlador y métodos de `User`.
- `AppServiceProvider` registra los listeners de login/registro y un `Gate::before` que concede todo al administrador.
- Los servicios actuales separan correctamente OAuth, Backblaze y envíos de disponibilidad de episodios.

### 1.4 Autenticación

- Laravel Breeze con sesión web y proveedor Eloquent de `User`.
- Registro por correo/contraseña y acceso mediante Google OAuth.
- Recuperación de contraseña y rutas de verificación de correo.
- `LoginRequest` aplica rate limiting de cinco intentos y rechaza usuarios con `is_active = false`.
- `RecordUserLogin` actualiza `last_login_at` para accesos normales y Google.
- Los administradores/moderadores entran al panel; usuarios normales regresan a la URL pública prevista.
- `User` no implementa actualmente `MustVerifyEmail`, aunque las rutas, campos y middleware `verified` existen. En consecuencia, no debe asumirse que `verified` exige verificación hasta corregir y probar ese contrato explícitamente.

### 1.5 Roles, permisos y administración

Spatie Laravel Permission está instalado y activo. Existen roles `admin`, `moderator` y `user`, junto con permisos de dashboard y CRUD/moderación de catálogo.

Hay una compatibilidad doble que debe preservarse durante la evolución:

- `users.role`, columna histórica de texto.
- Roles reales de Spatie mediante `model_has_roles`.

`isAdmin()` y `shouldEnterAdminPanel()` aceptan cualquiera de las dos fuentes. El seeder intenta sincronizarlas. Esta duplicidad funciona, pero puede divergir si algún flujo actualiza solo una fuente.

El panel usa Metronic/Bootstrap y ya incluye:

- Dashboard y métricas.
- Gestión de usuarios, roles y permisos.
- CRUD de géneros, series, episodios y secciones.
- Flujo de aprobación/rechazo de series y episodios.
- Mensajes y configuración de Miyu.
- Configuración de Backblaze B2.

### 1.6 Frontend actual

Hay dos superficies visuales que deben mantenerse:

1. **Portal público:** Bootstrap 5 por CDN, fuentes Playfair Display y DM Sans, y CSS propio en `public/assets/css/style.css` y `episodios.css`. Su identidad usa fondo oscuro y tokens rosa/morado (`--rose`, `--dark`, `--dark-card`, `--dark-surface`, `--muted`, `--text`).
2. **Panel administrativo:** Metronic y sus bundles locales bajo `public/metronic`.

Los nuevos módulos comunitarios deben continuar con Blade y Bootstrap, usar `x-navbar`, `x-footer`, `x-portal-favicon`, `x-seo` y extender los tokens existentes. No se recomienda introducir React/Vue ni sustituir los layouts.

`resources/css/app.css` solo contiene las directivas Tailwind, y `resources/js/app.js` solo inicia Alpine. El portal activo depende principalmente del CSS público y de JavaScript embebido en componentes/vistas. Las páginas HTML de `resources/views/metronic` son material de referencia, no funcionalidad conectada.

## 2. Funcionalidades que ya existen

| Funcionalidad | Estado actual |
|---|---|
| Autenticación | Correo/contraseña y Google OAuth. |
| Perfiles públicos | `/usuarios/{user}/{alias?}`, visibles para usuarios activos. |
| Edición de perfil | Nombre, alias, correo, avatar, portada y biografía. |
| Favoritos | Series publicadas; alta/baja idempotente y listado público. |
| Seguidores/seguidos | Alta/baja, contadores, listados y notificación al seguido. |
| Bloqueos | Impiden follows y mensajes en ambas direcciones; al bloquear se eliminan follows mutuos. |
| Mensajes privados | Una conversación por pareja, paginación, lectura y contador de no leídos. |
| Notificaciones | Canal `database`, listado, apertura segura y marcar todas como leídas. |
| Comentarios | Series/episodios, invitados o usuarios, respuestas de un nivel, publicación inmediata. |
| Moderación | Series/episodios pendientes, aprobados o rechazados. |
| Roles/permisos | Spatie, administración y permisos por acción de catálogo. |
| Panel administrativo | Metronic con navegación basada parcialmente en permisos. |
| Cola | Driver de base de datos configurado; en pruebas se usa `sync`. |
| Pruebas | Cobertura significativa de auth, perfil, follows, favoritos, bloqueos, mensajes y notificaciones. |

## 3. Qué debemos reutilizar

1. **`User` autenticado y sus relaciones actuales.** No crear un segundo sistema de cuentas.
2. **Spatie Permission** para capacidades administrativas y de moderación. Los rangos comunitarios no deben convertirse en roles Spatie.
3. **Notificaciones de Laravel** y la tabla `notifications`; agregar nuevos tipos con el mismo contrato `kind/title/message/actor/url`.
4. **Bloqueos actuales** como regla transversal para follows, mensajes, menciones, reacciones y visibilidad de actividad.
5. **Seguidores** para futuras notificaciones o feeds, sin convertirlos en suscripciones a temas.
6. **Estilo y componentes del portal**: navbar, footer, avatares, tarjetas, botones, alertas, paginación y variables CSS.
7. **Metronic** para todas las pantallas administrativas nuevas.
8. **Patrones de transacción e idempotencia** presentes en conversaciones, mensajes y notificaciones de episodios.
9. **`Comment` como comentarios del catálogo.** No convertirlo en posts de foro ni cambiar su tabla en la primera etapa.
10. **Tests con `RefreshDatabase` y SQLite** como base para pruebas de cada módulo, añadiendo pruebas específicas para MySQL donde existan diferencias de índices o DDL.

## 4. Qué falta

- Directorio público buscable y paginado de miembros.
- Privacidad de perfil y privacidad granular de secciones.
- Perfil comunitario con rango, insignias, firma, actividad y estadísticas del foro.
- Rangos automáticos y especiales administrables.
- Archivo de perfiles históricos, importación, insignia y reclamación.
- Categorías, foros, temas, posts y preguntas/respuestas aceptadas.
- Reacciones/likes reales; el botón visual de like en CSS de comentarios no tiene backend.
- Parser y persistencia de menciones `@alias`.
- Suscripciones a temas y notificaciones comunitarias.
- Actividad reciente persistida y filtrada por privacidad/bloqueo.
- Reportes formales, cola de moderación, sanciones y auditoría.
- Policies y Form Requests para centralizar reglas.
- Rate limits para comentarios y futuras escrituras comunitarias.
- Soft deletes y estrategia de preservación para contenido comunitario.
- Contador consistente de mensajes/posts y recálculo verificable.

## 5. Modelo de datos propuesto

### 5.1 Decisiones principales

- Mantener `users` como cuentas autenticables actuales.
- Guardar perfiles históricos en una tabla separada `historical_profiles`. Es la opción más segura: evita volver nulos `users.email` y `users.password`, no altera los flujos de Breeze/Google y permite perfiles sin credenciales.
- Mantener `comments` para el catálogo y crear `forum_topics`/`forum_posts` para foros. Sus ciclos de vida y necesidades de moderación son distintos.
- Modelar preguntas como `forum_topics.type = question`; una respuesta aceptada apunta a un `forum_post` del mismo tema.
- Incluir el primer mensaje de un tema en `forum_posts`. `forum_topics` conserva metadatos; `replies_count` excluye ese primer post.
- Mantener rangos, insignias y roles como conceptos diferentes:
  - rol/permiso: capacidad de seguridad;
  - rango: progreso o título comunitario;
  - insignia: reconocimiento visible.
- Guardar contenido de foro como texto plano en la primera versión y renderizarlo escapado, preservando saltos de línea. No aceptar HTML del usuario. Markdown puede añadirse después con parser y sanitizador explícitos.
- Usar contadores cacheados para listados rápidos, con comandos de reconciliación idempotentes.

### 5.2 Entidades y flujo

```text
User ──< ForumTopic ──< ForumPost
  │          │              │
  │          ├── accepted answer
  │          └── subscriptions
  ├──< Reactions >── forum topic/post (polimórfico)
  ├──< Mentions  >── forum post (polimórfico y extensible)
  ├──< Reports   >── contenido reportable (polimórfico)
  ├──< RankAssignments >── CommunityRank
  ├──< UserBadges >── CommunityBadge
  └──< HistoricalProfile (claim aprobado)

ForumCategory ──< Forum ──< ForumTopic
ModerationAction ──> sujeto polimórfico
UserSanction ──> usuario sancionado
CommunityActivity ──> actor + sujeto polimórfico
```

## 6. Nuevas tablas necesarias

Los nombres son propuestas concretas y deben mantenerse consistentes en código, pruebas y rutas.

### 6.1 Identidad, rangos e historia

#### `community_ranks`

- `id`
- `name`, `slug` único
- `kind`: `automatic` o `special`
- `min_posts` nullable; solo para rangos automáticos
- `priority` para resolver cuál se muestra primero
- `color`, `icon`, `image_path` nullable
- `description` nullable
- `is_active`, `sort_order`
- timestamps

Debe sembrar, sin umbrales rígidos en código: Nuevo miembro, Kohai, Yuri Fan, Yuri Senpai y Onee-sama. Los umbrales se definen y editan desde administración.

#### `community_rank_assignments`

- `id`
- `user_id`
- `community_rank_id`
- `awarded_by` nullable
- `reason` nullable
- `starts_at`, `expires_at` nullable
- `is_featured`
- timestamps
- unique `user_id + community_rank_id`

Los rangos automáticos se calculan por `forum_posts_count`; la tabla de asignaciones se usa para rangos especiales. Así no se escribe una fila por cada cambio automático.

#### `community_badges`

- `id`, `name`, `slug` único
- `description`, `icon`, `image_path`, `color` nullable
- `is_active`, `sort_order`
- timestamps

#### `community_badge_user`

- `user_id`, `community_badge_id`
- `awarded_by` nullable
- `reason` nullable
- `awarded_at`
- primary `user_id + community_badge_id`

La insignia “Miembro histórico” se entrega al usuario cuando una reclamación es aprobada. El perfil histórico sin reclamar muestra su condición histórica directamente, sin necesitar una cuenta.

#### `historical_profiles`

- `id`
- `source` y `source_user_id` para trazabilidad del foro de 2007
- `slug` único
- `legacy_alias`, `display_name`
- `biography`, `signature_archive`, `avatar_path` nullable
- `historical_joined_at` nullable
- `legacy_posts_count` default 0
- `legacy_rank` nullable
- `snapshot` JSON nullable para datos no normalizados del origen
- `claimed_by_user_id` nullable
- `claimed_at` nullable
- `is_published` default true
- timestamps
- unique `source + source_user_id`

No guardar correos históricos salvo necesidad probada. Si fueran necesarios como evidencia, deben cifrarse o conservarse únicamente como hash normalizado.

#### `historical_profile_claims`

- `id`
- `historical_profile_id`, `claimant_user_id`
- `status`: `pending`, `approved`, `rejected`, `cancelled`
- `evidence` text nullable
- `reviewed_by` nullable
- `review_notes` nullable
- `reviewed_at` nullable
- timestamps

Una restricción de negocio impide más de una reclamación activa por perfil y más de una aprobación. La aprobación se ejecuta dentro de una transacción y bloquea la fila histórica.

### 6.2 Foros y preguntas/respuestas

#### `forum_categories`

- `id`, `name`, `slug` único
- `description` nullable
- `sort_order`, `is_active`
- timestamps

#### `forums`

- `id`, `forum_category_id`
- `name`, `slug` único, `description` nullable
- `visibility`: `public`, `members`, `staff`
- `posting_mode`: `members`, `trusted`, `staff`, `closed`
- `sort_order`, `is_active`
- `topics_count`, `posts_count`
- `last_posted_at` nullable
- timestamps

#### `forum_topics`

- `id`, `forum_id`
- `author_user_id` nullable
- `author_historical_profile_id` nullable para una futura importación del foro antiguo
- `author_name_snapshot`
- `title`, `slug`
- `type`: `discussion` o `question`
- `status`: `visible`, `pending`, `hidden`, `locked`, `archived`
- `is_pinned`, `is_featured`
- `views_count`, `replies_count`, `reactions_count`
- `accepted_answer_post_id` nullable, agregado después de crear `forum_posts`
- `last_posted_at` nullable
- timestamps y soft deletes
- unique `forum_id + slug`

#### `forum_posts`

- `id`, `forum_topic_id`
- `author_user_id` nullable
- `author_historical_profile_id` nullable
- `author_name_snapshot`
- `parent_id` nullable, reservado para contexto/citas, no para crear árboles ilimitados
- `body`
- `status`: `visible`, `pending`, `hidden`
- `show_signature` default true
- `reactions_count` default 0
- `edited_at`, `edited_by` nullable
- timestamps y soft deletes

Para contenido nuevo, `author_user_id` es obligatorio a nivel de aplicación. Para importaciones, se permite autor histórico. Debe existir exactamente uno de los dos autores; MySQL 8 puede reforzarlo con `CHECK`, pero la aplicación también debe validarlo por compatibilidad.

#### `forum_topic_subscriptions`

- `user_id`, `forum_topic_id`
- `notification_level`: `all` o `mentions`
- `last_read_post_id` nullable
- timestamps
- primary `user_id + forum_topic_id`

### 6.3 Interacción, actividad y moderación

#### `reactions`

- `id`, `user_id`
- `reactable_type`, `reactable_id`
- `type` (`like` inicialmente; extensible)
- timestamps
- unique `user_id + reactable_type + reactable_id + type`

La relación polimórfica permite añadir reacciones a posts, temas y más adelante comentarios sin modificar `comments` ahora.

#### `mentions`

- `id`
- `mentioned_user_id`, `mentioner_user_id`
- `mentionable_type`, `mentionable_id`
- timestamps
- unique `mentioned_user_id + mentionable_type + mentionable_id`

#### `community_activities`

- `id`, `actor_user_id` nullable
- `verb`
- `subject_type`, `subject_id`
- `context` JSON nullable
- `visibility`: `public`, `members`, `private`, `staff`
- `created_at`

Es un registro derivado para feeds, no la fuente de verdad. Se puede reconstruir desde temas, posts y reacciones.

#### `reports`

- `id`, `reporter_user_id`
- `reportable_type`, `reportable_id`
- `reason_code`, `details` nullable
- `status`: `open`, `reviewing`, `resolved`, `dismissed`
- `assigned_to` nullable
- `resolution_notes`, `resolved_at` nullable
- timestamps

#### `moderation_actions`

- `id`, `moderator_user_id`
- `subject_type`, `subject_id`
- `action`
- `from_status`, `to_status`, `reason` nullable
- `metadata` JSON nullable
- `created_at`

Esta tabla es una bitácora inmutable; no debe editarse desde el panel.

#### `user_sanctions`

- `id`, `user_id`, `issued_by`
- `type`: `warning`, `mute`, `suspension`, `forum_ban`
- `reason`
- `starts_at`, `expires_at` nullable
- `revoked_at`, `revoked_by`, `revocation_reason` nullable
- timestamps

## 7. Columnas nuevas necesarias en `users`

Todas son aditivas; no se propone volver nullable `email` ni `password`.

| Columna | Tipo propuesto | Uso |
|---|---|---|
| `profile_visibility` | string(20), default `public`, index | `public`, `members` o `private`. |
| `show_followers` | boolean, default true | Privacidad granular. |
| `show_following` | boolean, default true | Privacidad granular. |
| `show_favorites` | boolean, default true | Privacidad granular. |
| `show_activity` | boolean, default true | Privacidad granular. |
| `show_last_seen` | boolean, default false | Evita exponer actividad por defecto. |
| `signature` | text nullable | Firma escapada, con límite estricto. |
| `signature_enabled` | boolean, default true | Permite ocultarla sin borrar contenido. |
| `forum_posts_count` | unsigned bigint, default 0, index | Contador visible y base de rango automático. |
| `last_activity_at` | timestamp nullable, index | Actividad comunitaria; distinta de login. |

No se recomienda agregar `historical_joined_at` a `users`: la fecha histórica pertenece a `historical_profiles.historical_joined_at` y se muestra a través de la reclamación aprobada. Esto mantiene `users.created_at` intacto y semánticamente correcto.

## 8. Relaciones Eloquent

### `User`

- `forumTopics(): HasMany`
- `forumPosts(): HasMany`
- `topicSubscriptions(): BelongsToMany`
- `reactions(): HasMany`
- `mentionsReceived(): HasMany`
- `mentionsMade(): HasMany`
- `communityActivities(): HasMany`
- `reportsSubmitted(): HasMany`
- `moderationActions(): HasMany`
- `sanctions(): HasMany`
- `specialRanks(): BelongsToMany`
- `badges(): BelongsToMany`
- `claimedHistoricalProfiles(): HasMany`

### Foros

- `ForumCategory hasMany Forums`.
- `Forum belongsTo ForumCategory`, `hasMany ForumTopics`.
- `ForumTopic belongsTo Forum`, `belongsTo User as author`, `belongsTo HistoricalProfile as historicalAuthor`, `hasMany ForumPosts`, `belongsTo ForumPost as acceptedAnswer`, `belongsToMany User as subscribers`, `morphMany Reactions/Reports/Activities`.
- `ForumPost belongsTo ForumTopic`, autores alternativos, `belongsTo parent`, `hasMany contextualReplies`, `morphMany Reactions/Mentions/Reports/Activities`.

### Historia, rangos y moderación

- `HistoricalProfile belongsTo User as claimedBy`; `hasMany HistoricalProfileClaims`.
- `HistoricalProfileClaim belongsTo HistoricalProfile`, `claimant`, `reviewer`.
- `CommunityRank belongsToMany User` para asignaciones especiales.
- `CommunityBadge belongsToMany User`.
- `Reaction morphTo reactable`.
- `Mention morphTo mentionable`.
- `Report morphTo reportable`.
- `ModerationAction morphTo subject`.
- `CommunityActivity morphTo subject`.

Los modelos deben ofrecer scopes como `visibleTo($viewer)`, `published()`, `active()`, `automatic()` y `special()`, pero la decisión final de acceso debe quedar en Policies/servicios, no solo en Blade.

## 9. Controllers necesarios

### Portal

- `MemberDirectoryController@index` — búsqueda, filtros, orden y paginación.
- `CommunityProfileController@update` — firma y privacidad, separado del endpoint de perfil actual para reducir regresiones.
- `HistoricalProfileController@show` — perfil histórico publicado.
- `HistoricalProfileClaimController@store` — solicitud autenticada de reclamación.
- `ForumController@index|show` — portada de foros y temas por foro.
- `ForumTopicController@create|store|show|edit|update|destroy`.
- `ForumPostController@store|edit|update|destroy`.
- `ForumAnswerController@store|destroy` — aceptar/quitar respuesta aceptada.
- `ForumSubscriptionController@store|destroy`.
- `ReactionController@store|destroy`.
- `CommunityActivityController@index`.
- `ReportController@store`.

### Administración

- `Admin/ForumCategoryController`.
- `Admin/ForumController`.
- `Admin/CommunityRankController`.
- `Admin/CommunityBadgeController`.
- `Admin/HistoricalProfileController` para CRUD/importación y publicación.
- `Admin/HistoricalProfileClaimController` para aprobar/rechazar.
- `Admin/CommunityModerationController` para reportes y contenido pendiente.
- `Admin/UserSanctionController`.
- `Admin/ModerationActionController@index` como auditoría de solo lectura.

Los controladores deben ser delgados: validar con Form Requests, autorizar con Policies y delegar operaciones multi-modelo a servicios transaccionales.

## 10. Policies

Crear y registrar por convención:

- `UserPolicy`: `viewDirectory`, `viewProfile`, `viewFollowers`, `viewFollowing`, `viewFavorites`, `viewActivity`, `interact`, `message`.
- `HistoricalProfilePolicy`: `view`, `claim`, `manage`.
- `ForumCategoryPolicy`: `view`, `manage`.
- `ForumPolicy`: `view`, `createTopic`, `manage`.
- `ForumTopicPolicy`: `view`, `create`, `update`, `delete`, `lock`, `pin`, `acceptAnswer`, `moderate`.
- `ForumPostPolicy`: `view`, `create`, `update`, `delete`, `moderate`.
- `ReactionPolicy`: `create`, `delete`.
- `ReportPolicy`: `create`, `viewAny`, `update`.
- `UserSanctionPolicy`: `viewAny`, `create`, `revoke`.

Reglas transversales:

- `Gate::before` actual conserva acceso total de admin.
- Bloqueos se comprueban en `UserPolicy::interact` y en servicios, para evitar bypass desde rutas nuevas.
- Moderadores requieren permisos Spatie específicos.
- Propietarios editan solo contenido dentro de la ventana configurada, salvo staff.
- Un usuario sancionado puede leer según visibilidad, pero no publicar/reaccionar/mencionar durante una sanción activa aplicable.
- La privacidad siempre se aplica en consultas y controladores, no únicamente ocultando componentes.

## 11. Form Requests

### Perfil e historia

- `MemberDirectoryRequest`
- `UpdateCommunityProfileRequest`
- `StoreHistoricalProfileClaimRequest`
- `ImportHistoricalProfilesRequest`
- `ReviewHistoricalProfileClaimRequest`

### Foros

- `StoreForumTopicRequest`
- `UpdateForumTopicRequest`
- `StoreForumPostRequest`
- `UpdateForumPostRequest`
- `AcceptForumAnswerRequest`
- `StoreReactionRequest`
- `StoreReportRequest`
- `StoreUserSanctionRequest`

### Administración

- `Store/UpdateForumCategoryRequest`
- `Store/UpdateForumRequest`
- `Store/UpdateCommunityRankRequest`
- `Store/UpdateCommunityBadgeRequest`

Reglas importantes:

- Títulos de 5–180 caracteres; cuerpo de 2–20 000 caracteres, configurable.
- Firma de máximo 1 000 caracteres, texto plano, máximo de líneas y enlaces.
- Alias mencionado se normaliza sin modificar el alias almacenado.
- La respuesta aceptada debe pertenecer al tema y no ser el primer post.
- Los IDs polimórficos nunca se aceptan libremente sin mapa explícito de tipos permitidos.
- Los campos de estado/moderación se descartan para usuarios sin permisos, aunque sean enviados manualmente.

## 12. Rutas

Las rutas existentes no deben cambiar. Las nuevas se declaran **antes** de `/{sectionSlug}` y, para miembros, antes de `/usuarios/{user}/{alias?}` si comparten prefijo.

```php
// Públicas
GET  /miembros                                      members.index
GET  /miembros/historicos/{historicalProfile:slug}  historical-profiles.show
GET  /foros                                         forums.index
GET  /foros/{forum:slug}                            forums.show
GET  /foros/{forum:slug}/temas/{topic:slug}         forum-topics.show
GET  /actividad                                     community-activity.index

// Autenticadas
PATCH  /profile/comunidad                           community-profile.update
POST   /miembros/historicos/{historicalProfile}/reclamar historical-profile-claims.store
GET    /foros/{forum:slug}/temas/crear               forum-topics.create
POST   /foros/{forum:slug}/temas                     forum-topics.store
GET    /foros/temas/{topic}/editar                   forum-topics.edit
PATCH  /foros/temas/{topic}                          forum-topics.update
DELETE /foros/temas/{topic}                          forum-topics.destroy
POST   /foros/temas/{topic}/respuestas               forum-posts.store
GET    /foros/respuestas/{post}/editar               forum-posts.edit
PATCH  /foros/respuestas/{post}                      forum-posts.update
DELETE /foros/respuestas/{post}                      forum-posts.destroy
POST   /foros/temas/{topic}/respuesta/{post}/aceptar forum-answers.store
DELETE /foros/temas/{topic}/respuesta                forum-answers.destroy
POST   /foros/temas/{topic}/suscripcion              forum-subscriptions.store
DELETE /foros/temas/{topic}/suscripcion              forum-subscriptions.destroy
POST   /foros/respuestas/{post}/reacciones           reactions.store
DELETE /foros/respuestas/{post}/reacciones/{type}    reactions.destroy
POST   /reportes                                     reports.store
```

Administración debe permanecer dentro de `/admin`, `auth`, `verified` y `admin.panel`, con permisos `can:*` por recurso.

Aplicar limitadores nombrados:

- `community-posts`: por usuario, con límites diferenciados para temas y respuestas.
- `community-reactions`: más permisivo, pero finito.
- `community-reports`: estricto para evitar abuso.
- `historical-claims`: muy estricto.

## 13. Servicios

- `MemberDirectoryService`: compone usuarios visibles y la sección histórica sin filtrar datos privados.
- `ProfileVisibilityService`: decisiones reutilizables de secciones y actividad; las Policies lo consumen.
- `CommunityRankService`: resuelve rango automático, rango especial destacado y recálculo.
- `ForumTopicService`: crea tema + primer post + contadores en una transacción.
- `ForumPostService`: publica/edita/elimina, actualiza contadores, rango, actividad, menciones y suscripciones.
- `ForumCounterService`: incrementos atómicos y reconciliación desde la fuente de verdad.
- `MentionService`: extrae alias, excluye bloqueos y automenciones, persiste menciones y dispara notificaciones únicas.
- `ReactionService`: toggle/idempotencia, contador y notificación.
- `CommunityActivityRecorder`: eventos derivados, privacidad y limpieza.
- `CommunityModerationService`: cambios de estado, reportes, sanciones y bitácora atómica.
- `HistoricalProfileImportService`: validación, dry-run, upsert por clave de origen y reporte de errores.
- `HistoricalProfileClaimService`: evita carreras y aprueba/rechaza dentro de transacción.
- `CommunityContentRenderer`: salida escapada, enlaces de mención seguros y límites de formato.

Agregar comandos operativos:

- `community:recount-posts`
- `community:recount-forums`
- `community:rebuild-activity`
- `community:import-historical-profiles --dry-run`
- `community:expire-sanctions`

## 14. Notificaciones

Reutilizar `Illuminate\Notifications\Notification` y el canal `database` inicialmente:

- `ForumTopicReplyNotification`
- `ForumMentionNotification`
- `ForumReactionNotification`
- `ForumAnswerAcceptedNotification`
- `ForumTopicSubscriptionNotification`
- `ContentModerationDecisionNotification`
- `HistoricalProfileClaimReceivedNotification` para staff
- `HistoricalProfileClaimDecisionNotification`
- `UserSanctionNotification`

Mantener el payload compatible con las vistas actuales:

```php
[
    'kind' => 'forum_mention',
    'title' => 'Te mencionaron en el foro',
    'message' => 'Extracto limitado y escapado',
    'actor_id' => $actor->id,
    'actor_name' => $actor->alias ?: $actor->name,
    'actor_avatar' => $actor->avatarUrl(),
    'url' => $post->publicUrl(),
]
```

Evitar duplicados: una respuesta con mención no debe producir simultáneamente una notificación de suscripción y otra de mención para el mismo usuario si ambas apuntan al mismo evento. Las notificaciones de autores bloqueados no se generan. El canal email debe añadirse solo después de implementar preferencias comunitarias explícitas; no reutilizar automáticamente `episode_email_notifications_enabled`.

## 15. Componentes Blade

### Reutilizar

- `<x-navbar>` y `<x-footer>`.
- `<x-portal-favicon>` y `<x-seo>`.
- Patrones de avatar/initials de perfiles, comentarios y mensajes.
- Clases `.profile-panel`, `.profile-btn`, `.social-*`, tarjetas y estados vacíos.
- Paginación de Laravel ya estilizada en las superficies sociales.

### Crear

- `layouts/community.blade.php`, solo para páginas nuevas, compuesto con los mismos assets públicos; no migrar las vistas existentes de inmediato.
- `components/community/member-card.blade.php`
- `components/community/rank.blade.php`
- `components/community/badge.blade.php`
- `components/community/historical-badge.blade.php`
- `components/community/privacy-notice.blade.php`
- `components/community/signature.blade.php`
- `components/forum/category.blade.php`
- `components/forum/forum-row.blade.php`
- `components/forum/topic-row.blade.php`
- `components/forum/post.blade.php`
- `components/forum/reaction-button.blade.php`
- `components/forum/report-modal.blade.php`
- `components/forum/composer.blade.php`

La firma se renderiza debajo del post solo si el autor la tiene habilitada, el post permite mostrarla y el visor puede ver el perfil. Debe ser texto escapado; no usar `{!! !!}` con contenido del usuario.

El CSS comunitario nuevo puede vivir en `public/assets/css/community.css` cargado después de `style.css`, usando las variables existentes. Esto reduce el riesgo de regresiones en las 3 143 líneas del archivo principal. Más adelante se puede consolidar, pero no durante la primera entrega funcional.

## 16. Panel administrativo

Agregar al sidebar Metronic una sección “Comunidad”, visible por permisos:

- Resumen: temas/posts diarios, usuarios activos, reportes abiertos y reclamaciones pendientes.
- Categorías y foros: orden, visibilidad, permisos de publicación, apertura/cierre.
- Rangos: umbrales automáticos, prioridad, color/icono y rangos especiales.
- Insignias: catálogo y asignación manual.
- Miembros históricos: importación con dry-run, revisión individual, publicar/ocultar.
- Reclamaciones: evidencia, historial y decisión.
- Moderación: reportes, contenido pendiente/oculto y acciones masivas limitadas.
- Sanciones: advertir, silenciar, suspender, revocar.
- Auditoría: lista de `moderation_actions` sin edición.

Permisos Spatie propuestos:

- `view community dashboard`
- `manage forum structure`
- `moderate forum content`
- `manage community ranks`
- `manage community badges`
- `manage historical profiles`
- `review historical claims`
- `manage community sanctions`
- `view moderation audit`

El rol `admin` obtiene todos mediante el patrón actual. `moderator` recibe moderación, reportes y sanciones según decisión operativa, pero no rangos, estructura ni importaciones históricas por defecto.

## 17. Estrategia de moderación

1. **Estados explícitos y soft deletes.** Ocultar conserva evidencia; borrar físicamente no es la acción cotidiana.
2. **Reportes polimórficos.** Al inicio se habilitan para temas/posts; luego pueden abarcar comentarios y perfiles.
3. **Bitácora inmutable.** Toda decisión de staff crea `moderation_actions` con actor, motivo y transición.
4. **Sanciones temporales.** Middleware `EnsureCommunityParticipationAllowed` y Policies consultan sanciones activas.
5. **Rate limiting.** Login ya lo tiene; añadirlo a posts, reacciones, reportes, menciones y comentarios.
6. **Contenido inicial.** Configurar si usuarios nuevos publican directamente o pasan a `pending`; no codificar la confianza en roles comunitarios.
7. **Protección contra abuso.** Límites de longitud, enlaces y menciones; honeypot o desafío solo si las métricas justifican su uso.
8. **Privacidad y bloqueos.** Un bloqueo impide menciones, reacciones dirigidas y mensajes; el feed no debe revelar actividad privada/bloqueada.
9. **Preservación.** `author_name_snapshot` y `nullOnDelete` mantienen legibilidad si una cuenta se elimina. Debe revisarse el borrado actual de cuentas antes de habilitar foros.
10. **Escalamiento.** Reporte abierto → revisión → acción y razón → notificación → resolución. Las acciones sensibles exigen permiso y confirmación.

## 18. Estrategia para perfiles históricos

### Separación segura

`historical_profiles` es un archivo de identidad, no un proveedor de autenticación. Esto permite importar perfiles sin email ni password y evita tocar columnas críticas de `users`.

### Importación

1. Definir formato CSV/JSON con identificador de origen estable.
2. Ejecutar `--dry-run`: validar duplicados, alias, fechas, codificación y rutas de imagen.
3. Generar reporte sin escribir.
4. Importar por chunks con `upsert(source, source_user_id)`.
5. Publicar inicialmente un subconjunto verificado.
6. Guardar datos no normalizados en `snapshot`, nunca credenciales antiguas.

### URLs y presentación

- URL canónica: `/miembros/historicos/{slug}`.
- Mostrar “Miembro histórico de Mundo Yuri”, fecha histórica, antiguo rango/post count y datos archivados disponibles.
- `created_at` indica importación; `historical_joined_at` indica ingreso al foro original.
- La página debe distinguir claramente información archivada de actividad actual.

### Reclamación futura

1. Requiere una cuenta autenticada y verificada.
2. El usuario presenta evidencia; nunca se expone públicamente.
3. Staff revisa y decide.
4. Aprobación transaccional: bloquea perfil, asegura que siga sin reclamar, enlaza `claimed_by_user_id`, registra acción y entrega insignia histórica.
5. No fusionar ni borrar automáticamente registros. Los posts importados conservan el autor histórico y el perfil muestra el vínculo con la cuenta actual.
6. Permitir revocación solo mediante acción administrativa auditada.

## 19. Estrategia para privacidad

### Niveles

- `public`: perfil y secciones habilitadas visibles a cualquiera.
- `members`: visibles únicamente con sesión iniciada.
- `private`: perfil completo solo para propietario y staff; los demás ven 404 o una ficha mínima según decisión de producto.

Recomendación: para `private`, devolver 404 a usuarios ajenos para no confirmar información; directorio y buscador siempre lo excluyen. Administradores/moderadores con permiso conservan acceso auditado.

### Reglas por sección

- La portada, nombre/alias y rango pueden seguir el nivel general.
- Followers, following, favoritos y actividad obedecen sus booleanos y el nivel general.
- `last_activity_at` se oculta por defecto; si se muestra, usar valores aproximados (“esta semana”), no timestamp exacto.
- Email, Google ID, estados de reclamación y evidencia nunca son públicos.
- Los perfiles inactivos siguen retornando 404 como hoy.
- La privacidad se aplica a controller query, Policy, sitemap y metadatos SEO.
- Los perfiles `members` y `private` reciben `noindex`; el sitemap solo incluye perfiles públicos si se decide indexarlos.
- Los contadores no deben filtrar información de secciones ocultas.

### Compatibilidad con bloqueos

El bloqueo tiene prioridad sobre follow, mensaje, reacción y mención. Para perfiles públicos puede mostrarse una ficha mínima sin controles, pero no actividad ni conexiones. Esta regla se centraliza en `UserPolicy`/`ProfileVisibilityService`.

## 20. Estrategia de migraciones segura para producción

Usar una estrategia **expandir → poblar → activar → consolidar**:

1. Capturar backup probado y métricas de tamaño/versión MySQL.
2. Restaurar un dump anonimizado en staging y medir cada DDL.
3. Crear tablas nuevas primero; no renombrar ni eliminar tablas/columnas existentes.
4. Agregar columnas de `users` en migraciones pequeñas y aditivas. Verificar `EXPLAIN ALTER TABLE`/algoritmo soportado por la versión de MySQL antes de producción.
5. Evitar backfills masivos dentro de `up()`. Usar comandos reanudables por chunks y checkpoints.
6. Agregar claves foráneas circulares (`accepted_answer_post_id`) en una migración posterior a la creación de ambas tablas.
7. Crear índices de tablas grandes por separado y en ventana de baja actividad; usar DDL online donde la infraestructura lo permita.
8. Desplegar primero código compatible con ausencia/presencia de datos nuevos cuando sea necesario.
9. Mantener funciones tras feature flags (`community_directory`, `community_profiles`, `forums`, `historical_profiles`, `community_reactions`).
10. Sembrar permisos/rangos con `updateOrCreate`, nunca con IDs fijos.
11. Ejecutar dry-run y contadores antes de habilitar tráfico.
12. Activar por etapas y observar errores, latencia, locks, tamaño de cola y consultas lentas.
13. Los `down()` no deben ser el mecanismo normal de rollback productivo cuando perderían datos; rollback de aplicación y feature flag primero.
14. No cambiar `users.email/password`, no borrar `comments` y no cambiar rutas existentes.

Cada migración debe probarse con SQLite y MySQL. SQLite da velocidad a la suite actual, pero no valida completamente enums, checks, collations, índices largos ni bloqueos DDL de MySQL.

## 21. Índices de base de datos recomendados

### `users`

- `(is_active, profile_visibility, created_at)` para directorio.
- `(forum_posts_count, id)` para rankings/paginación estable.
- `(last_activity_at, id)` si se permite ordenar por actividad.
- `alias` ya es único; revisar collation para que menciones no distingan mayúsculas inesperadamente.

### Historia/rangos

- `historical_profiles`: unique `(source, source_user_id)`, unique `slug`, `(is_published, historical_joined_at)`, `claimed_by_user_id`.
- `historical_profile_claims`: `(status, created_at)`, `(historical_profile_id, status)`, `(claimant_user_id, status)`.
- `community_ranks`: unique `slug`, `(kind, is_active, min_posts)`.
- asignaciones: unique `(user_id, community_rank_id)`, `(community_rank_id, expires_at)`.
- badges: unique `slug`; pivot con primary `(user_id, community_badge_id)`.

### Foros

- `forum_categories`: `(is_active, sort_order)`.
- `forums`: unique `slug`, `(forum_category_id, is_active, sort_order)`, `(visibility, is_active)`.
- `forum_topics`: unique `(forum_id, slug)`, `(forum_id, status, is_pinned, last_posted_at)`, `(author_user_id, created_at)`, `(type, status, last_posted_at)`.
- `forum_posts`: `(forum_topic_id, status, created_at, id)`, `(author_user_id, status, created_at)`, `parent_id`.
- suscripciones: primary `(user_id, forum_topic_id)`, `(forum_topic_id, notification_level)`.

### Interacción/moderación

- `reactions`: unique `(user_id, reactable_type, reactable_id, type)`, `(reactable_type, reactable_id, type)`.
- `mentions`: unique `(mentioned_user_id, mentionable_type, mentionable_id)`, `(mentionable_type, mentionable_id)`.
- `community_activities`: `(visibility, created_at, id)`, `(actor_user_id, created_at)`, `(subject_type, subject_id)`.
- `reports`: `(status, created_at)`, `(assigned_to, status, updated_at)`, `(reportable_type, reportable_id)`.
- `moderation_actions`: `(subject_type, subject_id, created_at)`, `(moderator_user_id, created_at)`.
- `user_sanctions`: `(user_id, type, revoked_at, expires_at)`, `(expires_at, revoked_at)`.

Evitar índices redundantes: comprobar `SHOW INDEX` después de cada migración, porque claves foráneas y restricciones únicas ya crean parte de los índices necesarios.

## 22. Riesgos de compatibilidad

| Riesgo | Impacto | Mitigación |
|---|---|---|
| Columna `users.role` y roles Spatie pueden divergir | Accesos inconsistentes | No eliminar todavía; nuevas capacidades usan `$user->can()`, añadir auditoría/sincronización. |
| No existen Policies | Reglas dispersas y bypass accidental | Introducirlas por módulo con tests antes de mover reglas existentes. |
| `verified` no es efectivo mientras `User` no implemente `MustVerifyEmail` | El panel puede aceptar cuentas no verificadas | Decidir el contrato esperado y habilitarlo en una entrega separada con pruebas de todos los accesos. |
| Google OAuth no comprueba `is_active` antes de `Auth::login` | Una cuenta desactivada podría entrar por Google | Añadir una prueba de regresión y aplicar la misma regla de `LoginRequest` en un cambio previo al lanzamiento comunitario. |
| Comentarios anónimos, aprobados por defecto y sin throttle | Spam/abuso | Mantener comportamiento hasta fase de moderación; añadir limitador y reportes de forma compatible. |
| Borrado actual de usuario es físico | Pérdida o anonimización involuntaria de futuro contenido | `nullOnDelete` + snapshots en foros; rediseñar cierre de cuenta antes de lanzar foros. |
| Vistas consultan contadores desde Blade/navbar | N+1/carga por petición | View composer o servicio de navegación cacheado cuando crezca el volumen. |
| `publicProfileUrl()` usa ID y alias decorativo opcional | Cambio de alias no rompe URL, pero genera múltiples URLs | Definir canonical; conservar ruta existente. |
| Límites de alias distintos (registro 120, edición 50) | Datos que luego no pueden reeditarse | Unificar regla en Form Request en una entrega separada y probada. |
| `CommentController` valida `parent_id` global antes de confirmar target | Superficie de enumeración y reglas acopladas | Nuevo foro no lo reutiliza; corregir comentarios en fase específica. |
| Solo un nivel de respuestas en comentarios | No sirve como foro | Crear `forum_posts`, no extender `comments` para forzar el caso. |
| CSS/JS embebido y duplicación de esqueletos HTML públicos | Riesgo visual y mantenibilidad | Layout nuevo solo para comunidad; migración gradual, screenshots de regresión. |
| Bootstrap por CDN en portal y Metronic local en admin | Diferencias de componentes/CSP | Mantener separación; no cargar Metronic en portal comunitario. |
| HTML de Metronic no conectado ocupa gran superficie | Confusión sobre funcionalidades reales | Tratarlo como referencia; no editarlo ni asumir que está activo. |
| Notificaciones no implementan `ShouldQueue` | Latencia si aumenta volumen | Encolar nuevas notificaciones después de validar workers y reintentos. |
| Contadores desnormalizados | Deriva por carreras/fallos | Incrementos atómicos, transacciones y comandos de recálculo. |
| Polimorfismo sin FK al sujeto | Integridad huérfana | Borrado mediante servicios, pruebas y limpieza programada. |
| Diferencias SQLite/MySQL | Pruebas verdes con DDL inválido o lento en producción | Pipeline MySQL adicional y ensayo sobre staging. |
| Catch-all `/{sectionSlug}` | Nuevas páginas de un segmento pueden ser absorbidas | Declarar rutas comunitarias antes del catch-all y probar `route:list`. |
| Suite base ya tiene 1 fallo | Dificulta detectar regresiones | Corregir primero la discrepancia de `catalog_section` en un commit separado. |
| Privacidad aplicada solo en vistas | Fuga de datos por endpoints/listados | Policies + scopes + tests de matriz guest/member/owner/staff/blocked. |
| Reclamaciones históricas fraudulentas | Suplantación | Revisión manual, evidencia privada, auditoría y sin fusión automática. |

## 23. Orden recomendado de implementación

Cada paso debe vivir en una rama descriptiva y terminar en un commit autocontenido con pruebas. No mezclar migraciones, UI y refactors no relacionados en un mismo commit.

### Fase 0 — Estabilización y observabilidad

1. Corregir la prueba base de `catalog_section` sin cambiar el contrato productivo.
2. Añadir CI con SQLite y MySQL, backup/staging y feature flags comunitarios.
3. Documentar métricas base: usuarios, comentarios, follows, mensajes, tamaño de tablas y latencias.

### Fase 1 — Fundaciones de autorización

4. Agregar permisos Spatie comunitarios de manera idempotente.
5. Introducir Policies y tests de matriz sin mover todavía todas las reglas existentes.
6. Crear limitadores nombrados y middleware de sanción, inicialmente sin sanciones activas.

### Fase 2 — Perfil, privacidad y directorio

7. Agregar columnas aditivas de privacidad, firma, contador y actividad.
8. Implementar `UpdateCommunityProfileRequest`, Policy/scopes de privacidad y pruebas.
9. Lanzar directorio de miembros detrás de feature flag.
10. Ampliar el perfil actual con rango, firma, privacidad y secciones reutilizando su UI.

### Fase 3 — Rangos e insignias

11. Crear tablas, modelos, seed idempotente y `CommunityRankService`.
12. Añadir administración de umbrales/rangos especiales e insignias.
13. Mostrar rango/insignias en perfil y componentes preparados para posts.

### Fase 4 — Perfiles históricos

14. Crear tablas y páginas históricas.
15. Construir importador con dry-run y ejecutar primero en staging.
16. Publicar archivo histórico por lotes.
17. Añadir reclamaciones manuales, auditoría e insignia al aprobar.

### Fase 5 — Núcleo de foros

18. Crear categorías, foros, temas, posts, suscripciones, contadores y soft deletes.
19. Implementar Policies/Form Requests/servicios transaccionales.
20. Construir UI Blade conservando tokens, navbar y footer actuales.
21. Habilitar lectura primero; después escritura a un grupo controlado; finalmente apertura general.

### Fase 6 — Preguntas, respuestas y firmas

22. Activar `type = question`, respuesta aceptada y notificación.
23. Renderizar firmas bajo posts con privacidad y salida escapada.
24. Activar contadores/rangos automáticos y reconciliación programada.

### Fase 7 — Reacciones, menciones y actividad

25. Añadir reacciones idempotentes y contadores.
26. Añadir parser de menciones, persistencia y deduplicación de notificaciones.
27. Añadir feed de actividad con filtros de privacidad y bloqueo.

### Fase 8 — Moderación completa

28. Lanzar reportes, cola de revisión, acciones auditadas y sanciones.
29. Extender reportes/reacciones a comentarios solo después de estabilizar foros.
30. Medir abuso, consultas lentas y cola; ajustar límites e índices con datos reales.

### Fase 9 — Consolidación

31. Extraer gradualmente lógica duplicada de vistas/controladores sin cambiar URLs.
32. Evaluar la retirada futura de `users.role` solo con migración de datos, auditoría y ventana propia; no es requisito para la comunidad.
33. Retirar feature flags únicamente después de estabilidad, rollback ensayado y aceptación visual.

## Criterios transversales de aceptación

- Ninguna ruta actual cambia o desaparece.
- Los perfiles, favoritos, follows, bloqueos, mensajes, comentarios y notificaciones existentes continúan pasando sus pruebas.
- Ningún dato histórico requiere email o contraseña.
- Ningún rango comunitario concede permisos de seguridad.
- Todo contenido del usuario se muestra escapado/sanitizado.
- Privacidad y bloqueos tienen pruebas de autorización, no solo pruebas visuales.
- Las migraciones son aditivas, ensayadas en MySQL y reversibles a nivel de aplicación.
- El portal conserva su identidad oscura rosa/morada y el panel conserva Metronic.
- Cada prompt/cambio se desarrolla en rama y recibe un commit lógico antes de continuar.
