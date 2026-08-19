@extends('layouts.admin')

@section('title', 'Secciones del catálogo')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-5">
        <div><h1 class="h3 mb-1">Secciones del catálogo</h1><p class="text-muted mb-0">Configura el hero, los textos y la etiqueta de Anime, Series GL y futuras secciones.</p></div>
        <a href="{{ route('admin.catalog-sections.create') }}" class="btn btn-primary">Nueva sección</a>
    </div>
    <div class="card"><div class="table-responsive"><table class="table table-row-bordered align-middle gy-4 mb-0">
        <thead><tr class="fw-bold text-muted"><th>Sección</th><th>Etiqueta</th><th>Hero</th><th>URL</th><th>Estado</th><th class="text-end"></th></tr></thead>
        <tbody>@forelse($sections as $catalogSection)
            <tr><td><strong>{{ $catalogSection->name }}</strong></td><td>{{ $catalogSection->label ?: '—' }}</td><td>{{ \Illuminate\Support\Str::limit($catalogSection->hero_title, 55) }}</td><td><a href="{{ url('/'.$catalogSection->slug) }}" target="_blank" rel="noopener">/{{ $catalogSection->slug }}</a></td><td><span class="badge badge-light-{{ $catalogSection->is_active ? 'success' : 'secondary' }}">{{ $catalogSection->is_active ? 'Visible' : 'Oculta' }}</span></td><td class="text-end"><a href="{{ route('admin.catalog-sections.edit', $catalogSection) }}" class="btn btn-sm btn-light-primary">Configurar</a></td></tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-8">Aún no hay secciones.</td></tr>
        @endforelse</tbody>
    </table></div></div>
@endsection
