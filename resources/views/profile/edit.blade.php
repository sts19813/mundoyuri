@extends('layouts.portal')

@section('head')

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mi perfil · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">


@endsection

@section('body')

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
                @if($user->coverImageUrl())
                    <img src="{{ $user->coverImageUrl() }}" alt="" class="profile-cover-media" data-cover-preview>
                @else
                    <img src="" alt="" class="profile-cover-media d-none" data-cover-preview>
                @endif
                @if($user->coverVideoEmbedUrl())
                    <iframe src="{{ $user->coverVideoEmbedUrl() }}" class="profile-cover-video" title="Vista previa de tu video de portada" tabindex="-1" aria-hidden="true" allow="autoplay; encrypted-media; picture-in-picture" data-cover-video-preview></iframe>
                @endif
                <div class="profile-cover-overlay"></div>
                <div class="profile-hero-pattern"></div>
                <button type="button" class="profile-cover-edit" data-cover-source-open>
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14.5 4h-5L7.8 7H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2.8z"/>
                        <circle cx="12" cy="13" r="3"/>
                    </svg>
                    Cambiar portada
                </button>
                <div class="profile-identity">
                    <div @class(['profile-avatar-wrap', 'is-online' => $user->isOnline()])>
                        <button type="button" class="profile-avatar-display" data-avatar-source-open aria-label="Cambiar foto de perfil">
                            @if($user->hasProfileAvatar())
                                <img src="{{ $user->avatarUrl() }}" alt="Foto de perfil de {{ $user->name }}" class="profile-avatar-main" data-avatar-preview>
                                <span class="profile-avatar-main profile-avatar-generic d-none" data-avatar-fallback>{{ $user->initials() }}</span>
                            @else
                                <img src="" alt="Foto de perfil de {{ $user->name }}" class="profile-avatar-main d-none" data-avatar-preview>
                                <span class="profile-avatar-main profile-avatar-generic" data-avatar-fallback>{{ $user->initials() }}</span>
                            @endif
                        </button>
                        <button type="button" class="profile-avatar-edit" data-avatar-source-open title="Cambiar foto">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            <span class="visually-hidden">Cambiar foto</span>
                        </button>
                    </div>
                    <div class="profile-identity-copy">
                        <span class="profile-eyebrow">Tu espacio en Mundo Yuri</span>
                        <h1>{{ $user->name }}</h1>
                        <p>{{ $user->alias ? '@'.$user->alias : $user->email }}</p>
                    </div>
                </div>
                <div class="profile-hero-actions">
                    <a class="profile-btn profile-btn-soft" href="{{ $user->publicProfileUrl() }}">Ver perfil público</a>
                    <div class="profile-status-chip">
                        <span></span>
                        {{ $user->email_verified_at ? 'Cuenta verificada' : 'Verificación pendiente' }}
                    </div>
                </div>
            </section>

            <div class="profile-grid">
                <section class="profile-panel profile-panel-main">
                    <div class="profile-panel-heading">
                        <div>
                            <span class="profile-panel-kicker">Información personal</span>
                            <h2>Edita tu perfil</h2>
                        </div>
                        <div class="profile-panel-heading-actions">
                            <p>Esta información identifica tus comentarios y aportes.</p>
                            <button type="submit" form="profile-update-form" class="profile-btn profile-btn-primary">Guardar cambios</button>
                        </div>
                    </div>

                    @php($defaultProfileTab = $errors->hasAny(['signature_text', 'signature_image']) ? 'signature' : ($errors->has('profile_visibility') ? 'privacy' : 'personal'))
                    <div class="profile-tabs" data-profile-tabs data-profile-default-tab="{{ $defaultProfileTab }}">
                        <div class="profile-tab-list" role="tablist" aria-label="Secciones de edición del perfil">
                            <button type="button" class="profile-tab is-active" id="profile-tab-personal" role="tab" aria-selected="true" aria-controls="profile-panel-personal" data-profile-tab="personal">Información personal</button>
                            <button type="button" class="profile-tab" id="profile-tab-privacy" role="tab" aria-selected="false" aria-controls="profile-panel-privacy" data-profile-tab="privacy">Privacidad</button>
                            <button type="button" class="profile-tab" id="profile-tab-notifications" role="tab" aria-selected="false" aria-controls="profile-panel-notifications" data-profile-tab="notifications">Notificaciones</button>
                            <button type="button" class="profile-tab" id="profile-tab-signature" role="tab" aria-selected="false" aria-controls="profile-panel-signature" data-profile-tab="signature">Firma</button>
                        </div>
                    </div>

                    <form id="profile-update-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="portal-profile-form">
                        @csrf
                        @method('PATCH')
                        <input type="file" id="profile_image" accept="image/png,image/jpeg,image/webp" class="visually-hidden" data-avatar-input>
                        <input type="hidden" value="0" data-avatar-remove>
                        <input type="file" id="cover_image" accept="image/png,image/jpeg,image/webp" class="visually-hidden" data-cover-input>
                        <input type="hidden" value="0" data-cover-remove>
                        <input type="file" id="signature_image" name="signature_image" accept="image/png,image/jpeg,image/webp,image/gif" class="visually-hidden" data-signature-input @disabled($user->signatureIsSuspended())>
                        <input type="hidden" name="signature_remove" value="0" data-signature-remove>

                        <input id="cover_video_url" type="hidden" value="{{ old('cover_video_url', $user->cover_video_url) }}">
                        <p class="profile-media-change-notice" data-profile-media-change-notice hidden aria-live="polite"></p>
                        @error('profile_image')<span class="profile-field-error">{{ $message }}</span>@enderror
                        @error('cover_image')<span class="profile-field-error">{{ $message }}</span>@enderror
                        @error('cover_video_url')<span class="profile-field-error">{{ $message }}</span>@enderror

                        <section class="profile-tab-panel" id="profile-panel-personal" role="tabpanel" aria-labelledby="profile-tab-personal" data-profile-tab-panel="personal">
                            <div class="profile-section-heading">
                                <span class="profile-panel-kicker">Tu presentación</span>
                                <h3>Biografía</h3>
                                <p>Cuéntale a la comunidad un poco de ti, con enlaces, imágenes o videos si quieres.</p>
                            </div>
                            <div class="profile-field profile-field-wide">
                                <label for="biography">Biografía <span>Con enlaces, imágenes y videos</span></label>
                                <x-rich-editor
                                    id="biography"
                                    name="biography"
                                    :value="old('biography', $user->biography)"
                                    placeholder="Cuéntale a la comunidad algo sobre ti, tus gustos y tus series favoritas…"
                                    min-height="260px"
                                />
                                @error('biography')<span class="profile-field-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="profile-section-heading profile-section-heading-compact">
                                <span class="profile-panel-kicker">Tu cuenta</span>
                                <h3>Nombre y alias</h3>
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

                            <fieldset class="community-profile-settings">
                                <legend>Información personal</legend>
                                <p>Estos datos ayudan a que otras personas conozcan tus intereses y contexto.</p>
                                <div class="profile-form-grid">
                                    <div class="profile-field">
                                        <label for="location">Localización <span>Opcional</span></label>
                                        <input id="location" name="location" type="text" maxlength="120" value="{{ old('location', $user->location) }}" autocomplete="address-level2" class="@error('location') is-invalid @enderror">
                                        @error('location')<span class="profile-field-error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="profile-field">
                                        <label for="occupation">Ocupación <span>Opcional</span></label>
                                        <input id="occupation" name="occupation" type="text" maxlength="160" value="{{ old('occupation', $user->occupation) }}" class="@error('occupation') is-invalid @enderror">
                                        @error('occupation')<span class="profile-field-error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="profile-field profile-field-wide">
                                        <label for="website">Sitio web <span>Debe comenzar con http:// o https://</span></label>
                                        <input id="website" name="website" type="url" maxlength="2048" value="{{ old('website', $user->website) }}" placeholder="https://" autocomplete="url" class="@error('website') is-invalid @enderror">
                                        @error('website')<span class="profile-field-error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="profile-field profile-field-wide">
                                        <label for="interests">Intereses <span>Máximo 1000 caracteres</span></label>
                                        <textarea id="interests" name="interests" rows="4" maxlength="1000" placeholder="Anime, manga, arte, escritura…" class="@error('interests') is-invalid @enderror">{{ old('interests', $user->interests) }}</textarea>
                                        @error('interests')<span class="profile-field-error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </fieldset>

                        </section>

                        <section class="profile-tab-panel" id="profile-panel-privacy" role="tabpanel" aria-labelledby="profile-tab-privacy" data-profile-tab-panel="privacy" hidden>
                            <fieldset class="community-profile-settings profile-tab-settings">
                                <legend>Privacidad del perfil</legend>
                                <p>El directorio muestra únicamente perfiles públicos. Tu perfil seguirá disponible para ti y para el equipo de moderación.</p>
                                <div class="profile-field">
                                    <label for="profile_visibility">Quién puede consultar tu perfil</label>
                                    <select id="profile_visibility" name="profile_visibility">
                                        <option value="public" @selected(old('profile_visibility', $user->profile_visibility) === 'public')>Cualquier persona</option>
                                        <option value="members" @selected(old('profile_visibility', $user->profile_visibility) === 'members')>Solo miembros con sesión iniciada</option>
                                        <option value="private" @selected(old('profile_visibility', $user->profile_visibility) === 'private')>Solo yo y el equipo de moderación</option>
                                    </select>
                                    @error('profile_visibility')<span class="profile-field-error">{{ $message }}</span>@enderror
                                </div>

                                <div class="community-privacy-grid">
                                    @foreach([
                                        'show_last_seen' => ['Mostrar última visita', 'Permite ver cuándo visitaste Mundo Yuri por última vez.'],
                                        'show_join_date' => ['Mostrar fecha de ingreso', 'Incluye tu fecha de registro o fecha histórica.'],
                                        'show_favorites' => ['Mostrar favoritas', 'Comparte tus series favoritas en el perfil.'],
                                        'show_activity' => ['Mostrar actividad reciente', 'Muestra tus participaciones públicas más recientes.'],
                                        'show_signatures' => ['Mostrar firmas', 'Oculta todas las firmas de las conversaciones para ti.'],
                                    ] as $field => [$label, $description])
                                        <label class="community-privacy-option">
                                            <input type="hidden" name="{{ $field }}" value="0">
                                            <input type="checkbox" name="{{ $field }}" value="1" @checked((bool) old($field, $user->{$field}))>
                                            <span><strong>{{ $label }}</strong><small>{{ $description }}</small></span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>

                        </section>

                        <section class="profile-tab-panel" id="profile-panel-signature" role="tabpanel" aria-labelledby="profile-tab-signature" data-profile-tab-panel="signature" hidden>
                            <fieldset class="community-profile-settings profile-tab-settings">
                                <legend>Firma comunitaria</legend>
                                <p>Se muestra debajo de tus participaciones cuando la persona que lee tenga activadas las firmas.</p>
                                @if($user->signatureIsSuspended())
                                    <div class="portal-alert portal-alert-error" role="alert">
                                        Tu firma está suspendida hasta {{ $user->signature_suspended_until->translatedFormat('d M Y, H:i') }}.
                                    </div>
                                @endif
                                <label class="community-privacy-option mb-4">
                                    <input type="hidden" name="signature_enabled" value="0" @disabled($user->signatureIsSuspended())>
                                    <input type="checkbox" name="signature_enabled" value="1" @checked((bool) old('signature_enabled', $user->signature_enabled)) @disabled($user->signatureIsSuspended())>
                                    <span><strong>Activar mi firma</strong><small>Conserva el texto y la imagen cuando la desactives.</small></span>
                                </label>
                                <div class="profile-field">
                                    <label for="signature_text">Texto de firma <span>Máximo 500 caracteres</span></label>
                                    <textarea id="signature_text" name="signature_text" rows="4" maxlength="500" @disabled($user->signatureIsSuspended()) class="@error('signature_text') is-invalid @enderror">{{ old('signature_text', $user->signature_text) }}</textarea>
                                    @error('signature_text')<span class="profile-field-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="community-signature-upload">
                                    <div>
                                        <strong>Imagen de firma</strong>
                                        <span>JPG, PNG, WebP o GIF · máximo 2 MB y 600 × 180 px</span>
                                    </div>
                                    @if($user->signatureImageUrl())
                                        <img src="{{ $user->signatureImageUrl() }}" alt="Tu firma actual" data-signature-preview>
                                    @else
                                        <img src="" alt="Vista previa de la firma" class="d-none" data-signature-preview>
                                    @endif
                                    <div class="profile-photo-actions">
                                        <label for="signature_image" class="profile-btn profile-btn-soft @if($user->signatureIsSuspended()) disabled @endif">Elegir imagen</label>
                                        <button type="button" class="profile-btn profile-btn-text" data-signature-clear @disabled($user->signatureIsSuspended())>Quitar imagen</button>
                                    </div>
                                    @error('signature_image')<span class="profile-field-error">{{ $message }}</span>@enderror
                                </div>
                            </fieldset>

                        </section>
                    </form>

                    <section class="profile-tab-panel profile-notifications-panel" id="profile-panel-notifications" role="tabpanel" aria-labelledby="profile-tab-notifications" data-profile-tab-panel="notifications" hidden>
                        <div class="profile-section-heading">
                            <span class="profile-panel-kicker">Tú decides</span>
                            <h3>Preferencias de notificación</h3>
                            <p>Activa solo los avisos que quieras recibir.</p>
                        </div>
                        <section class="profile-email-settings" id="email-notifications" aria-labelledby="email-notifications-title">
                            <div class="profile-email-settings-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            </div>
                            <div class="profile-email-settings-copy">
                                <span class="profile-panel-kicker">Preferencias de correo</span>
                                <h3 id="email-notifications-title">Avisos de nuevos episodios</h3>
                                <p>{{ $user->episode_email_notifications_enabled
                                    ? 'Te avisaremos por correo en cuanto publiquemos un episodio nuevo.'
                                    : 'Estos avisos están pausados. Puedes reactivarlos cuando quieras.' }}</p>
                            </div>
                            <form method="POST" action="{{ route('email-episode-notifications.update') }}" data-email-preference-form @if($user->episode_email_notifications_enabled) data-confirm-disable="¿Seguro que quieres pausar los avisos? Podrías perderte nuevos episodios cuando estén disponibles." @endif>
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="enabled" value="{{ $user->episode_email_notifications_enabled ? 0 : 1 }}">
                                <button type="submit" class="profile-email-toggle{{ $user->episode_email_notifications_enabled ? ' is-active' : '' }}" aria-pressed="{{ $user->episode_email_notifications_enabled ? 'true' : 'false' }}">
                                    <span class="profile-email-toggle-track" aria-hidden="true"><span></span></span>
                                    {{ $user->episode_email_notifications_enabled ? 'Activados' : 'Activar avisos' }}
                                </button>
                            </form>
                        </section>

                        <section class="profile-email-settings" id="push-notifications" aria-labelledby="push-notifications-title">
                            <div class="profile-email-settings-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                            </div>
                            <div class="profile-email-settings-copy">
                                <span class="profile-panel-kicker">Preferencias de notificaciones</span>
                                <h3 id="push-notifications-title">Avisos en este dispositivo</h3>
                                <p>Recibe mensajes privados, menciones, respuestas y avisos importantes aunque Mundo Yuri no esté abierto.</p>
                            </div>
                            <button type="button" class="profile-email-toggle{{ $user->push_notifications_enabled ? ' is-active' : '' }}" data-push-toggle aria-pressed="{{ $user->push_notifications_enabled ? 'true' : 'false' }}">
                                <span class="profile-email-toggle-track{{ $user->push_notifications_enabled ? ' is-active' : '' }}" data-push-switch aria-hidden="true"><span></span></span>
                                <span data-push-status>{{ $user->push_notifications_enabled ? 'Activadas' : 'Activar avisos' }}</span>
                            </button>
                        </section>
                    </section>
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
                            <div>
                                <dt>Correos de episodios</dt>
                                <dd class="profile-preference-status{{ $user->episode_email_notifications_enabled ? ' is-active' : '' }}">{{ $user->episode_email_notifications_enabled ? 'Activos' : 'Pausados' }}</dd>
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

    <dialog class="profile-cover-source-dialog" data-cover-source-dialog aria-labelledby="cover-source-title">
        <div class="profile-cover-source-dialog-inner">
            <div class="profile-cover-source-dialog-heading">
                <div>
                    <span class="profile-panel-kicker">Personaliza tu perfil</span>
                    <h2 id="cover-source-title">¿Cómo quieres tu portada?</h2>
                </div>
                <button type="button" class="profile-cover-source-close" data-cover-source-close aria-label="Cerrar">×</button>
            </div>
            <p>Elige una imagen o agrega un video de YouTube. Podrás revisar el cambio antes de guardar el perfil.</p>
            <div class="profile-cover-source-options">
                <button type="button" class="profile-cover-source-option" data-cover-select-image>
                    <span class="profile-cover-source-option-icon" aria-hidden="true">▧</span>
                    <span><strong>Subir una imagen</strong><small>Hasta 10 MB y 4K. La convertimos a WebP.</small></span>
                    <span aria-hidden="true">→</span>
                </button>
                <button type="button" class="profile-cover-source-option" data-cover-select-video>
                    <span class="profile-cover-source-option-icon" aria-hidden="true">▷</span>
                    <span><strong>Usar un video de YouTube</strong><small>Pega el enlace de un video para tu perfil público.</small></span>
                    <span aria-hidden="true">→</span>
                </button>
                <button type="button" class="profile-cover-source-option profile-cover-source-option-danger" data-cover-remove-all>
                    <span class="profile-cover-source-option-icon" aria-hidden="true">×</span>
                    <span><strong>Quitar portada</strong><small>Elimina tanto la imagen como el video de YouTube.</small></span>
                    <span aria-hidden="true">→</span>
                </button>
            </div>
        </div>
    </dialog>

    <dialog class="profile-cover-source-dialog profile-avatar-source-dialog" data-avatar-source-dialog aria-labelledby="avatar-source-title">
        <div class="profile-cover-source-dialog-inner">
            <div class="profile-cover-source-dialog-heading">
                <div>
                    <span class="profile-panel-kicker">Personaliza tu perfil</span>
                    <h2 id="avatar-source-title">Tu foto de perfil</h2>
                </div>
                <button type="button" class="profile-cover-source-close" data-avatar-source-close aria-label="Cerrar">×</button>
            </div>
            <p>Elige una foto para tu perfil o vuelve al avatar genérico. Las imágenes se optimizan automáticamente a WebP.</p>
            <div class="profile-cover-source-options">
                <button type="button" class="profile-cover-source-option" data-avatar-select-image>
                    <span class="profile-cover-source-option-icon" aria-hidden="true">▧</span>
                    <span><strong>Subir una foto</strong><small>JPG, PNG o WebP de hasta 2 MB.</small></span>
                    <span aria-hidden="true">→</span>
                </button>
                <button type="button" class="profile-cover-source-option profile-cover-source-option-danger" data-avatar-clear>
                    <span class="profile-cover-source-option-icon" aria-hidden="true">×</span>
                    <span><strong>Usar avatar genérico</strong><small>Quita tu foto actual y muestra tus iniciales.</small></span>
                    <span aria-hidden="true">→</span>
                </button>
            </div>
        </div>
    </dialog>

    <x-footer />

    <script>
        const avatarInput = document.querySelector('[data-avatar-input]');
        const avatarPreview = document.querySelector('[data-avatar-preview]');
        const avatarFallback = document.querySelector('[data-avatar-fallback]');
        const avatarRemove = document.querySelector('[data-avatar-remove]');
        const coverInput = document.querySelector('[data-cover-input]');
        const coverPreview = document.querySelector('[data-cover-preview]');
        const coverRemove = document.querySelector('[data-cover-remove]');
        const coverVideoInput = document.getElementById('cover_video_url');
        const coverSourceDialog = document.querySelector('[data-cover-source-dialog]');
        const avatarSourceDialog = document.querySelector('[data-avatar-source-dialog]');
        const profileMediaChangeNotice = document.querySelector('[data-profile-media-change-notice]');
        const profileHeroCard = document.querySelector('.profile-hero-card');
        const profileMediaEndpoint = @json(route('profile.media.update'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const signatureInput = document.querySelector('[data-signature-input]');
        const signaturePreview = document.querySelector('[data-signature-preview]');
        const signatureRemove = document.querySelector('[data-signature-remove]');
        const profileTabs = document.querySelector('[data-profile-tabs]');

        const activateProfileTab = (tabName) => {
            document.querySelectorAll('[data-profile-tab]').forEach((tab) => {
                const isActive = tab.dataset.profileTab === tabName;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', String(isActive));
            });

            document.querySelectorAll('[data-profile-tab-panel]').forEach((panel) => {
                panel.hidden = panel.dataset.profileTabPanel !== tabName;
            });
        };

        if (profileTabs) {
            document.querySelectorAll('[data-profile-tab]').forEach((tab) => {
                tab.addEventListener('click', () => activateProfileTab(tab.dataset.profileTab));
            });

            activateProfileTab(profileTabs.dataset.profileDefaultTab || 'personal');
        }

        const setProfileMediaStatus = (message, isError = false) => {
            if (!profileMediaChangeNotice) return;

            profileMediaChangeNotice.textContent = message;
            profileMediaChangeNotice.classList.toggle('is-error', isError);
            profileMediaChangeNotice.hidden = false;
        };

        const setCoverVideoPreview = (embedUrl) => {
            document.querySelector('[data-cover-video-preview]')?.remove();
            if (!embedUrl || !profileHeroCard) return;

            const iframe = document.createElement('iframe');
            iframe.src = embedUrl;
            iframe.className = 'profile-cover-video';
            iframe.title = 'Vista previa de tu video de portada';
            iframe.tabIndex = -1;
            iframe.setAttribute('aria-hidden', 'true');
            iframe.allow = 'autoplay; encrypted-media; picture-in-picture';
            iframe.dataset.coverVideoPreview = '';
            profileHeroCard.querySelector('.profile-cover-overlay')?.before(iframe);
        };

        const saveProfileMedia = async (data, loadingMessage, successMessage) => {
            setProfileMediaStatus(loadingMessage);

            try {
                const response = await fetch(profileMediaEndpoint, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: data,
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstError = payload.errors ? Object.values(payload.errors).flat()[0] : null;
                    throw new Error(firstError || 'No se pudo guardar el cambio. Inténtalo de nuevo.');
                }

                setProfileMediaStatus(successMessage);

                return payload;
            } catch (error) {
                setProfileMediaStatus(error.message || 'No se pudo guardar el cambio. Inténtalo de nuevo.', true);

                return null;
            }
        };

        avatarInput?.addEventListener('change', async function () {
            const file = this.files?.[0];
            if (!file) return;

            avatarPreview.src = URL.createObjectURL(file);
            avatarPreview.classList.remove('d-none');
            avatarFallback.classList.add('d-none');
            avatarRemove.value = '0';
            avatarSourceDialog?.close();

            const data = new FormData();
            data.append('profile_image', file);
            data.append('avatar_remove', '0');
            const result = await saveProfileMedia(data, 'Subiendo foto de perfil…', 'Foto de perfil guardada.');

            if (result?.avatar_url) avatarPreview.src = result.avatar_url;
            avatarInput.value = '';
        });

        const openAvatarSourceDialog = () => {
            if (avatarSourceDialog?.showModal) {
                avatarSourceDialog.showModal();
                return;
            }

            avatarInput?.click();
        };

        document.querySelectorAll('[data-avatar-source-open]').forEach((trigger) => {
            trigger.addEventListener('click', openAvatarSourceDialog);
        });

        document.querySelector('[data-avatar-clear]')?.addEventListener('click', async function () {
            avatarInput.value = '';
            avatarPreview.removeAttribute('src');
            avatarPreview.classList.add('d-none');
            avatarFallback.classList.remove('d-none');
            avatarSourceDialog?.close();

            const data = new FormData();
            data.append('avatar_remove', '1');
            const result = await saveProfileMedia(data, 'Quitando foto de perfil…', 'Avatar genérico activado.');
            avatarRemove.value = '0';

            if (result?.avatar_url) {
                avatarPreview.src = result.avatar_url;
                avatarPreview.classList.remove('d-none');
                avatarFallback.classList.add('d-none');
            }
        });

        coverInput?.addEventListener('change', async function () {
            const file = this.files?.[0];
            if (!file) return;

            coverPreview.src = URL.createObjectURL(file);
            coverPreview.classList.remove('d-none');
            coverSourceDialog?.close();

            const data = new FormData();
            data.append('cover_image', file);
            data.append('cover_remove', '0');
            const result = await saveProfileMedia(data, 'Optimizando y subiendo portada…', 'Portada guardada.');
            coverRemove.value = '0';

            if (result) {
                coverVideoInput.value = '';
                setCoverVideoPreview(null);
                if (result.cover_image_url) coverPreview.src = result.cover_image_url;
            }
            coverInput.value = '';
        });

        document.querySelector('[data-cover-remove-all]')?.addEventListener('click', async function () {
            coverInput.value = '';
            coverPreview.removeAttribute('src');
            coverPreview.classList.add('d-none');
            coverSourceDialog?.close();

            const data = new FormData();
            data.append('cover_remove', '1');
            const result = await saveProfileMedia(data, 'Quitando portada…', 'Portada eliminada.');
            coverRemove.value = '0';

            if (result) {
                coverVideoInput.value = '';
                setCoverVideoPreview(null);
            }
        });

        const openCoverSourceDialog = () => {
            if (coverSourceDialog?.showModal) {
                coverSourceDialog.showModal();
                return;
            }

            coverInput?.click();
        };

        document.querySelectorAll('[data-cover-source-open]').forEach((trigger) => {
            trigger.addEventListener('click', openCoverSourceDialog);
        });

        document.querySelector('[data-cover-source-close]')?.addEventListener('click', () => coverSourceDialog?.close());

        coverSourceDialog?.addEventListener('click', (event) => {
            if (event.target === coverSourceDialog) coverSourceDialog.close();
        });

        document.querySelector('[data-cover-select-image]')?.addEventListener('click', () => {
            coverSourceDialog?.close();
            coverInput?.click();
        });

        document.querySelector('[data-cover-select-video]')?.addEventListener('click', async () => {
            coverSourceDialog?.close();
            const videoUrl = window.prompt('Pega el enlace de YouTube para tu portada:', coverVideoInput?.value || '');

            if (videoUrl !== null && coverVideoInput) {
                coverVideoInput.value = videoUrl.trim();
                const data = new FormData();
                data.append('cover_video_url', coverVideoInput.value);
                const result = await saveProfileMedia(data, 'Guardando video de portada…', 'Video de portada guardado.');

                if (result) setCoverVideoPreview(result.cover_video_embed_url);
            }
        });

        document.querySelector('[data-avatar-source-close]')?.addEventListener('click', () => avatarSourceDialog?.close());

        avatarSourceDialog?.addEventListener('click', (event) => {
            if (event.target === avatarSourceDialog) avatarSourceDialog.close();
        });

        document.querySelector('[data-avatar-select-image]')?.addEventListener('click', () => {
            avatarSourceDialog?.close();
            avatarInput?.click();
        });

        signatureInput?.addEventListener('change', function () {
            const file = this.files?.[0];
            if (!file) return;

            signaturePreview.src = URL.createObjectURL(file);
            signaturePreview.classList.remove('d-none');
            signatureRemove.value = '0';
        });

        document.querySelector('[data-signature-clear]')?.addEventListener('click', function () {
            signatureInput.value = '';
            signaturePreview.removeAttribute('src');
            signaturePreview.classList.add('d-none');
            signatureRemove.value = '1';
        });
    </script>
@endsection
