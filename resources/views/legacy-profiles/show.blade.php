<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, follow">
    <title>{{ $legacyProfile->nickname }} · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar />

    <main class="portal-profile-page public-profile-page legacy-public-profile-page">
        <div class="profile-ambient profile-ambient-one"></div>
        <div class="profile-ambient profile-ambient-two"></div>

        <div class="container-xl px-4 position-relative public-profile-alerts">
            @if(session('success'))
                <div class="portal-alert portal-alert-success" role="status">{{ session('success') }}</div>
            @endif
        </div>

        <section class="profile-hero-card public-profile-hero public-profile-hero-full">
            <div class="profile-cover-overlay"></div>
            <div class="profile-hero-pattern"></div>

            <div class="profile-identity">
                <div class="profile-avatar-wrap">
                    @if($legacyProfile->avatarUrl())
                        <img src="{{ $legacyProfile->avatarUrl() }}" alt="Foto de perfil de {{ $legacyProfile->nickname }}" class="profile-avatar-main">
                    @else
                        <span class="profile-avatar-main profile-avatar-generic">{{ mb_strtoupper(mb_substr($legacyProfile->nickname, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="profile-identity-copy">
                    <span class="profile-eyebrow">Perfil de la comunidad</span>
                    <h1>{{ $legacyProfile->nickname }}</h1>
                    <p>Miembro de Mundo Yuri</p>
                    <div class="community-profile-labels">
                        @if($legacyProfile->legacy_rank)
                            <span class="community-rank">{{ $legacyProfile->legacy_rank }}</span>
                        @endif
                        @foreach($legacyProfile->badges as $badge)
                            <x-community.badge :badge="$badge" />
                        @endforeach
                    </div>
                    <div class="public-profile-social-stats">
                        @if(! is_null($legacyProfile->legacy_message_count))
                            <span><strong>{{ number_format($legacyProfile->legacy_message_count) }}</strong> publicaciones</span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <div class="container-xl px-4 position-relative public-profile-content">
            <div class="profile-grid public-profile-grid">
                <div class="public-profile-main">
                    <section class="profile-panel profile-panel-main public-profile-about">
                        <div class="profile-panel-heading">
                            <div>
                                <span class="profile-panel-kicker">Acerca de</span>
                                <h2>Información personal</h2>
                            </div>
                        </div>

                        @if(filled($legacyProfile->legacy_location) || filled($legacyProfile->legacy_occupation) || filled($legacyProfile->legacy_interests) || filled($legacyProfile->legacy_website))
                            <dl class="community-profile-about-grid">
                                @if(filled($legacyProfile->legacy_location))
                                    <div><dt>Localización</dt><dd>{{ $legacyProfile->legacy_location }}</dd></div>
                                @endif
                                @if(filled($legacyProfile->legacy_occupation))
                                    <div><dt>Ocupación</dt><dd>{{ $legacyProfile->legacy_occupation }}</dd></div>
                                @endif
                                @if(filled($legacyProfile->legacy_interests))
                                    <div class="is-wide"><dt>Intereses</dt><dd class="legacy-profile-prewrap">{{ $legacyProfile->legacy_interests }}</dd></div>
                                @endif
                                @if(filled($legacyProfile->legacy_website))
                                    <div class="is-wide"><dt>Sitio web</dt><dd><a href="{{ $legacyProfile->legacy_website }}" rel="nofollow noopener noreferrer" target="_blank">{{ $legacyProfile->legacy_website }}</a></dd></div>
                                @endif
                            </dl>
                        @else
                            <div class="public-profile-empty">
                                <span aria-hidden="true">✦</span>
                                <p>Este perfil todavía no tiene más información pública.</p>
                            </div>
                        @endif
                    </section>
                </div>

                <aside class="profile-sidebar">
                    <section class="profile-panel public-profile-badges-card" aria-labelledby="profile-badges-title">
                        <div class="public-profile-badges-heading">
                            <div>
                                <span class="profile-panel-kicker">Reconocimientos</span>
                                <h2 id="profile-badges-title">Insignias</h2>
                            </div>
                            @if($legacyProfile->badges->isNotEmpty())
                                <span class="public-profile-badges-count">{{ $legacyProfile->badges->count() }}</span>
                            @endif
                        </div>

                        @if($legacyProfile->badges->isNotEmpty())
                            <div class="public-profile-badge-grid" role="list" aria-label="Insignias de {{ $legacyProfile->nickname }}">
                                @foreach($legacyProfile->badges as $badge)
                                    <div role="listitem">
                                        <x-community.badge :badge="$badge" detailed compact />
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="public-profile-badges-empty">Sin insignias todavía.</p>
                        @endif
                    </section>

                    <section class="profile-panel profile-account-card">
                        <span class="profile-panel-kicker">Información</span>
                        <h2>Perfil</h2>
                        <dl class="profile-details-list">
                            @if($legacyProfile->legacy_joined_at)
                                <div>
                                    <dt>Miembro desde</dt>
                                    <dd>{{ $legacyProfile->legacy_joined_at->translatedFormat('d M Y') }}</dd>
                                </div>
                            @endif
                            @if(! is_null($legacyProfile->legacy_message_count))
                                <div>
                                    <dt>Publicaciones</dt>
                                    <dd>{{ number_format($legacyProfile->legacy_message_count) }}</dd>
                                </div>
                            @endif
                            @if($legacyProfile->legacy_rank)
                                <div>
                                    <dt>Rango</dt>
                                    <dd>{{ $legacyProfile->legacy_rank }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if($legacyProfile->canBeClaimed())
                            <div class="legacy-claim-prompt">
                                <strong>¿Este perfil era tuyo?</strong>
                                @auth
                                    <a href="{{ route('legacy-profile-claims.create', ['profile' => $legacyProfile->id]) }}" class="profile-btn profile-btn-soft">Solicitar reclamación</a>
                                @else
                                    <a href="{{ route('login') }}" class="profile-btn profile-btn-soft">Solicitar reclamación</a>
                                @endauth
                            </div>
                        @endif
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <x-footer />
</body>
</html>
