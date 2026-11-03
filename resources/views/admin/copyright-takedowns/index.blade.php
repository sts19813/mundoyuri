@extends('layouts.admin')

@section('title', 'Derechos de autor - Admin')

@section('toolbar')
    <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Avisos de derechos de autor</h1>
@endsection

@section('content')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="alert alert-info">Este registro es interno y no ofrece eliminación. Documenta la recepción, URLs, medida y resolución de cada aviso.</div>
    <div class="card mb-6">
        <div class="card-header"><h2 class="card-title">Registrar aviso recibido</h2></div>
        <form method="POST" action="{{ route('admin.copyright-takedowns.store') }}" class="card-body">@csrf
            <div class="row g-4">
                <div class="col-md-4"><label class="form-label">Fecha y hora de recepción</label><input class="form-control" type="datetime-local" name="received_at" value="{{ old('received_at', now()->format('Y-m-d\\TH:i')) }}" required></div>
                <div class="col-md-4"><label class="form-label">Reclamante</label><input class="form-control" name="claimant_name" value="{{ old('claimant_name') }}" required></div>
                <div class="col-md-4"><label class="form-label">Correo</label><input class="form-control" type="email" name="claimant_email" value="{{ old('claimant_email') }}" required></div>
                <div class="col-md-6"><label class="form-label">Organización <span class="text-muted">(opcional)</span></label><input class="form-control" name="claimant_organization" value="{{ old('claimant_organization') }}"></div>
                <div class="col-md-6"><label class="form-label">Obra reclamada</label><textarea class="form-control" rows="2" name="work_description" required>{{ old('work_description') }}</textarea></div>
                <div class="col-12"><label class="form-label">URLs reportadas</label><textarea class="form-control" rows="3" name="reported_urls" placeholder="Una URL por línea" required>{{ old('reported_urls') }}</textarea></div>
            </div>
            <button class="btn btn-primary mt-5">Registrar aviso</button>
        </form>
    </div>
    <div class="card">
        <div class="card-header flex-wrap gap-3"><h2 class="card-title">Historial</h2><div class="d-flex flex-wrap gap-2">@foreach(['' => 'Todos', 'received' => 'Recibidos', 'reviewing' => 'En revisión', 'resolved' => 'Resueltos', 'dismissed' => 'Descartados'] as $value => $label)<a class="btn btn-sm {{ $status === $value ? 'btn-primary' : 'btn-light' }}" href="{{ route('admin.copyright-takedowns.index', $value === '' ? [] : ['status' => $value]) }}">{{ $label }}</a>@endforeach</div></div>
        <div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Recepción y reclamante</th><th>Obra y URLs</th><th>Resolución</th></tr></thead><tbody>@forelse($takedowns as $takedown)
            <tr><td><strong>{{ $takedown->claimant_name }}</strong><div class="text-muted fs-8">{{ $takedown->claimant_email }}@if($takedown->claimant_organization) · {{ $takedown->claimant_organization }}@endif</div><div class="text-muted fs-8">{{ $takedown->received_at->format('d/m/Y H:i') }}</div></td><td class="min-w-300px"><strong>{{ $takedown->work_description }}</strong><pre class="text-muted fs-8 text-wrap mb-0">{{ $takedown->reported_urls }}</pre></td><td class="min-w-350px"><form method="POST" action="{{ route('admin.copyright-takedowns.update', $takedown) }}">@csrf @method('PATCH')<div class="d-flex gap-2 mb-2"><select class="form-select form-select-sm" name="status">@foreach(['received' => 'Recibido', 'reviewing' => 'En revisión', 'resolved' => 'Resuelto', 'dismissed' => 'Descartado'] as $value => $label)<option value="{{ $value }}" @selected($takedown->status === $value)>{{ $label }}</option>@endforeach</select><select class="form-select form-select-sm" name="action_taken"><option value="">Sin medida</option>@foreach(['source_disabled' => 'Fuente deshabilitada', 'episode_unpublished' => 'Episodio despublicado', 'series_unpublished' => 'Serie despublicada', 'content_restored' => 'Contenido restaurado', 'no_action' => 'Sin acción'] as $value => $label)<option value="{{ $value }}" @selected($takedown->action_taken === $value)>{{ $label }}</option>@endforeach</select></div><textarea class="form-control form-control-sm" rows="2" name="resolution_notes" placeholder="Notas de resolución">{{ $takedown->resolution_notes }}</textarea><button class="btn btn-sm btn-primary mt-2">Guardar</button></form>@if($takedown->resolved_at)<div class="text-muted fs-8 mt-2">{{ $takedown->resolved_at->format('d/m/Y H:i') }} · {{ $takedown->resolver?->displayName() }}</div>@endif</td></tr>
        @empty<tr><td colspan="3" class="text-center text-muted">Aún no hay avisos registrados.</td></tr>@endforelse</tbody></table></div>{{ $takedowns->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
