@extends('layouts.admin')

@section('title', 'Panel - Mundo Yuri')

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center me-3">
        <h1 class="page-heading fw-bold fs-3 m-0">Panel de contenido</h1>
        <span class="text-muted fs-7">
            @can('moderate content')
                Gestión y moderación del catálogo
            @else
                Tus aportes y actividad en Mundo Yuri
            @endcan
        </span>
    </div>
@endsection

@section('content')
    @php
        $formatDuration = function (int $seconds): string {
            $seconds = max(0, $seconds);
            $hours = intdiv($seconds, 3600);
            $minutes = intdiv($seconds % 3600, 60);

            if ($hours > 0) {
                return $hours.' h'.($minutes > 0 ? ' '.$minutes.' min' : '');
            }

            if ($minutes > 0) {
                return $minutes.' min';
            }

            return $seconds.' s';
        };

        $displayUser = fn ($user): string => $user?->alias ?: $user?->name ?: $user?->email ?: 'Usuario eliminado';
    @endphp

    <div class="card card-flush mb-7 overflow-hidden dashboard-welcome">
        <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-6 py-9 px-7 px-lg-10">
            <div>
                <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-2"><i class="ki-outline ki-chart-line-up fs-3"></i> MUNDO YURI · PULSO DEL SITIO</div>
                <h2 class="fw-bolder text-gray-900 mb-2">Hola, {{ auth()->user()->name }}.</h2>
                <p class="text-muted fs-6 mb-0">
                    @can('moderate content')
                        El resumen combina tráfico, comunidad, reproducciones y cola de moderación.
                    @else
                        Consulta el estado de tus aportes y la actividad del catálogo.
                    @endcan
                </p>
            </div>
            <div class="d-flex flex-wrap gap-3">
                @can('create series')<a href="{{ route('admin.series.create') }}" class="btn btn-primary"><i class="ki-outline ki-plus fs-2"></i>Nueva serie o película</a>@endcan
                @can('create episodes')<a href="{{ route('admin.episodes.create') }}" class="btn btn-light-primary"><i class="ki-outline ki-plus-square fs-2"></i>Nuevo episodio</a>@endcan
            </div>
        </div>
    </div>

    @if($siteVisitStats)
        <div class="row g-5 g-xl-8 mb-7">
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 dashboard-stat-card"><div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4"><div class="dashboard-stat-icon bg-light-success"><i class="ki-outline ki-people fs-2 text-success"></i></div><span class="badge badge-light-success">en vivo</span></div>
                    <div class="fs-2hx fw-bolder">{{ number_format($siteVisitStats['active_now']) }}</div>
                    <div class="fw-bold text-gray-800">Personas en el sitio</div>
                    <div class="text-muted fs-7 mt-1">{{ number_format($siteVisitStats['active_registered']) }} registradas · {{ number_format($siteVisitStats['active_anonymous']) }} anónimas · últimos 15 min</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 dashboard-stat-card"><div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4"><div class="dashboard-stat-icon bg-light-primary"><i class="ki-outline ki-calendar-tick fs-2 text-primary"></i></div><span @class(['badge', 'badge-light-success' => $siteVisitStats['today_change'] >= 0, 'badge-light-danger' => $siteVisitStats['today_change'] < 0])>{{ $siteVisitStats['today_change'] >= 0 ? '+' : '' }}{{ $siteVisitStats['today_change'] }}%</span></div>
                    <div class="fs-2hx fw-bolder">{{ number_format($siteVisitStats['visits_today']) }}</div>
                    <div class="fw-bold text-gray-800">Páginas vistas hoy</div>
                    <div class="text-muted fs-7 mt-1">Comparado con el día anterior</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 dashboard-stat-card"><div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4"><div class="dashboard-stat-icon bg-light-info"><i class="ki-outline ki-profile-user fs-2 text-info"></i></div><i class="ki-outline ki-chart-line-up fs-2 text-gray-400"></i></div>
                    <div class="fs-2hx fw-bolder">{{ number_format($siteVisitStats['unique_visitors']) }}</div>
                    <div class="fw-bold text-gray-800">Visitantes únicos</div>
                    <div class="text-muted fs-7 mt-1">Identificados por cuenta o cookie</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 dashboard-stat-card"><div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4"><div class="dashboard-stat-icon bg-light-warning"><i class="ki-outline ki-graph-up fs-2 text-warning"></i></div><span class="badge badge-light-warning">30 días</span></div>
                    <div class="fs-2hx fw-bolder">{{ number_format($siteVisitStats['visits_30_days']) }}</div>
                    <div class="fw-bold text-gray-800">Visitas recientes</div>
                    <div class="text-muted fs-7 mt-1">{{ number_format($siteVisitStats['anonymous_visitors']) }} anónimos · {{ number_format($siteVisitStats['registered_visitors']) }} con cuenta</div>
                </div></div>
            </div>
        </div>

        <div class="row g-5 g-xl-8 mb-7">
            <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header border-0 pt-6">
                <div class="card-title d-flex flex-column">
                    <h3 class="fw-bold mb-1">Tráfico de los últimos 30 días</h3>
                    <span class="text-muted fs-7">Páginas vistas frente a personas únicas; los datos se actualizan al cargar una página.</span>
                </div>
            </div>
            <div class="card-body pt-2">
                <div id="site-visits-30-days-chart" style="height: 290px;"></div>
            </div>
        </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0 pt-6"><div class="card-title d-flex flex-column"><h3 class="fw-bold mb-1">Actividad de hoy</h3><span class="text-muted fs-7">Páginas vistas por hora</span></div></div>
                    <div class="card-body pt-2"><div id="site-visits-today-chart" style="height: 205px;"></div><div class="separator separator-dashed my-4"></div><div class="d-flex justify-content-between"><div><div class="text-muted fs-8">Horas registradas</div><div class="fw-bold fs-5">{{ number_format($siteVisitStats['tracked_hours']) }} h</div></div><div class="text-end"><div class="text-muted fs-8">Nuevos usuarios</div><div class="fw-bold fs-5">{{ number_format($siteVisitStats['new_users_30_days']) }}</div></div></div></div>
                </div>
            </div>
        </div>

        <div class="row g-5 g-xl-8 mb-7">
            <div class="col-xl-7"><div class="card h-100"><div class="card-header border-0 pt-6"><div class="card-title d-flex flex-column"><h3 class="fw-bold mb-1">Páginas que atraen más visitas</h3><span class="text-muted fs-7">Últimos 30 días</span></div></div><div class="card-body pt-2"><div class="d-flex flex-column gap-4">@forelse($topVisitedPages as $page)<div><div class="d-flex justify-content-between gap-3 mb-2"><a href="{{ url($page->path) }}" target="_blank" rel="noopener" class="fw-bold text-gray-800 text-hover-primary text-truncate">{{ $page->path }}</a><span class="text-muted text-nowrap">{{ number_format($page->visits) }} vistas</span></div><div class="progress h-7px bg-light-primary"><div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, max(4, ($topVisitedPages->first()->visits ?? 1) > 0 ? ($page->visits / $topVisitedPages->first()->visits) * 100 : 0)) }}%"></div></div><div class="text-muted fs-8 mt-1">{{ number_format($page->visitors) }} visitantes únicos</div></div>@empty<div class="text-center text-muted py-8">Aún no hay páginas registradas.</div>@endforelse</div></div></div></div>
            <div class="col-xl-5"><div class="card h-100"><div class="card-header border-0 pt-6"><div class="card-title d-flex flex-column"><h3 class="fw-bold mb-1">Comunidad y contenido</h3><span class="text-muted fs-7">Interacción registrada en 30 días</span></div></div><div class="card-body pt-2"><div class="row g-4">@foreach([['Reproducciones iniciadas', $contentMomentum['episode_starts'], 'ki-video'], ['Episodios terminados', $contentMomentum['episode_completions'], 'ki-check-circle'], ['Comentarios nuevos', $contentMomentum['comments'], 'ki-message-text-2'], ['Registros nuevos', $contentMomentum['new_users'], 'ki-profile-user']] as [$label, $value, $icon])<div class="col-6"><div class="border border-dashed border-gray-300 rounded p-4 h-100"><i class="ki-outline {{ $icon }} fs-2 text-primary mb-3 d-block"></i><div class="fs-3 fw-bolder">{{ number_format($value) }}</div><div class="text-muted fs-8">{{ $label }}</div></div></div>@endforeach</div><div class="separator separator-dashed my-5"></div><div class="d-flex align-items-center gap-3"><span class="badge badge-light-success">Privacidad</span><span class="text-muted fs-8">Los anónimos se cuentan con una cookie aleatoria; no se almacenan IP ni datos personales.</span></div></div></div></div>
        </div>

        <div class="row g-5 g-xl-8 mb-7">
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title d-flex flex-column">
                            <h3 class="fw-bold mb-1">Usuarios viendo ahora</h3>
                            <span class="text-muted fs-7">Página actual detectada por latidos del navegador</span>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed gy-4 mb-0">
                                <thead>
                                    <tr class="text-muted fw-bold fs-7 text-uppercase">
                                        <th>Usuario</th>
                                        <th>Está viendo</th>
                                        <th class="text-end">Último latido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activePresenceSessions as $session)
                                        <tr>
                                            <td>
                                                @if($session->user)
                                                    <a href="{{ route('admin.users.show', $session->user) }}" class="text-gray-900 text-hover-primary fw-bold">
                                                        {{ $displayUser($session->user) }}
                                                    </a>
                                                    <div class="text-muted fs-7">{{ $session->user->email }}</div>
                                                @else
                                                    <span class="text-muted">Usuario eliminado</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ url($session->current_path) }}" class="text-gray-900 text-hover-primary fw-semibold" target="_blank" rel="noopener">
                                                    {{ \Illuminate\Support\Str::limit($session->current_title ?: $session->current_path, 46) }}
                                                </a>
                                                <div class="text-muted fs-7">{{ \Illuminate\Support\Str::limit($session->current_path, 54) }}</div>
                                            </td>
                                            <td class="text-end text-muted fs-7">{{ $session->last_seen_at?->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-8">No hay usuarios activos ahora mismo.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title d-flex flex-column">
                            <h3 class="fw-bold mb-1">Usuarios con más tiempo</h3>
                            <span class="text-muted fs-7">Horas acumuladas dentro del portal</span>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed gy-4 mb-0">
                                <thead>
                                    <tr class="text-muted fw-bold fs-7 text-uppercase">
                                        <th>Usuario</th>
                                        <th class="text-end">Tiempo total</th>
                                        <th class="text-end">Última actividad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topTimeUsers as $timeUser)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.users.show', $timeUser) }}" class="text-gray-900 text-hover-primary fw-bold">
                                                    {{ $displayUser($timeUser) }}
                                                </a>
                                                <div class="text-muted fs-7">{{ $timeUser->email }}</div>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge badge-light-primary fs-7">{{ $formatDuration((int) $timeUser->total_presence_seconds) }}</span>
                                            </td>
                                            <td class="text-end text-muted fs-7">
                                                {{ \Illuminate\Support\Carbon::parse($timeUser->last_presence_at)->diffForHumans() }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-8">Aún no hay tiempo acumulado.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-5 g-xl-8 mb-7">
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title d-flex flex-column">
                            <h3 class="fw-bold mb-1">Últimas páginas vistas</h3>
                            <span class="text-muted fs-7">Historial reciente de usuarios registrados</span>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed gy-4 mb-0">
                                <thead>
                                    <tr class="text-muted fw-bold fs-7 text-uppercase">
                                        <th>Usuario</th>
                                        <th>Página</th>
                                        <th class="text-end">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentUserVisits as $visit)
                                        <tr>
                                            <td>
                                                @if($visit->user)
                                                    <a href="{{ route('admin.users.show', $visit->user) }}" class="text-gray-900 text-hover-primary fw-bold">
                                                        {{ $displayUser($visit->user) }}
                                                    </a>
                                                    <div class="text-muted fs-7">{{ $visit->user->email }}</div>
                                                @else
                                                    <span class="text-muted">Usuario eliminado</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ url($visit->path) }}" class="text-gray-900 text-hover-primary fw-semibold" target="_blank" rel="noopener">
                                                    {{ \Illuminate\Support\Str::limit($visit->path, 54) }}
                                                </a>
                                            </td>
                                            <td class="text-end text-muted fs-7">{{ $visit->visited_at?->format('d/m H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-8">Aún no hay visitas de usuarios registrados.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title d-flex flex-column">
                            <h3 class="fw-bold mb-1">Progreso de episodios</h3>
                            <span class="text-muted fs-7">Qué empezó, dónde va y qué ya completó cada usuario</span>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed gy-4 mb-0">
                                <thead>
                                    <tr class="text-muted fw-bold fs-7 text-uppercase">
                                        <th>Usuario</th>
                                        <th>Episodio</th>
                                        <th class="text-end">Avance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentWatchProgress as $progress)
                                        <tr>
                                            <td>
                                                @if($progress->user)
                                                    <a href="{{ route('admin.users.show', $progress->user) }}" class="text-gray-900 text-hover-primary fw-bold">
                                                        {{ $displayUser($progress->user) }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Usuario eliminado</span>
                                                @endif
                                                <div class="text-muted fs-7">{{ $progress->last_watched_at?->diffForHumans() }}</div>
                                            </td>
                                            <td>
                                                @if($progress->episode)
                                                    <a href="{{ route('admin.episodes.show', $progress->episode) }}" class="text-gray-900 text-hover-primary fw-semibold">
                                                        {{ \Illuminate\Support\Str::limit($progress->episode->title, 42) }}
                                                    </a>
                                                    <div class="text-muted fs-7">{{ $progress->episode->series?->title ?: 'Sin serie' }}</div>
                                                @else
                                                    <span class="text-muted">Episodio eliminado</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <span @class(['badge fs-7', 'badge-light-success' => $progress->completed, 'badge-light-warning' => ! $progress->completed])>
                                                    {{ $progress->completed ? 'Completo' : number_format((float) $progress->progress_percent, 0).'%' }}
                                                </span>
                                                <div class="text-muted fs-8">{{ $formatDuration((int) $progress->position_seconds) }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-8">Aún no hay progreso de episodios.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($currentWatchingEpisodes->isNotEmpty())
                            <div class="separator separator-dashed my-6"></div>
                            <div class="fw-bold text-gray-900 mb-3">Viendo episodio ahora</div>
                            <div class="d-flex flex-column gap-3">
                                @foreach($currentWatchingEpisodes as $progress)
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ $displayUser($progress->user) }}</div>
                                            <div class="text-muted fs-7">
                                                {{ \Illuminate\Support\Str::limit($progress->episode?->title ?: 'Episodio eliminado', 54) }}
                                            </div>
                                        </div>
                                        <span class="badge badge-light-success">en vivo</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-5 g-xl-8">
        @if($stats['users'] !== null)
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="fs-6 text-gray-600">Usuarios</div>
                    <div class="fs-2hx fw-bold">{{ $stats['users'] }}</div>
                </div></div>
            </div>
        @endif
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="fs-6 text-gray-600">{{ auth()->user()->can('moderate content') ? 'Títulos totales' : 'Mis títulos' }}</div>
                <div class="fs-2hx fw-bold">{{ $stats['series'] }}</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="fs-6 text-gray-600">{{ auth()->user()->can('moderate content') ? 'Episodios totales' : 'Mis episodios' }}</div>
                <div class="fs-2hx fw-bold">{{ $stats['episodes'] }}</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="fs-6 text-gray-600">Comentarios</div>
                <div class="fs-2hx fw-bold">{{ $stats['comments'] }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-5 g-xl-8 mt-1">
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fs-6 text-gray-600">Títulos pendientes</div>
                    <div class="fs-2hx fw-bold text-warning">{{ $stats['pending_series'] }}</div>
                </div>
                <i class="ki-outline ki-screen fs-3x text-warning"></i>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fs-6 text-gray-600">Episodios pendientes</div>
                    <div class="fs-2hx fw-bold text-warning">{{ $stats['pending_episodes'] }}</div>
                </div>
                <i class="ki-outline ki-subtitle fs-3x text-warning"></i>
            </div></div>
        </div>
    </div>

    <div class="row g-5 g-xl-8 mt-3">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header border-0 pt-6">
                    <div class="card-title d-flex flex-column">
                        <h3 class="fw-bold mb-1">Episodios con más vistas</h3>
                        <span class="text-muted fs-7">Ranking por reproducciones registradas</span>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed gy-4 mb-0">
                            <thead>
                                <tr class="text-muted fw-bold fs-7 text-uppercase">
                                    <th>Episodio</th>
                                    <th class="text-end">Vistas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostViewedEpisodes as $episode)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.episodes.show', $episode) }}" class="text-gray-900 text-hover-primary fw-bold">
                                                {{ $episode->title }}
                                            </a>
                                            <div class="text-muted fs-7">
                                                {{ $episode->series?->title ?: 'Sin serie' }} · T{{ $episode->season_number }} E{{ $episode->episode_number }}
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge badge-light-primary fs-7">{{ number_format($episode->views_count) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-8">Aún no hay episodios con vistas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header border-0 pt-6">
                    <div class="card-title d-flex flex-column">
                        <h3 class="fw-bold mb-1">Series y películas con más vistas</h3>
                        <span class="text-muted fs-7">Suma de las vistas de todos sus episodios</span>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed gy-4 mb-0">
                            <thead>
                                <tr class="text-muted fw-bold fs-7 text-uppercase">
                                    <th>Título</th>
                                    <th class="text-end">Vistas totales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostViewedSeries as $series)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.series.show', $series) }}" class="text-gray-900 text-hover-primary fw-bold">
                                                {{ $series->title }}
                                            </a>
                                            <div class="text-muted fs-7">
                                                {{ $series->content_type === 'movie' ? 'Película' : 'Serie' }}
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge badge-light-success fs-7">{{ number_format((int) $series->total_views) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-8">Aún no hay series o películas con vistas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('moderate content')
        <div class="card mt-8">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="fw-bold fs-4 text-gray-900">Cola de moderación</div>
                    <div class="text-muted">Valida aportes antes de publicarlos.</div>
                </div>
                <a href="{{ route('admin.moderation.index') }}" class="btn btn-primary">Revisar pendientes</a>
            </div>
        </div>
    @endcan
@endsection

@if($siteVisitChart)
    @push('styles')
        <style>
            .dashboard-welcome { background: linear-gradient(116deg, var(--bs-body-bg) 0%, var(--bs-primary-light) 155%); border: 1px solid color-mix(in srgb, var(--bs-primary) 22%, var(--bs-border-color)); }
            .dashboard-stat-card { transition: transform .2s ease, box-shadow .2s ease; }
            .dashboard-stat-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1.25rem rgba(0, 0, 0, .08); }
            .dashboard-stat-icon { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; }
        </style>
    @endpush
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const trafficElement = document.getElementById('site-visits-30-days-chart');
                const todayElement = document.getElementById('site-visits-today-chart');

                if (typeof ApexCharts === 'undefined') {
                    return;
                }

                const renderChart = function () {
                    const labelColor = KTUtil.getCssVariableValue('--bs-gray-500');
                    const borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');
                    const primaryColor = KTUtil.getCssVariableValue('--bs-primary');
                    const primaryLightColor = KTUtil.getCssVariableValue('--bs-primary-light');

                    if (trafficElement) {
                        trafficElement.innerHTML = '';
                        new ApexCharts(trafficElement, {
                        series: [
                            { name: 'Páginas vistas', data: @js($siteVisitChart['visits']) },
                            { name: 'Personas únicas', data: @js($siteVisitChart['visitors']) },
                        ],
                        chart: {
                            fontFamily: 'inherit',
                            type: 'area',
                            height: 290,
                            toolbar: { show: false },
                        },
                        dataLabels: { enabled: false },
                        fill: {
                            type: 'gradient',
                            opacity: 0.22,
                            gradient: { opacityFrom: .28, opacityTo: .02 },
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 3,
                        },
                        markers: { size: 0, hover: { size: 5 } },
                        xaxis: {
                            categories: @js($siteVisitChart['labels']),
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: {
                                style: {
                                    colors: labelColor,
                                    fontSize: '12px',
                                },
                            },
                            tooltip: { enabled: false },
                        },
                        yaxis: {
                            min: 0,
                            forceNiceScale: true,
                            labels: {
                                style: {
                                    colors: labelColor,
                                    fontSize: '12px',
                                },
                                formatter: function (value) {
                                    return Math.round(value);
                                },
                            },
                        },
                        tooltip: {
                            style: { fontSize: '12px' },
                            y: {
                                formatter: function (value) {
                                    return Math.round(value) + ' personas';
                                },
                            },
                        },
                        legend: { position: 'top', horizontalAlign: 'left', fontSize: '12px', labels: { colors: labelColor } },
                        colors: [primaryColor, '#50CD89'],
                        grid: {
                            borderColor: borderColor,
                            strokeDashArray: 4,
                            yaxis: { lines: { show: true } },
                        },
                        }).render();
                    }

                    if (todayElement) {
                        todayElement.innerHTML = '';
                        new ApexCharts(todayElement, {
                            series: [{ name: 'Páginas vistas', data: @js($todayTrafficChart['values']) }],
                            chart: { fontFamily: 'inherit', type: 'bar', height: 205, toolbar: { show: false }, sparkline: { enabled: false } },
                            plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
                            dataLabels: { enabled: false },
                            xaxis: { categories: @js($todayTrafficChart['labels']), labels: { style: { colors: labelColor, fontSize: '10px' }, rotate: -45 }, axisBorder: { show: false }, axisTicks: { show: false } },
                            yaxis: { labels: { style: { colors: labelColor, fontSize: '11px' }, formatter: value => Math.round(value) }, min: 0, forceNiceScale: true },
                            tooltip: { y: { formatter: value => Math.round(value) + ' vistas' } },
                            fill: { opacity: 1, colors: [primaryColor] },
                            grid: { borderColor: borderColor, strokeDashArray: 4 },
                        }).render();
                    }
                };

                renderChart();

                if (typeof KTThemeMode !== 'undefined') {
                    KTThemeMode.on('kt.thememode.change', renderChart);
                }
            });
        </script>
    @endpush
@endif
