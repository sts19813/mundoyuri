# Insignias comunitarias

El catálogo de insignias es independiente de rangos y permisos. Las insignias con una condición objetiva se sincronizan automáticamente; los reconocimientos de criterio humano, eventos, contribuciones externas y secretos conservan asignación manual.

La actividad automática no depende del foro: cuenta mensajes enviados por chat y comentarios aprobados en capítulos. El sistema solo evalúa cantidades y relaciones de respuesta; nunca lee ni expone el contenido de los mensajes privados.

## Importar el catálogo aprobado

En producción, después de desplegar el código y las migraciones:

```bash
php artisan migrate --force
php artisan db:seed --class=Database\\Seeders\\CommunityBadgeCatalogSeeder --force
```

El seeder usa el `slug` como identificador y puede ejecutarse nuevamente sin crear duplicados. Sincroniza nombres, emojis, descripciones, tipo, prioridad y color del catálogo; no modifica `badge_user` ni las asignaciones históricas existentes.

No está registrado en `DatabaseSeeder`: se ejecuta de forma explícita también en producción.

## Asignación manual

1. Entra a `/admin/insignias-comunidad` para revisar, crear o editar insignias.
2. Abre `/admin/usuarios/{id}` para la persona destinataria.
3. En **Insignias comunitarias**, revisa la columna **Asignación y condición exacta**. Puedes usar **Sincronizar ahora** para aplicar todas las reglas a la base actual. La tarea `badges:sync-automation` también se ejecuta cada día a las 03:15.
4. Para un reconocimiento manual, entra al perfil de la persona, selecciona una insignia, añade una nota interna opcional como evidencia y pulsa **Asignar**.

El sistema registra altas y retiros en el historial del módulo, incluyendo si el origen fue manual o automático, la regla evaluada y la fecha. Una concesión manual no es retirada por la automatización. Las insignias no otorgan permisos: estos continúan dependiendo de roles y permisos de Spatie.

## Emojis ahora; imágenes después

El campo `icon` es el emoji actual. La tabla también dispone de `image_path`, nullable y aditivo, para una futura carga de assets. Mientras esté vacío, las vistas muestran el emoji. Cuando se implemente la carga de imágenes, un archivo en el disco público y su ruta en `image_path` reemplazarán visualmente al emoji, sin cambiar los slugs ni las asignaciones existentes.

Los nombres de asset acordados se conservarán bajo `community-badges/` cuando se diseñen, por ejemplo `community-badges/legacy-member.webp`.
