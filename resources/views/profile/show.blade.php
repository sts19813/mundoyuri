<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Perfil de {{ $profileUser->alias ?: $profileUser->name }} en Mundo Yuri.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $profileUser->alias ?: $profileUser->name }} · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar />

    <main class="portal-profile-page public-profile-page">
        <div class="profile-ambient profile-ambient-one"></div>
        <div class="profile-ambient profile-ambient-two"></div>

        <div class="container-xl px-4 position-relative">
            <nav class="profile-breadcrumb" aria-label="Migas de pan">
                <a href="{{ route('home') }}">Inicio</a>
                <span>›</span>
                <span>Perfil de {{ $profileUser->alias ?: $profileUser->name }}</span>
            </nav>

            @if(session('success'))
                <div class="portal-alert portal-alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="portal-alert portal-alert-error" role="alert">{{ session('error') }}</div>
            @endif

            <section class="profile-hero-card public-profile-hero">
                @if($profileUser->coverImageUrl())
                    <img src="{{ $profileUser->coverImageUrl() }}" alt="Portada de {{ $profileUser->alias ?: $profileUser->name }}" class="profile-cover-media">
                @endif
                <div class="profile-cover-overlay"></div>
                <div class="profile-hero-pattern"></div>

                <div class="profile-identity">
                    <div class="profile-avatar-wrap">
                        @if($profileUser->hasProfileAvatar())
                            <img src="{{ $profileUser->avatarUrl() }}" alt="Foto de perfil de {{ $profileUser->alias ?: $profileUser->name }}" class="profile-avatar-main">
                        @else
                            <span class="profile-avatar-main profile-avatar-generic">{{ $profileUser->initials() }}</span>
                        @endif
                    </div>
                    <div class="profile-identity-copy">
                        <span class="profile-eyebrow">Perfil de la comunidad</span>
                        <h1>{{ $profileUser->name }}</h1>
                        <p>{{ $profileUser->alias ? '@'.$profileUser->alias : 'Miembro de Mundo Yuri' }}</p>
                        <div class="community-profile-labels">
                            <x-community.rank :rank="$communityRank" />
                            @if($profileUser->is_legacy)
                                <span class="community-badge community-badge-legacy"><span aria-hidden="true">✦</span> Miembro histórico de Mundo Yuri</span>
                            @endif
                            @foreach($profileUser->badges as $badge)
                                @unless($profileUser->is_legacy && $badge->slug === 'miembro-historico')
                                    <x-community.badge :badge="$badge" />
                                @endunless
                            @endforeach
                        </div>
                        <div class="public-profile-social-stats">
                            <a href="{{ route('profiles.followers', $profileUser) }}">
                                <strong>{{ $profileUser->followers_count }}</strong> seguidores
                            </a>
                            <a href="{{ route('profiles.following', $profileUser) }}">
                                <strong>{{ $profileUser->following_count }}</strong> siguiendo
                            </a>
                            @if($canViewFavorites)
                                <span><strong>{{ $profileUser->favorite_series_count }}</strong> favoritas</span>
                            @endif
                            <span><strong>{{ $profileUser->community_message_count }}</strong> publicaciones</span>
                        </div>
                    </div>
                </div>

                <div class="profile-hero-actions">
                    @if($isOwner)
                        <a class="profile-btn profile-btn-primary" href="{{ route('profile.edit') }}">
                            Editar mi perfil
                        </a>
                        <a class="profile-btn profile-btn-soft" href="{{ route('messages.index') }}">Mensajes</a>
                    @else
                        @auth
                            @unless($interactionBlocked)
                                <form method="POST" action="{{ $isFollowing ? route('users.follow.destroy', $profileUser) : route('users.follow.store', $profileUser) }}">
                                    @csrf
                                    @if($isFollowing)
                                        @method('DELETE')
                                    @endif
                                    <button class="profile-btn {{ $isFollowing ? 'profile-btn-soft' : 'profile-btn-primary' }}" type="submit">
                                        {{ $isFollowing ? 'Siguiendo' : 'Seguir' }}
                                    </button>
                                </form>
                                <a class="profile-btn profile-btn-soft" href="{{ route('messages.show', $profileUser) }}">Mensaje</a>
                            @endunless

                            @if($viewerHasBlocked)
                                <form method="POST" action="{{ route('users.block.destroy', $profileUser) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="profile-btn profile-btn-soft" type="submit">Desbloquear</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('users.block.store', $profileUser) }}" onsubmit="return confirm('¿Quieres bloquear a esta persona? Ya no podrán seguirse ni enviarse mensajes.')">
                                    @csrf
                                    <button class="profile-btn profile-btn-danger-soft" type="submit">Bloquear</button>
                                </form>
                            @endif
                        @else
                            <a class="profile-btn profile-btn-primary" href="{{ route('login') }}">Inicia sesión para seguir</a>
                        @endauth
                    @endif
                    <div class="profile-status-chip">
                        <span></span>
                        {{ $profileUser->is_legacy ? 'Comunidad desde sus orígenes' : 'Miembro de la comunidad' }}
                    </div>
                </div>
            </section>

            <div class="profile-grid public-profile-grid">
                <div class="public-profile-main">
                    <section class="profile-panel profile-panel-main public-profile-about">
                        <div class="profile-panel-heading">
                            <div>
                                <span class="profile-panel-kicker">Acerca de</span>
                                <h2>Biografía</h2>
                            </div>
                        </div>

                        @if(filled($profileUser->biography))
                            <p class="public-profile-biography">{{ $profileUser->biography }}</p>
                        @else
                            <div class="public-profile-empty">
                                <span aria-hidden="true">✦</span>
                                <p>{{ $isOwner ? 'Todavía no has agregado una biografía.' : 'Esta persona todavía no ha agregado una biografía.' }}</p>
                                @if($isOwner)
                                    <a href="{{ route('profile.edit') }}">Agregar biografía</a>
                                @endif
                            </div>
                        @endif

                        @if(filled($profileUser->location) || filled($profileUser->occupation) || filled($profileUser->interests) || filled($profileUser->website))
                            <dl class="community-profile-about-grid">
                                @if(filled($profileUser->location))
                                    <div><dt>Localización</dt><dd>{{ $profileUser->location }}</dd></div>
                                @endif
                                @if(filled($profileUser->occupation))
                                    <div><dt>Ocupación</dt><dd>{{ $profileUser->occupation }}</dd></div>
                                @endif
                                @if(filled($profileUser->interests))
                                    <div class="is-wide"><dt>Intereses</dt><dd>{{ $profileUser->interests }}</dd></div>
                                @endif
                                @if(filled($profileUser->website))
                                    <div class="is-wide"><dt>Sitio web</dt><dd><a href="{{ $profileUser->website }}" rel="nofollow noopener noreferrer" target="_blank">{{ $profileUser->website }}</a></dd></div>
                                @endif
                            </dl>
                        @endif
                    </section>

                    @if($canViewFavorites)
                        <section class="profile-panel profile-panel-main public-profile-favorites">
                        <div class="profile-panel-heading">
                            <div>
                                <span class="profile-panel-kicker">Mi colección</span>
                                <h2>Series favoritas</h2>
                            </div>
                            <div class="profile-panel-heading-actions">
                                <p>{{ $profileUser->favorite_series_count }} {{ $profileUser->favorite_series_count === 1 ? 'historia guardada' : 'historias guardadas' }}</p>
                                <a href="{{ route('profiles.favorites', $profileUser) }}">Ver todas</a>
                            </div>
                        </div>

                        @if($favoriteSeries->isNotEmpty())
                            <div class="public-favorites-grid">
                                @foreach($favoriteSeries as $favorite)
                                    <a class="public-favorite-card" href="{{ route('catalog.series.show', $favorite) }}">
                                        <x-media-preview
                                            :src="$favorite->coverMediaUrl() ?: 'https://picsum.photos/360/500?favorite='.$favorite->id"
                                            :type="$favorite->coverMediaUrl() ? $favorite->coverMediaType() : 'image'"
                                            :alt="$favorite->title"
                                            class="public-favorite-media"
                                        />
                                        <span class="public-favorite-overlay"></span>
                                        <span class="public-favorite-copy">
                                            <strong>{{ $favorite->title }}</strong>
                                            <small>{{ $favorite->genre?->name ?: 'Girls’ Love' }}</small>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="public-profile-empty">
                                <span aria-hidden="true">♡</span>
                                <p>{{ $isOwner ? 'Aún no has agregado series a tus favoritas.' : 'Esta persona todavía no ha compartido series favoritas.' }}</p>
                                @if($isOwner)
                                    <a href="{{ route('catalog.series.index') }}">Explorar el catálogo</a>
                                @endif
                            </div>
                        @endif
                        </section>
                    @endif

                    @if($canViewActivity)
                        <section class="profile-panel profile-panel-main community-profile-activity">
                            <div class="profile-panel-heading">
                                <div>
                                    <span class="profile-panel-kicker">Actividad reciente</span>
                                    <h2>Participación</h2>
                                </div>
                            </div>
                            @forelse($recentActivity as $activity)
                                <article class="community-activity-item">
                                    <p>{{ \Illuminate\Support\Str::limit($activity->body, 220) }}</p>
                                    <time datetime="{{ $activity->created_at?->toAtomString() }}">{{ $activity->created_at?->diffForHumans() }}</time>
                                </article>
                            @empty
                                <div class="public-profile-empty">
                                    <span aria-hidden="true">✦</span>
                                    <p>Este perfil todavía no tiene actividad pública reciente.</p>
                                </div>
                            @endforelse
                        </section>
                    @endif

                    @if(filled($profileUser->signature_text) || $profileUser->signatureImageUrl())
                        <section class="profile-panel community-profile-signature" aria-label="Firma de {{ $profileUser->displayName() }}">
                            <span class="profile-panel-kicker">Firma</span>
                            @if(filled($profileUser->signature_text))
                                <p>{{ $profileUser->signature_text }}</p>
                            @endif
                            @if($profileUser->signatureImageUrl())
                                <img src="{{ $profileUser->signatureImageUrl() }}" alt="Firma gráfica de {{ $profileUser->displayName() }}">
                            @endif
                        </section>
                    @endif
                </div>

                <aside class="profile-sidebar">
                    <section class="profile-panel profile-account-card">
                        <span class="profile-panel-kicker">Información</span>
                        <h2>Perfil</h2>
                        <dl class="profile-details-list">
                            @if($profileUser->show_join_date || $isOwner || auth()->user()?->shouldEnterAdminPanel())
                                <div>
                                    <dt>Miembro desde</dt>
                                    <dd>
                                        @if($profileUser->is_legacy && $profileUser->legacy_joined_at)
                                            Miembro desde {{ $profileUser->legacy_joined_at->format('Y') }}
                                        @else
                                            {{ optional($profileUser->created_at)->translatedFormat('M Y') }}
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            <div>
                                <dt>Publicaciones</dt>
                                <dd>{{ number_format($profileUser->community_message_count) }}</dd>
                            </div>
                            @if($canViewFavorites)
                                <div>
                                    <dt>Favoritas</dt>
                                    <dd>{{ $profileUser->favorite_series_count }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt>Reputación</dt>
                                <dd>{{ number_format($profileUser->community_reputation) }}</dd>
                            </div>
                            @if($profileUser->show_last_seen && $profileUser->last_login_at)
                                <div>
                                    <dt>Última visita</dt>
                                    <dd>{{ $profileUser->last_login_at->diffForHumans() }}</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    @if($isOwner)
                        <section class="profile-panel profile-security-card public-profile-social-card">
                            <div class="profile-security-icon" aria-hidden="true">✉</div>
                            <div>
                                <h3>Tu comunidad</h3>
                                <p>Revisa tus conversaciones y la actividad reciente de tu perfil.</p>
                                <div class="public-profile-social-links">
                                    <a href="{{ route('messages.index') }}">Mensajes</a>
                                    <a href="{{ route('notifications.index') }}">Notificaciones</a>
                                    <a href="{{ route('blocks.index') }}">Cuentas bloqueadas</a>
                                </div>
                            </div>
                        </section>
                    @endif
                </aside>
            </div>
        </div>
    </main>

    <x-footer />
</body>
</html>
