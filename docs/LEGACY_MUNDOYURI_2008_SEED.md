# Importación verificada del archivo Mundo Yuri 2007–2008

El seeder `LegacyMundoYuri2008Seeder` contiene exclusivamente los 21 perfiles verificados de la lista archivada. No está incluido en `DatabaseSeeder`: no se ejecuta durante el desarrollo normal ni crea usuarios, correos, contraseñas o credenciales.

En producción, tras desplegar y ejecutar las migraciones, ejecútalo explícitamente:

```bash
php artisan db:seed --class=Database\\Seeders\\LegacyMundoYuri2008Seeder --force
```

La operación es idempotente. La clave estable `mundo-yuri-wayback-2008:member-XXX` identifica cada registro; si el perfil ya existe, se actualizan sólo los datos históricos verificados y se conservan la reclamación, el vínculo con una cuenta moderna, `created_at` y cualquier información histórica adicional gestionada posteriormente por administración.

El seeder asigna `Miembro Histórico` a los 21 perfiles y `Pionera 2007` a los siete perfiles con fecha de 2007. No asigna insignias de staff, fundación o moderación.
