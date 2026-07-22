@extends('layouts.admin')

@section('title', 'Episodios')

@push('styles')
<style>
    .episode-group-toggle { width: 2rem; height: 2rem; }
    .episode-group-toggle .episode-chevron { display: inline-block; transition: transform .18s ease; }
    .episode-group-toggle[aria-expanded="true"] .episode-chevron { transform: rotate(180deg); }
    .episode-source-url { overflow-wrap: anywhere; word-break: break-word; }
    .episode-detail-row > td { background: var(--bs-gray-100); }
    .episode-group-table > :not(caption) > * > * { padding-top: .65rem; padding-bottom: .65rem; }
</style>
@endpush

@section('toolbar')
<div class="page-title d-flex flex-column justify-content-center me-3">
    <h1 class="page-heading fw-bold fs-3 m-0">Episodios por serie o película</h1>
</div>
@can('create episodes')
    <div><a href="{{ route('admin.episodes.create') }}" class="btn btn-primary">Nuevo episodio</a></div>
@endcan
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card mb-5">
    <div class="card-body py-4">
        <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.episodes.index') }}">
            <div class="col-lg-4 col-md-6">
                <label class="form-label fs-7">Buscar título</label>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Serie o película">
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fs-7">Título exacto</label>
                <select class="form-select" name="series_id">
                    <option value="">Todos los títulos</option>
                    @foreach($seriesOptions as $option)
                        <option value="{{ $option->id }}" @selected(request('series_id') == $option->id)>{{ $option->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fs-7">Moderación</label>
                <select class="form-select" name="moderation_status">
                    <option value="">Todos los estados</option>
                    <option value="pending" @selected(request('moderation_status') === 'pending')>Pendiente</option>
                    <option value="approved" @selected(request('moderation_status') === 'approved')>Aprobado</option>
                    <option value="rejected" @selected(request('moderation_status') === 'rejected')>Rechazado</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6 d-flex gap-2">
                <button class="btn btn-light-primary flex-grow-1" type="submit">Filtrar</button>
                @if(request()->hasAny(['q', 'series_id', 'moderation_status']))
                    <a class="btn btn-light" href="{{ route('admin.episodes.index') }}" title="Limpiar filtros">×</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-row-dashed align-middle mb-0 episode-group-table">
                <thead>
                    <tr class="text-muted fw-semibold fs-7">
                        <th class="w-40px"></th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Episodios</th>
                        <th>Temporadas</th>
                        <th>Última publicación web</th>
                        <th class="text-end pe-5">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($seriesGroups as $seriesGroup)
                    @php
                        $episodes = $seriesGroup->episodes;
                        $loadedCount = (int) $seriesGroup->loaded_episodes_count;
                        $declaredCount = (int) $seriesGroup->total_episodes;
                        $missingCount = $declaredCount > $loadedCount ? $declaredCount - $loadedCount : 0;
                        $seasonCount = $episodes->pluck('season_number')->unique()->count();
                        $latestPublication = $episodes->whereNotNull('published_at')->max('published_at');
                        $collapseId = 'episodes-series-'.$seriesGroup->id;
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <button
                                class="btn btn-sm btn-icon btn-light episode-group-toggle"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#{{ $collapseId }}"
                                aria-expanded="{{ request('series_id') == $seriesGroup->id ? 'true' : 'false' }}"
                                aria-controls="{{ $collapseId }}"
                                title="Mostrar episodios"
                            ><span class="episode-chevron">⌄</span></button>
                        </td>
                        <td>
                            <div class="fw-bold text-gray-900">{{ $seriesGroup->title }}</div>
                            @if($missingCount > 0)
                                <span class="text-warning fs-8">Faltan {{ $missingCount }} de {{ $declaredCount }}</span>
                            @elseif($declaredCount > 0)
                                <span class="text-success fs-8">Carga completa</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-light-{{ $seriesGroup->content_type === 'movie' ? 'info' : 'primary' }}">
                                {{ $seriesGroup->content_type === 'movie' ? 'Película' : 'Serie' }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-bold">{{ $loadedCount }}</span>
                            @if($declaredCount > 0)<span class="text-muted"> / {{ $declaredCount }}</span>@endif
                        </td>
                        <td>{{ $seasonCount ?: '—' }}</td>
                        <td>{{ $latestPublication?->format('d/m/Y H:i') ?: 'Sin publicar' }}</td>
                        <td class="text-end pe-4">
                            @can('create episodes')
                                <a href="{{ route('admin.episodes.create', ['series_id' => $seriesGroup->id]) }}" class="btn btn-sm btn-primary text-nowrap">Agregar episodio</a>
                            @endcan
                        </td>
                    </tr>
                    <tr class="episode-detail-row">
                        <td colspan="7" class="p-0">
                            <div id="{{ $collapseId }}" class="collapse {{ request('series_id') == $seriesGroup->id ? 'show' : '' }}">
                                <div class="p-4">
                                    @if($episodes->isEmpty())
                                        <div class="text-center text-muted py-5">Este título todavía no tiene episodios.</div>
                                    @else
                                        <div class="table-responsive rounded border bg-body">
                                            <table class="table table-sm table-row-bordered align-middle mb-0">
                                            <thead>
                                                <tr class="text-muted fw-semibold fs-8">
                                                    <th class="ps-4">Temporada / episodio</th>
                                                    <th>Título</th>
                                                    <th style="min-width: 320px">URLs</th>
                                                    <th>Publicación web</th>
                                                    <th>Moderación</th>
                                                    <th class="text-end pe-4">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($episodes as $episode)
                                                <tr>
                                                    <td class="ps-4 text-nowrap fw-semibold">T{{ $episode->season_number }} · E{{ $episode->episode_number }}</td>
                                                    <td>{{ $episode->title }}</td>
                                                    <td>
                                                        @forelse($episode->sources as $source)
                                                            <div class="d-flex align-items-start gap-2 {{ !$loop->last ? 'mb-2' : '' }}">
                                                                <span class="badge badge-light text-uppercase">{{ $source->provider }}</span>
                                                                <a class="episode-source-url fs-8" href="{{ $source->video_url }}" target="_blank" rel="noopener noreferrer">{{ $source->video_url }}</a>
                                                            </div>
                                                        @empty
                                                            <span class="text-danger fs-8">Sin URL</span>
                                                        @endforelse
                                                    </td>
                                                    <td class="text-nowrap">
                                                        <div>{{ $episode->published_at?->format('d/m/Y H:i') ?: 'No publicada' }}</div>
                                                        @if($episode->release_date)
                                                            <div class="text-muted fs-8">Estreno: {{ $episode->release_date->format('d/m/Y') }}</div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-light-{{ $episode->moderation_status === 'approved' ? 'success' : ($episode->moderation_status === 'pending' ? 'warning' : 'danger') }}">
                                                            {{ match($episode->moderation_status) { 'approved' => 'Aprobado', 'pending' => 'Pendiente', default => 'Rechazado' } }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end pe-4 text-nowrap">
                                                        <a href="{{ route('admin.episodes.show', $episode) }}" class="btn btn-sm btn-light">Ver</a>
                                                        @can('edit episodes')
                                                            <a href="{{ route('admin.episodes.edit', $episode) }}" class="btn btn-sm btn-light-primary">Editar</a>
                                                        @endcan
                                                        @can('delete episodes')
                                                            <form method="POST" action="{{ route('admin.episodes.destroy', $episode) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este episodio?')">
                                                                @csrf @method('DELETE')
                                                                <button class="btn btn-sm btn-light-danger" type="submit">Eliminar</button>
                                                            </form>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-10">No hay series o películas para mostrar.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($seriesGroups->hasPages())
        <div class="card-footer py-3">{{ $seriesGroups->links() }}</div>
    @endif
</div>
@endsection
