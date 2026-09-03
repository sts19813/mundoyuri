# Importación de perfiles históricos

El archivo histórico usa `legacy_profiles`, no `users`. La importación no crea correos, contraseñas, cuentas ni credenciales de acceso.

## Uso

```bash
php artisan community:legacy-import storage/app/perfiles-historicos.csv --dry-run
php artisan community:legacy-import storage/app/perfiles-historicos.csv
```

Ejecuta primero `--dry-run`. El proceso identifica cada fila por `legacy_external_key`, por lo que puede repetirse sin duplicar perfiles: una clave existente actualiza únicamente los datos archivados importables y conserva el estado de reclamación.

## CSV UTF-8

Columnas obligatorias:

| Columna | Descripción |
| --- | --- |
| `legacy_external_key` | Identificador estable de la fuente, por ejemplo `foro-2007:123`. |
| `nickname` | Nickname recuperado del foro. |

Columnas opcionales:

| Columna | Descripción |
| --- | --- |
| `legacy_joined_at` | Fecha histórica, en formato ISO recomendado (`2007-04-12`). |
| `legacy_rank` | Rango que figuraba en el foro original. |
| `legacy_message_count` | Número entero no negativo de mensajes archivados. |
| `legacy_location` | Localización histórica pública. |
| `legacy_occupation` | Ocupación histórica pública. |
| `legacy_interests` | Intereses históricos públicos. |
| `legacy_website` | URL histórica HTTP/HTTPS. |
| `legacy_avatar_path` | Ruta local opcional bajo `legacy-avatars/`; nunca una URL remota. |
| `source` | Fuente de archivo o conjunto de capturas. Por defecto: `archivo-mundo-yuri`. |
| `evidence` | Referencia privada de evidencia. |
| `admin_notes` | Notas privadas de administración. |
| `is_published` | `true`/`false`, `1`/`0` o `sí`/`no`. Por defecto: `true`. |

Ejemplo:

```csv
legacy_external_key,nickname,legacy_joined_at,legacy_rank,legacy_message_count,legacy_location,legacy_occupation,legacy_interests,legacy_website,source,evidence,is_published
foro-2007:123,LunaRosa,2007-04-12,Yuri Fan,184,Mérida,Ilustradora,"Manga, anime y arte",https://example.org,capturas-2007,captura-archivo-014,true
```

No incluyas email, contraseña ni datos de inicio de sesión. `created_at` se genera al importar y conserva su significado técnico; la fecha pública de registro es `legacy_joined_at`.
