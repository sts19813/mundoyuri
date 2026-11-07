@extends('layouts.admin')

@section('title', 'Dashboard - Mundo Yuri')

@section('toolbar')
    <div class="page-title"><h1 class="page-heading fw-bold fs-3 m-0">Dashboard</h1><span class="text-muted fs-7">Una mirada a Mundo Yuri</span></div>
@endsection

@section('content')
@php
    $displayUser = fn ($person): string => $person?->alias ?: $person?->name ?: 'Usuario eliminado';
    $formatDuration = function (int $seconds): string {
        $seconds = max(0, $seconds);
        return intdiv($seconds, 3600).' h '.intdiv($seconds % 3600, 60).' min';
    };
@endphp
<div class="my-dashboard">
    <div class="dash-hero mb-7">
        <div><div class="dash-eyebrow">MUNDO YURI / PANORAMA GENERAL</div><h2>Tu comunidad, en perspectiva.</h2><p>Hola, {{ auth()->user()->name }}. Aquí tienes el pulso de tu sitio.</p></div>
        <div class="d-flex flex-wrap gap-3">
            @can('create series')<a href="{{ route('admin.series.create') }}" class="btn btn-primary">+ Nueva serie o película</a>@endcan
            @can('create episodes')<a href="{{ route('admin.episodes.create') }}" class="btn btn-light">+ Nuevo episodio</a>@endcan
        </div>
    </div>

    @if($siteVisitStats)
    <nav class="dash-nav mb-8" aria-label="Secciones del dashboard"><a href="#resumen">Resumen</a><a href="#trafico">Tráfico</a><a href="#comunidad">Comunidad</a><a href="#contenido">Contenido</a><a href="#actividad">Actividad</a></nav>

    <section id="resumen" class="dash-section">
        <div class="dash-section-heading"><div><span>01 / PANORAMA</span><h2>El sitio ahora</h2><p>Presencia reciente y tráfico de hoy.</p></div><span class="dash-live"><span></span> Últimos 15 min</span></div>
        <div class="dash-metrics">
            <article class="dash-metric dash-metric-accent"><div class="dash-metric-top"><span>Personas activas</span><i class="ki-outline ki-people"></i></div><strong>{{ number_format($siteVisitStats['active_now']) }}</strong><small>{{ number_format($siteVisitStats['active_registered']) }} con cuenta · {{ number_format($siteVisitStats['active_anonymous']) }} anónimas</small></article>
            <article class="dash-metric"><div class="dash-metric-top"><span>Páginas vistas hoy</span><i class="ki-outline ki-chart-line-up"></i></div><strong>{{ number_format($siteVisitStats['visits_today']) }}</strong><small>Ayer: {{ number_format($siteVisitStats['yesterday_visits']) }} en todo el día</small></article>
            <article class="dash-metric"><div class="dash-metric-top"><span>Visitas · 7 días</span><i class="ki-outline ki-calendar-tick"></i></div><strong>{{ number_format($siteVisitStats['visits_7_days']) }}</strong><small>Incluye hoy</small></article>
            <article class="dash-metric"><div class="dash-metric-top"><span>Visitas · 30 días</span><i class="ki-outline ki-graph-up"></i></div><strong>{{ number_format($siteVisitStats['visits_30_days']) }}</strong><small>Incluye hoy</small></article>
        </div>
    </section>

    <section id="trafico" class="dash-section">
        <div class="dash-section-heading"><div><span>02 / AUDIENCIA</span><h2>Cómo crece el tráfico</h2><p>Visitas registradas en páginas públicas, agrupadas por fecha.</p></div></div>
        <div class="dash-grid dash-grid-traffic">
            <article class="dash-card"><div class="dash-card-heading"><div><h3>Tráfico de los últimos 30 días</h3><p>Visitas y visitantes únicos por día</p></div><span class="dash-period">30 DÍAS</span></div><div id="site-visits-30-days-chart" class="dash-chart" aria-label="Gráfica de tráfico de los últimos 30 días"></div><noscript>Activa JavaScript para ver la gráfica.</noscript><div class="dash-inline-stats"><div><small>Visitas totales</small><strong>{{ number_format($siteVisitStats['total_visits']) }}</strong><span>histórico</span></div><div><small>Visitantes únicos</small><strong>{{ number_format($siteVisitStats['unique_visitors_30_days']) }}</strong><span>últimos 30 días</span></div><div><small>Visitantes anónimos</small><strong>{{ number_format($siteVisitStats['anonymous_visitors_30_days']) }}</strong><span>últimos 30 días</span></div><div><small>Visitantes con cuenta</small><strong>{{ number_format($siteVisitStats['registered_visitors_30_days']) }}</strong><span>últimos 30 días</span></div></div><p class="dash-note">Las visitas a una misma página se agrupan durante 30 minutos. Una persona puede contar como anónima y con cuenta si inicia sesión.</p></article>
            <article class="dash-card"><div class="dash-card-heading"><div><h3>Actividad de hoy</h3><p>Visitas por hora · hora del sitio</p></div><span class="dash-period">HOY</span></div><div id="site-visits-today-chart" class="dash-chart-small" aria-label="Gráfica de visitas por hora"></div><div class="dash-callout"><strong>{{ number_format($siteVisitStats['visits_today']) }}</strong><span>visitas registradas hoy</span></div></article>
        </div>
        <div class="dash-grid dash-grid-even mt-6">
            <article class="dash-card"><div class="dash-card-heading"><div><h3>Páginas que atraen más visitas</h3><p>Últimos 30 días</p></div></div><div class="dash-bars">@forelse($topVisitedPages as $page)<div><div class="d-flex justify-content-between gap-3"><a href="{{ url($page->path) }}" target="_blank" rel="noopener" class="text-truncate">{{ $page->path }}</a><strong class="text-nowrap">{{ number_format($page->visits) }}</strong></div><div class="dash-bar"><span style="width: {{ max(2, min(100, $page->visits / max(1, $topVisitedPages->first()->visits) * 100)) }}%"></span></div><small>{{ number_format($page->visitors) }} visitantes únicos</small></div>@empty<p class="dash-empty">Todavía no hay visitas registradas en este periodo.</p>@endforelse</div></article>
            <article class="dash-card"><div class="dash-card-heading"><div><h3>Visitantes anónimos activos</h3><p>Última página detectada por navegador · últimos 15 minutos</p></div><span class="dash-period">{{ number_format($siteVisitStats['active_anonymous']) }} ACTIVOS</span></div><div class="dash-list">@forelse($anonymousPages as $page)<div><span class="text-truncate">{{ $page->path }}</span><strong>{{ number_format($page->visitors) }}</strong></div>@empty<p class="dash-empty">No hay visitantes anónimos detectados en este momento.</p>@endforelse</div><p class="dash-note">Es una estimación por navegador; tener una página abierta no confirma que se esté reproduciendo un video.</p></article>
        </div>
    </section>

    <section id="comunidad" class="dash-section">
        <div class="dash-section-heading"><div><span>03 / COMUNIDAD</span><h2>Personas y participación</h2><p>Crecimiento reciente y actividad de miembros.</p></div></div>
        <div class="dash-metrics dash-metrics-three"><article class="dash-metric"><div class="dash-metric-top"><span>Nuevos registros</span><i class="ki-outline ki-profile-user"></i></div><strong>{{ number_format($siteVisitStats['new_users_30_days']) }}</strong><small>Últimos 30 días</small></article><article class="dash-metric"><div class="dash-metric-top"><span>Comentarios nuevos</span><i class="ki-outline ki-message-text-2"></i></div><strong>{{ number_format($contentMomentum['comments']) }}</strong><small>Últimos 30 días</small></article><article class="dash-metric"><div class="dash-metric-top"><span>Tiempo registrado</span><i class="ki-outline ki-time"></i></div><strong>{{ number_format($siteVisitStats['tracked_hours']) }} <em>h</em></strong><small>Histórico · usuarios con cuenta</small></article></div>
        <div class="dash-grid dash-grid-even mt-6">
            <article class="dash-card"><div class="dash-card-heading"><div><h3>Usuarios activos ahora</h3><p>Último pulso en los últimos 15 minutos</p></div></div><div class="table-responsive"><table class="table align-middle table-row-dashed"><thead><tr><th>Usuario</th><th>Página</th><th class="text-end">Actividad</th></tr></thead><tbody>@forelse($activePresenceSessions as $session)<tr><td>@if($session->user)<a href="{{ route('admin.users.show', $session->user) }}">{{ $displayUser($session->user) }}</a>@else Usuario eliminado @endif</td><td>{{ \Illuminate\Support\Str::limit($session->current_title ?: $session->current_path, 42) }}</td><td class="text-end text-muted text-nowrap">{{ $session->last_seen_at?->diffForHumans() }}</td></tr>@empty<tr><td colspan="3" class="dash-empty">No hay usuarios registrados activos.</td></tr>@endforelse</tbody></table></div></article>
            <article class="dash-card"><div class="dash-card-heading"><div><h3>Usuarios con más tiempo</h3><p>Tiempo histórico acumulado en el portal</p></div></div><div class="table-responsive"><table class="table align-middle table-row-dashed"><thead><tr><th>Usuario</th><th class="text-end">Tiempo</th><th class="text-end">Última actividad</th></tr></thead><tbody>@forelse($topTimeUsers as $timeUser)<tr><td><a href="{{ route('admin.users.show', $timeUser) }}">{{ $displayUser($timeUser) }}</a></td><td class="text-end text-nowrap">{{ $formatDuration((int) $timeUser->total_presence_seconds) }}</td><td class="text-end text-muted text-nowrap">{{ \Illuminate\Support\Carbon::parse($timeUser->last_presence_at)->diffForHumans() }}</td></tr>@empty<tr><td colspan="3" class="dash-empty">Aún no hay tiempo acumulado.</td></tr>@endforelse</tbody></table></div></article>
        </div>
    </section>

    <section id="contenido" class="dash-section">
        <div class="dash-section-heading"><div><span>04 / CATÁLOGO</span><h2>El contenido que conecta</h2><p>Inventario y rendimiento histórico.</p></div></div>
        <div class="dash-metrics"><article class="dash-metric"><div class="dash-metric-top"><span>Títulos</span><i class="ki-outline ki-screen"></i></div><strong>{{ number_format($stats['series']) }}</strong><small>En el catálogo</small></article><article class="dash-metric"><div class="dash-metric-top"><span>Episodios</span><i class="ki-outline ki-video"></i></div><strong>{{ number_format($stats['episodes']) }}</strong><small>En el catálogo</small></article><article class="dash-metric"><div class="dash-metric-top"><span>Usuarios</span><i class="ki-outline ki-people"></i></div><strong>{{ $stats['users'] !== null ? number_format($stats['users']) : '—' }}</strong><small>Cuentas registradas</small></article><article class="dash-metric"><div class="dash-metric-top"><span>Comentarios</span><i class="ki-outline ki-message-text-2"></i></div><strong>{{ number_format($stats['comments']) }}</strong><small>Histórico</small></article></div>
        <div class="dash-grid dash-grid-even mt-6">
            <article class="dash-card"><div class="dash-card-heading"><div><h3>Series y películas más vistas</h3><p>Vistas históricas sumadas de sus episodios</p></div></div><div class="dash-ranking">@forelse($mostViewedSeries as $series)<a href="{{ route('admin.series.show', $series) }}"><span>{{ $loop->iteration }}</span><div>{{ $series->title }}<small>{{ $series->content_type === 'movie' ? 'Película' : 'Serie' }}</small></div><strong>{{ number_format((int) $series->total_views) }}</strong></a>@empty<p class="dash-empty">Todavía no hay títulos con vistas.</p>@endforelse</div></article>
            <article class="dash-card"><div class="dash-card-heading"><div><h3>Episodios más vistos</h3><p>Vistas históricas registradas</p></div></div><div class="dash-ranking">@forelse($mostViewedEpisodes as $episode)<a href="{{ route('admin.episodes.show', $episode) }}"><span>{{ $loop->iteration }}</span><div>{{ $episode->title }}<small>{{ $episode->series?->title ?: 'Sin serie' }} · T{{ $episode->season_number }} E{{ $episode->episode_number }}</small></div><strong>{{ number_format($episode->views_count) }}</strong></a>@empty<p class="dash-empty">Todavía no hay episodios con vistas.</p>@endforelse</div></article>
        </div>
    </section>

    <section id="actividad" class="dash-section">
        <div class="dash-section-heading"><div><span>05 / DETALLE</span><h2>Actividad reciente</h2><p>Información disponible de usuarios registrados.</p></div></div>
        <div class="dash-grid dash-grid-even">
            <article class="dash-card"><div class="dash-card-heading"><div><h3>Últimas páginas vistas</h3><p>Visitas recientes de miembros</p></div></div><div class="table-responsive"><table class="table align-middle table-row-dashed"><thead><tr><th>Usuario</th><th>Página</th><th class="text-end">Fecha</th></tr></thead><tbody>@forelse($recentUserVisits as $visit)<tr><td>@if($visit->user)<a href="{{ route('admin.users.show', $visit->user) }}">{{ $displayUser($visit->user) }}</a>@else Usuario eliminado @endif</td><td>{{ \Illuminate\Support\Str::limit($visit->path, 44) }}</td><td class="text-end text-nowrap">{{ $visit->visited_at?->format('d/m H:i') }}</td></tr>@empty<tr><td colspan="3" class="dash-empty">No hay visitas recientes.</td></tr>@endforelse</tbody></table></div></article>
            <article class="dash-card"><div class="dash-card-heading"><div><h3>Progreso de episodios</h3><p>Últimos avances guardados</p></div></div><div class="table-responsive"><table class="table align-middle table-row-dashed"><thead><tr><th>Usuario</th><th>Episodio</th><th class="text-end">Avance</th></tr></thead><tbody>@forelse($recentWatchProgress as $progress)<tr><td>{{ $displayUser($progress->user) }}<small class="d-block text-muted">{{ $progress->last_watched_at?->diffForHumans() }}</small></td><td>@if($progress->episode)<a href="{{ route('admin.episodes.show', $progress->episode) }}">{{ \Illuminate\Support\Str::limit($progress->episode->title, 35) }}</a><small class="d-block text-muted">{{ $progress->episode->series?->title }}</small>@else Episodio eliminado @endif</td><td class="text-end text-nowrap">{{ $progress->completed ? 'Completo' : number_format((float) $progress->progress_percent, 0).'%' }}</td></tr>@empty<tr><td colspan="3" class="dash-empty">Todavía no hay progreso guardado.</td></tr>@endforelse</tbody></table></div><p class="dash-note">El progreso se conserva para usuarios registrados y fuentes compatibles.</p>@if($currentWatchingEpisodes->isNotEmpty())<div class="dash-note"><strong>Progreso actualizado en los últimos 15 minutos</strong><div class="dash-list">@foreach($currentWatchingEpisodes as $progress)<div><span>{{ $displayUser($progress->user) }} · {{ \Illuminate\Support\Str::limit($progress->episode?->title ?: 'Episodio eliminado', 45) }}</span><span>{{ $progress->last_watched_at?->diffForHumans() }}</span></div>@endforeach</div></div>@endif</article>
        </div>
    </section>
    @else
        <section class="dash-section"><div class="dash-section-heading"><div><span>MIS APORTES</span><h2>Tu actividad</h2><p>Estado de tus contribuciones al catálogo.</p></div></div><div class="dash-metrics"><article class="dash-metric"><span>Mis títulos</span><strong>{{ number_format($stats['series']) }}</strong></article><article class="dash-metric"><span>Mis episodios</span><strong>{{ number_format($stats['episodes']) }}</strong></article><article class="dash-metric"><span>Títulos pendientes</span><strong>{{ number_format($stats['pending_series']) }}</strong></article><article class="dash-metric"><span>Episodios pendientes</span><strong>{{ number_format($stats['pending_episodes']) }}</strong></article></div></section>
    @endif
</div>
@endsection

@push('styles')
<style>
.my-dashboard { --dash-card:var(--bs-body-bg); --dash-muted:var(--bs-gray-600); --dash-line:var(--bs-border-color); max-width:1680px; margin:auto; }
.dash-hero { position:relative; overflow:hidden; border-radius:20px; padding:34px 38px; display:flex; align-items:center; justify-content:space-between; gap:24px; background:linear-gradient(115deg,#25142f,#512045 65%,#ad3069); color:#fff; }
.dash-hero:after { content:''; position:absolute; width:250px; height:250px; right:25%; top:-170px; border-radius:50%; border:45px solid rgba(255,255,255,.05); pointer-events:none; }
.dash-hero h2 { color:#fff; font-size:clamp(1.6rem,2.5vw,2.3rem); font-weight:750; margin:10px 0; }.dash-hero p { color:#e1c9dd; margin:0; font-size:1.06rem; }.dash-eyebrow { color:#ff9bc8; font-size:.73rem; font-weight:800; letter-spacing:.12em; }
.dash-nav { display:flex; gap:8px; overflow:auto; padding-bottom:4px; }.dash-nav a { flex:none; color:var(--dash-muted); padding:10px 16px; border-radius:10px; font-weight:700; }.dash-nav a:hover { background:var(--bs-primary-light); color:var(--bs-primary); }
.dash-section { scroll-margin-top:100px; margin-bottom:50px; }.dash-section-heading { display:flex; align-items:end; justify-content:space-between; gap:15px; margin-bottom:20px; }.dash-section-heading span:not(.dash-live) { color:var(--bs-primary); font-weight:800; font-size:.68rem; letter-spacing:.13em; }.dash-section-heading h2 { color:var(--bs-gray-900); font-size:1.5rem; font-weight:750; margin:6px 0; }.dash-section-heading p,.dash-card-heading p { color:var(--dash-muted); margin:0; }.dash-live { border-radius:999px; background:var(--bs-success-light); color:var(--bs-success); font-size:.75rem; font-weight:700; padding:8px 12px; white-space:nowrap; }.dash-live span { display:inline-block; width:8px; height:8px; background:var(--bs-success); border-radius:50%; margin-right:5px; }
.dash-metrics { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:18px; }.dash-metrics-three { grid-template-columns:repeat(3,minmax(0,1fr)); }.dash-metric,.dash-card { background:var(--dash-card); border:1px solid var(--dash-line); border-radius:17px; padding:24px; min-width:0; }.dash-metric { display:flex; flex-direction:column; min-height:150px; }.dash-metric-accent { border-top:3px solid var(--bs-success); }.dash-metric-top { display:flex; align-items:center; justify-content:space-between; color:var(--dash-muted); font-weight:650; gap:10px; }.dash-metric-top i { color:var(--bs-primary); font-size:1.5rem; }.dash-metric strong { color:var(--bs-gray-900); font-size:2.2rem; line-height:1.1; margin:18px 0 10px; font-weight:750; }.dash-metric em { font-size:1rem; font-style:normal; }.dash-metric small { color:var(--dash-muted); margin-top:auto; }
.dash-grid { display:grid; gap:20px; }.dash-grid-traffic { grid-template-columns:minmax(0,2fr) minmax(0,1fr); }.dash-grid-even { grid-template-columns:repeat(2,minmax(0,1fr)); }.dash-card-heading { display:flex; align-items:start; justify-content:space-between; gap:12px; margin-bottom:18px; }.dash-card-heading h3 { color:var(--bs-gray-900); font-size:1.13rem; font-weight:750; margin:0 0 6px; }.dash-period { color:var(--bs-primary); background:var(--bs-primary-light); padding:6px 9px; border-radius:8px; font-size:.65rem; font-weight:800; white-space:nowrap; }.dash-chart { height:310px; }.dash-chart-small { height:240px; }.dash-inline-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; border-top:1px solid var(--dash-line); padding-top:18px; }.dash-inline-stats div { display:flex; flex-direction:column; }.dash-inline-stats small,.dash-inline-stats span { color:var(--dash-muted); font-size:.75rem; }.dash-inline-stats strong { color:var(--bs-gray-900); font-size:1.2rem; }.dash-note { color:var(--dash-muted); font-size:.75rem; line-height:1.5; margin:18px 0 0; }.dash-callout { display:flex; flex-direction:column; border-top:1px solid var(--dash-line); padding-top:18px; }.dash-callout strong { font-size:1.6rem; color:var(--bs-gray-900); }.dash-callout span { color:var(--dash-muted); }
.dash-bars { display:grid; gap:17px; }.dash-bars a,.dash-ranking a,.dash-card td a { color:var(--bs-gray-800); font-weight:650; }.dash-bars a:hover,.dash-ranking a:hover,.dash-card td a:hover { color:var(--bs-primary); }.dash-bar { height:6px; background:var(--bs-primary-light); border-radius:8px; margin:8px 0 3px; overflow:hidden; }.dash-bar span { display:block; height:100%; background:var(--bs-primary); border-radius:8px; }.dash-bars small { color:var(--dash-muted); }.dash-list>div { display:flex; gap:12px; justify-content:space-between; padding:13px 0; border-bottom:1px solid var(--dash-line); }.dash-ranking>a { display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid var(--dash-line); }.dash-ranking>a>span { display:grid; place-items:center; width:30px; height:30px; flex:none; border-radius:9px; background:var(--bs-primary-light); color:var(--bs-primary); }.dash-ranking>a>div { flex:1; min-width:0; }.dash-ranking small { display:block; color:var(--dash-muted); font-weight:400; }.dash-ranking strong { color:var(--bs-gray-900); }.dash-empty { color:var(--dash-muted); text-align:center; padding:24px 0; }.dash-card .table { margin-bottom:0; }.dash-card .table th { color:var(--dash-muted); font-size:.72rem; text-transform:uppercase; white-space:nowrap; }.dash-card .table td { padding-top:13px; padding-bottom:13px; }
@media(max-width:1100px) { .dash-metrics { grid-template-columns:repeat(2,minmax(0,1fr)); }.dash-grid-traffic { grid-template-columns:1fr; } }
@media(max-width:700px) { .dash-hero { padding:25px; align-items:flex-start; flex-direction:column; }.dash-grid-even { grid-template-columns:1fr; }.dash-inline-stats { grid-template-columns:repeat(2,1fr); }.dash-metrics { gap:10px; }.dash-metric,.dash-card { padding:18px; }.dash-metric strong { font-size:1.8rem; }.dash-section-heading { align-items:start; }.dash-section { margin-bottom:38px; } }
</style>
@endpush

@if($siteVisitChart)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') return;
    const traffic = @js($siteVisitChart);
    const today = @js($todayTrafficChart);
    const clean = values => values.map(value => Number.isFinite(Number(value)) ? Number(value) : 0);
    const trafficValues = clean(traffic.visits), visitorValues = clean(traffic.visitors), todayValues = clean(today.values);
    const charts = [];
    function render() {
        while (charts.length) charts.pop().destroy();
        const label = KTUtil.getCssVariableValue('--bs-gray-600');
        const border = KTUtil.getCssVariableValue('--bs-border-dashed-color');
        const primary = KTUtil.getCssVariableValue('--bs-primary');
        const common = { chart: { fontFamily:'inherit', toolbar:{show:false}, animations:{enabled:false} }, dataLabels:{enabled:false}, grid:{borderColor:border,strokeDashArray:4}, legend:{position:'top',horizontalAlign:'left',labels:{colors:label}}, xaxis:{axisBorder:{show:false},axisTicks:{show:false},labels:{style:{colors:label}}} };
        const trafficChart = new ApexCharts(document.querySelector('#site-visits-30-days-chart'), {
            ...common, chart:{...common.chart,type:'area',height:310},
            series:[{name:'Visitas',data:trafficValues},{name:'Visitantes únicos',data:visitorValues}], colors:[primary,'#50cd89'], stroke:{curve:'smooth',width:3}, fill:{type:'gradient',gradient:{opacityFrom:.25,opacityTo:.02}}, markers:{size:0},
            xaxis:{...common.xaxis,categories:traffic.labels,labels:{...common.xaxis.labels,rotate:-45,hideOverlappingLabels:true}}, yaxis:{min:0,max:Math.max(1,...trafficValues,...visitorValues),tickAmount:4,forceNiceScale:true,labels:{style:{colors:label},formatter:value=>Math.round(value)}}, tooltip:{y:{formatter:value=>Math.round(value).toLocaleString('es-MX')}}
        });
        const todayChart = new ApexCharts(document.querySelector('#site-visits-today-chart'), {
            ...common, chart:{...common.chart,type:'bar',height:240}, series:[{name:'Visitas',data:todayValues}], colors:[primary], plotOptions:{bar:{borderRadius:4,columnWidth:'55%'}}, xaxis:{...common.xaxis,categories:today.labels,labels:{...common.xaxis.labels,rotate:-45,hideOverlappingLabels:true}}, yaxis:{min:0,max:Math.max(1,...todayValues),tickAmount:4,forceNiceScale:true,labels:{style:{colors:label},formatter:value=>Math.round(value)}}
        });
        charts.push(trafficChart,todayChart); trafficChart.render(); todayChart.render();
    }
    render();
    if (typeof KTThemeMode !== 'undefined') KTThemeMode.on('kt.thememode.change', render);
});
</script>
@endpush
@endif
