<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mi perfil · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar />

    <main class="portal-profile-page">
        <div class="profile-ambient profile-ambient-one"></div>
        <div class="profile-ambient profile-ambient-two"></div>

        <div class="container-xl px-4 position-relative">
            <nav class="profile-breadcrumb" aria-label="Migas de pan">
                <a href="{{ route('home') }}">Inicio</a>
                <span>›</span>
                <span>Mi perfil</span>
            </nav>

            @if(session('success'))
                <div class="portal-alert portal-alert-success" role="status">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            <section class="profile-hero-card">
                <div class="profile-hero-pattern"></div>
                <div class="profile-identity">
                    <div class="profile-avatar-wrap">
                        @if($user->hasProfileAvatar())
                            <img src="{{ $user->avatarUrl() }}" alt="Foto de perfil de {{ $user->name }}" class="profile-avatar-main" data-avatar-preview>
                            <span class="profile-avatar-main profile-avatar-generic d-none" data-avatar-fallback>{{ $user->initials() }}</span>
                        @else
                            <img src="" alt="Foto de perfil de {{ $user->name }}" class="profile-avatar-main d-none" data-avatar-preview>
                            <span class="profile-avatar-main profile-avatar-generic" data-avatar-fallback>{{ $user->initials() }}</span>
                        @endif
                        <label class="profile-avatar-edit" for="profile_image" title="Cambiar foto">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            <span class="visually-hidden">Cambiar foto</span>
                        </label>
                    </div>
                    <div class="profile-identity-copy">
                        <span class="profile-eyebrow">Tu espacio en Mundo Yuri</span>
                        <h1>{{ $user->name }}</h1>
                        <p>{{ $user->alias ? '@'.$user->alias : $user->email }}</p>
                    </div>
                </div>
                <div class="profile-status-chip">
                    <span></span>
                    {{ $user->email_verified_at ? 'Cuenta verificada' : 'Verificación pendiente' }}
                </div>
            </section>

            <div class="profile-grid">
                <section class="profile-panel profile-panel-main">
                    <div class="profile-panel-heading">
                        <div>
                            <span class="profile-panel-kicker">Información personal</span>
                            <h2>Edita tu perfil</h2>
                        </div>
                        <p>Esta información identifica tus comentarios y aportes.</p>
                    </div>

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="portal-profile-form">
                        @csrf
                        @method('PATCH')
                        <input type="file" id="profile_image" name="profile_image" accept="image/png,image/jpeg,image/webp" class="visually-hidden" data-avatar-input>
                        <input type="hidden" name="avatar_remove" value="0" data-avatar-remove>

                        <div class="profile-photo-controls">
                            <div>
                                <strong>Foto de perfil</strong>
                                <span>JPG, PNG o WebP · máximo 2 MB</span>
                            </div>
                            <div class="profile-photo-actions">
                                <label for="profile_image" class="profile-btn profile-btn-soft">Elegir foto</label>
                                <button type="button" class="profile-btn profile-btn-text" data-avatar-clear>Usar avatar genérico</button>
                            </div>
                            @error('profile_image')<span class="profile-field-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="profile-form-grid">
                            <div class="profile-field profile-field-wide">
                                <label for="name">Nombre</label>
                                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required class="@error('name') is-invalid @enderror">
                                @error('name')<span class="profile-field-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="profile-field">
                                <label for="alias">Alias público <span>Opcional</span></label>
                                <div class="profile-input-prefix">
                                    <span>@</span>
                                    <input id="alias" name="alias" type="text" value="{{ old('alias', $user->alias) }}" autocomplete="nickname" class="@error('alias') is-invalid @enderror">
                                </div>
                                @error('alias')<span class="profile-field-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="profile-field">
                                <label for="email">Correo electrónico</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required class="@error('email') is-invalid @enderror">
                                @error('email')<span class="profile-field-error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="profile-form-footer">
                            <span>Los cambios se aplican también al menú del portal.</span>
                            <button type="submit" class="profile-btn profile-btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </section>

                <aside class="profile-sidebar">
                    <section class="profile-panel profile-account-card">
                        <span class="profile-panel-kicker">Tu cuenta</span>
                        <h2>Resumen</h2>
                        <dl class="profile-details-list">
                            <div>
                                <dt>Miembro desde</dt>
                                <dd>{{ optional($user->created_at)->translatedFormat('M Y') }}</dd>
                            </div>
                            <div>
                                <dt>Rol</dt>
                                <dd>{{ ucfirst($user->role ?: 'usuario') }}</dd>
                            </div>
                            <div>
                                <dt>Comentarios</dt>
                                <dd>{{ $user->comments()->count() }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="profile-panel profile-security-card">
                        <div class="profile-security-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <div>
                            <h3>Seguridad</h3>
                            <p>Confirma tu identidad antes de realizar cambios sensibles en tu cuenta.</p>
                            <a href="{{ route('password.confirm') }}">Confirmar identidad <span>→</span></a>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <x-footer />

    <script>
        const avatarInput = document.querySelector('[data-avatar-input]');
        const avatarPreview = document.querySelector('[data-avatar-preview]');
        const avatarFallback = document.querySelector('[data-avatar-fallback]');
        const avatarRemove = document.querySelector('[data-avatar-remove]');

        avatarInput?.addEventListener('change', function () {
            const file = this.files?.[0];
            if (!file) return;

            avatarPreview.src = URL.createObjectURL(file);
            avatarPreview.classList.remove('d-none');
            avatarFallback.classList.add('d-none');
            avatarRemove.value = '0';
        });

        document.querySelector('[data-avatar-clear]')?.addEventListener('click', function () {
            avatarInput.value = '';
            avatarPreview.removeAttribute('src');
            avatarPreview.classList.add('d-none');
            avatarFallback.classList.remove('d-none');
            avatarRemove.value = '1';
        });
    </script>
</body>
</html>
