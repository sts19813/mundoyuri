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
    <div class="card mb-7">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-5">
            <div>
                <div class="text-muted fw-semibold mb-1">Hola, {{ auth()->user()->name }}</div>
                <h2 class="fw-bold text-gray-900 mb-1">
                    @can('moderate content')
                        El catálogo está listo para gestionar
                    @else
                        Comparte nuevas series, películas y episodios
                    @endcan
                </h2>
                <p class="text-muted mb-0">
                    @can('moderate content')
                        Revisa pendientes o continúa organizando la información publicada.
                    @else
                        Tus aportes quedarán pendientes hasta que un moderador los revise.
                    @endcan
                </p>
            </div>
            <div class="d-flex flex-wrap gap-3">
                @can('create series')
                    <a href="{{ route('admin.series.create') }}" class="btn btn-primary">
                        <i class="ki-outline ki-plus fs-2"></i>Nueva serie o película
                    </a>
                @endcan
                @can('create episodes')
                    <a href="{{ route('admin.episodes.create') }}" class="btn btn-light-primary">
                        <i class="ki-outline ki-plus-square fs-2"></i>Nuevo episodio
                    </a>
                @endcan
            </div>
        </div>
    </div>

    @if($siteVisitStats)
        <div class="row g-5 g-xl-8 mb-7">
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fs-6 text-gray-600">Visitantes únicos</div>
                        <i class="ki-outline ki-profile-user fs-2 text-primary"></i>
                    </div>
                    <div class="fs-2hx fw-bold">{{ number_format($siteVisitStats['unique_visitors']) }}</div>
                    <div class="text-muted fs-7">Aproximados por usuario o cookie</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fs-6 text-gray-600">Visitas hoy</div>
                        <i class="ki-outline ki-calendar-tick fs-2 text-success"></i>
                    </div>
                    <div class="fs-2hx fw-bold">{{ number_format($siteVisitStats['visits_today']) }}</div>
                    <div class="text-muted fs-7">Páginas reales del sitio</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fs-6 text-gray-600">Visitas 7 días</div>
                        <i class="ki-outline ki-chart-simple fs-2 text-info"></i>
                    </div>
                    <div class="fs-2hx fw-bold">{{ number_format($siteVisitStats['visits_7_days']) }}</div>
                    <div class="text-muted fs-7">Incluye el día actual</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fs-6 text-gray-600">Visitas 30 días</div>
                        <i class="ki-outline ki-graph-up fs-2 text-warning"></i>
                    </div>
                    <div class="fs-2hx fw-bold">{{ number_format($siteVisitStats['visits_30_days']) }}</div>
                    <div class="text-muted fs-7">Últimos 30 días</div>
                </div></div>
            </div>
        </div>

        <div class="card mb-7">
            <div class="card-header border-0 pt-6">
                <div class="card-title d-flex flex-column">
                    <h3 class="fw-bold mb-1">Visitas últimos 30 días</h3>
                    <span class="text-muted fs-7">Cantidad de visitas registradas por día</span>
                </div>
            </div>
            <div class="card-body pt-2">
                <div id="site-visits-30-days-chart" style="height: 260px;"></div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row g-5">
                    <div class="col-6 col-lg-3">
                        <div class="fs-7 text-muted">Visitas totales</div>
                        <div class="fs-3 fw-bold text-gray-900">{{ number_format($siteVisitStats['total_visits']) }}</div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="fs-7 text-muted">Visitantes anónimos</div>
                        <div class="fs-3 fw-bold text-gray-900">{{ number_format($siteVisitStats['anonymous_visitors']) }}</div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="fs-7 text-muted">Visitantes registrados</div>
                        <div class="fs-3 fw-bold text-gray-900">{{ number_format($siteVisitStats['registered_visitors']) }}</div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="fs-7 text-muted">Usuarios nuevos 30 días</div>
                        <div class="fs-3 fw-bold text-gray-900">{{ number_format($siteVisitStats['new_users_30_days']) }}</div>
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
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const element = document.getElementById('site-visits-30-days-chart');

                if (!element || typeof ApexCharts === 'undefined') {
                    return;
                }

                const renderChart = function () {
                    element.innerHTML = '';

                    const labelColor = KTUtil.getCssVariableValue('--bs-gray-500');
                    const borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');
                    const primaryColor = KTUtil.getCssVariableValue('--bs-primary');
                    const primaryLightColor = KTUtil.getCssVariableValue('--bs-primary-light');

                    new ApexCharts(element, {
                        series: [{
                            name: 'Visitas',
                            data: @js($siteVisitChart['values']),
                        }],
                        chart: {
                            fontFamily: 'inherit',
                            type: 'area',
                            height: 260,
                            toolbar: { show: false },
                        },
                        dataLabels: { enabled: false },
                        fill: {
                            type: 'solid',
                            opacity: 0.12,
                            colors: [primaryColor],
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 3,
                            colors: [primaryColor],
                        },
                        markers: {
                            size: 3,
                            strokeWidth: 2,
                            strokeColors: primaryColor,
                            colors: [primaryLightColor],
                        },
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
                                    return Math.round(value) + ' visitas';
                                },
                            },
                        },
                        colors: [primaryColor],
                        grid: {
                            borderColor: borderColor,
                            strokeDashArray: 4,
                            yaxis: { lines: { show: true } },
                        },
                    }).render();
                };

                renderChart();

                if (typeof KTThemeMode !== 'undefined') {
                    KTThemeMode.on('kt.thememode.change', renderChart);
                }
            });
        </script>
    @endpush
@endif
