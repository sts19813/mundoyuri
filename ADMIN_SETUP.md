# Panel Administrativo - Mundo GL

## ✅ Instalación Completada

Se ha configurado exitosamente un **panel administrativo completo** con:

### 🔧 Componentes Instalados

1. **Laravel Spatie Permissions** - Sistema de roles y permisos
2. **Metronic 8.2.11** - Interfaz Bootstrap profesional
3. **Middleware de Administrador** - Protección de rutas
4. **Base de datos estructurada** - Tablas de roles, permisos y usuarios

### 📋 Estructura del Sistema

```
/admin
├── /dashboard          - Panel principal
├── /users
│   ├── index          - Listar usuarios
│   ├── create         - Crear usuario
│   ├── edit           - Editar usuario
│   └── show           - Ver detalles
└── /profile
    ├── show           - Ver perfil
    ├── edit           - Editar perfil
    └── updatePassword - Cambiar contraseña
```

### 🚀 Primeros Pasos

#### Paso 1: Crear Usuario Administrador

```bash
php artisan create:admin
```

Responde las preguntas interactivas:
- Nombre del administrador
- Email
- Contraseña (se pedirá confirmación)

#### Paso 2: Acceder al Panel

1. Abre tu navegador en: `http://tudominio.com/admin/dashboard`
2. Usa el email y contraseña que creaste
3. ¡Listo! Ya estás en el panel de administración

### 👥 Roles Disponibles

| Rol | Permisos |
|-----|----------|
| **Admin** | Acceso total al sistema |
| **Moderator** | Gestionar series y episodios |
| **User** | Solo lectura |

### 🔐 Middleware de Seguridad

Todas las rutas bajo `/admin/*` están protegidas por:
- `auth` - Verificar autenticación
- `admin` - Verificar rol de administrador

### 📝 Características del Panel

#### Dashboard
- Estadísticas de usuarios
- Conteo de administradores
- Lista rápida de usuarios activos

#### Gestión de Usuarios
- Crear nuevos usuarios
- Editar información de usuarios
- Cambiar rol y estado
- Eliminar usuarios (excepto el propio)

#### Perfil Personal
- Editar nombre y email
- Cambiar contraseña
- Ver información personal

### 🎯 Controladores Creados

- `AdminDashboardController` - Dashboard
- `AdminUserController` - CRUD de usuarios
- `AdminProfileController` - Perfil personal

### 🛠️ Personalización

Para agregar más funcionalidades:

1. **Crear controlador**
   ```bash
   php artisan make:controller Admin/YourController
   ```

2. **Agregar ruta** en `routes/web.php`
   ```php
   Route::resource('your-resource', YourController::class);
   ```

3. **Crear vista** en `resources/views/admin/`

4. **Proteger con permisos** usando:
   ```blade
   @can('your-permission')
       // Contenido protegido
   @endcan
   ```

### 📚 Archivos Importantes

- Controladores: `app/Http/Controllers/Admin/`
- Vistas: `resources/views/admin/`
- Middleware: `app/Http/Middleware/AdminMiddleware.php`
- Rutas: `routes/web.php`
- Modelos: `app/Models/User.php`

### 💡 Tips Útiles

- Usar `Auth::user()->role` para obtener el rol
- Usar `Auth::check()` para verificar autenticación
- Usar `@can()` en vistas para controlar visibilidad

¡Tu panel administrativo está listo para usar! 🎉
