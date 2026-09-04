<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Conoce a quienes forman la comunidad de Mundo Yuri.">
    <title>Comunidad · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar />

    <main class="portal-profile-page community-directory-page">
        <div class="profile-ambient profile-ambient-one"></div>
        <div class="profile-ambient profile-ambient-two"></div>

        <div class="container-xl px-4 position-relative">
            <nav class="profile-breadcrumb" aria-label="Migas de pan">
                <a href="{{ route('home') }}">Inicio</a>
                <span>›</span>
                <span>Comunidad</span>
            </nav>

            <header class="community-directory-hero">
                <span class="profile-eyebrow">Nuestro punto de encuentro</span>
                <h1>Comunidad <em>Mundo Yuri</em></h1>
                <p>Conoce a quienes mantienen viva la comunidad, desde sus primeros recuerdos hasta las nuevas conversaciones.</p>
            </header>

            <section class="profile-panel community-directory-tools" aria-label="Buscar y filtrar miembros">
                <form action="{{ route('community.members') }}" method="GET" class="community-directory-form" data-community-directory-form>
                    <div class="profile-field community-search-field">
                        <label for="community-search">Buscar miembros</label>
                        <input id="community-search" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Nombre, alias o localización">
                    </div>
                    <div class="profile-field">
                        <label for="community-rank">Rango</label>
                        <select id="community-rank" name="rank">
                            <option value="">Todos los rangos</option>
                            @foreach($ranks as $rank)
                                <option value="{{ $rank->id }}" @selected((string) ($filters['rank'] ?? '') === (string) $rank->id)>{{ $rank->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="profile-field">
                        <label for="community-sort">Ordenar por</label>
                        <select id="community-sort" name="sort">
                            <option value="joined" @selected(($filters['sort'] ?? 'joined') === 'joined')>Fecha de registro</option>
                            <option value="activity" @selected(($filters['sort'] ?? '') === 'activity')>Actividad</option>
                            <option value="messages" @selected(($filters['sort'] ?? '') === 'messages')>Mensajes</option>
                            <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Nombre</option>
                        </select>
                    </div>
                    <div class="profile-field">
                        <label for="community-direction">Dirección</label>
                        <select id="community-direction" name="direction">
                            <option value="desc" @selected(($filters['direction'] ?? 'desc') === 'desc')>Descendente</option>
                            <option value="asc" @selected(($filters['direction'] ?? '') === 'asc')>Ascendente</option>
                        </select>
                    </div>
                    @if(filled($filters['filter'] ?? null))
                        <input type="hidden" name="filter" value="{{ $filters['filter'] }}">
                    @endif
                </form>

                <div class="community-filter-chips" aria-label="Filtros rápidos">
                    <a class="{{ empty($filters['filter']) ? 'is-active' : '' }}" href="{{ route('community.members') }}">Todos</a>
                    <a class="{{ ($filters['filter'] ?? null) === 'new' ? 'is-active' : '' }}" href="{{ route('community.members', ['filter' => 'new']) }}">Miembros nuevos</a>
                    <a class="{{ ($filters['filter'] ?? null) === 'oldest' ? 'is-active' : '' }}" href="{{ route('community.members', ['filter' => 'oldest']) }}">Más antiguos</a>
                    <a class="{{ ($filters['filter'] ?? null) === 'active' ? 'is-active' : '' }}" href="{{ route('community.members', ['filter' => 'active']) }}">Más activos</a>
                    <a class="{{ ($filters['filter'] ?? null) === 'legacy' ? 'is-active' : '' }}" href="{{ route('community.members', ['filter' => 'legacy']) }}">Miembros históricos</a>
                </div>
            </section>

            <div class="community-directory-heading">
                <div>
                    <span class="profile-panel-kicker">Directorio público</span>
                    <h2>{{ $members->total() }} {{ $members->total() === 1 ? 'miembro' : 'miembros' }}</h2>
                </div>
                @if(request()->hasAny(['q', 'filter', 'rank', 'sort', 'direction']))
                    <a href="{{ route('community.members') }}">Limpiar filtros</a>
                @endif
            </div>

            <div class="community-member-grid">
                @forelse($members as $member)
                    @php($isHistoricalProfile = $member instanceof \App\Models\LegacyProfile)
                    <article class="community-member-card">
                        <a class="community-member-avatar" href="{{ $isHistoricalProfile ? route('legacy-profiles.show', $member) : $member->publicProfileUrl() }}" aria-label="Ver perfil de {{ $isHistoricalProfile ? $member->nickname : $member->displayName() }}">
                            @if($isHistoricalProfile && $member->avatarUrl())
                                <img src="{{ $member->avatarUrl() }}" alt="Avatar histórico de {{ $member->nickname }}">
                            @elseif(! $isHistoricalProfile && $member->hasProfileAvatar())
                                <img src="{{ $member->avatarUrl() }}" alt="Foto de perfil de {{ $member->displayName() }}">
                            @else
                                <span>{{ $isHistoricalProfile ? mb_strtoupper(mb_substr($member->nickname, 0, 1)) : $member->initials() }}</span>
                            @endif
                        </a>
                        <div class="community-member-copy">
                            <div class="community-member-title">
                                <div>
                                    <h3><a href="{{ $isHistoricalProfile ? route('legacy-profiles.show', $member) : $member->publicProfileUrl() }}">{{ $isHistoricalProfile ? $member->nickname : $member->name }}</a></h3>
                                    @if($isHistoricalProfile)<small>Perfil histórico</small>@elseif($member->alias)<small>{{ '@'.$member->alias }}</small>@endif
                                </div>
                                @if($isHistoricalProfile)
                                    @if($member->legacy_rank)<span class="community-rank">{{ $member->legacy_rank }}</span>@endif
                                @else
                                    <x-community.rank :rank="$rankResolver->resolve($member)" />
                                @endif
                            </div>

                            <div class="community-member-badges">
                                @foreach($member->badges as $badge)<x-community.badge :badge="$badge" />@endforeach
                                @if(! $isHistoricalProfile && $member->is_legacy && $member->badges->doesntContain('slug', 'miembro-historico'))<span class="community-badge community-badge-legacy"><span aria-hidden="true">🌸</span> Miembro histórico</span>@endif
                            </div>

                            <dl class="community-member-stats">
                                <div>
                                    <dt>Ingreso</dt>
                                    <dd>{{ $isHistoricalProfile ? $member->legacy_joined_at?->translatedFormat('d M Y') : ($member->show_join_date ? optional($member->communityJoinDate())->translatedFormat('d M Y') : 'Fecha privada') }}</dd>
                                </div>
                                <div>
                                    <dt>{{ $isHistoricalProfile ? 'Mensajes archivados' : 'Publicaciones' }}</dt>
                                    <dd>{{ number_format($isHistoricalProfile ? $member->legacy_message_count : $member->community_message_count) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </article>
                @empty
                    <div class="profile-panel community-directory-empty">
                        <span aria-hidden="true">✦</span>
                        <h2>No encontramos miembros</h2>
                        <p>Prueba con otro nombre o elimina alguno de los filtros.</p>
                        <a class="profile-btn profile-btn-soft" href="{{ route('community.members') }}">Ver toda la comunidad</a>
                    </div>
                @endforelse
            </div>

            @if($members->hasPages())
                <div class="community-directory-pagination">{{ $members->links() }}</div>
            @endif
        </div>
    </main>

    <x-footer />
    <script>
        (() => {
            const form = document.querySelector('[data-community-directory-form]');
            if (! form) return;

            const search = form.querySelector('[name="q"]');
            let debounce;

            search?.addEventListener('input', () => {
                window.clearTimeout(debounce);
                debounce = window.setTimeout(() => form.requestSubmit(), 300);
            });

            form.querySelectorAll('select').forEach((field) => field.addEventListener('change', () => form.requestSubmit()));
        })();
    </script>
</body>
</html>
