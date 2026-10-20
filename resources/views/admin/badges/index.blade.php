@extends('layouts.admin')

@section('title', 'Insignias comunitarias - Admin')

@php($automaticCount = $badges->filter(fn ($badge) => array_key_exists($badge->slug, $automationRules))->count())

@section('toolbar')
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Insignias comunitarias</h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 ms-2"><li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Admin</a></li><li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li><li class="breadcrumb-item text-muted">Comunidad</li></ul>
    </div>
    <div class="d-flex gap-2"><form method="POST" action="{{ route('admin.badges.sync-automation') }}">@csrf<button class="btn btn-sm btn-light-primary" type="submit"><i class="ki-outline ki-arrows-circle fs-2"></i>Sincronizar ahora</button></form><a href="{{ route('admin.badges.create') }}" class="btn btn-sm btn-primary"><i class="ki-outline ki-plus fs-2"></i>Nueva insignia</a></div>
@endsection

@section('content')
    @if(session('success'))<div class="alert alert-success d-flex align-items-center gap-3"><i class="ki-outline ki-check-circle fs-2x"></i><span>{{ session('success') }}</span></div>@endif
    <div class="alert alert-info d-flex align-items-start gap-3 mb-6"><i class="ki-outline ki-information-5 fs-2x mt-1"></i><div><strong>Cómo funciona.</strong> Las insignias automáticas se conceden cuando una actividad verificable cumple la condición indicada. Si deja de cumplirse, solo se retira una concesión que también haya sido automática; las concesiones manuales siempre se conservan hasta que una persona las retire.</div></div>

    <div class="row g-5 g-xl-8 mb-6">
        <div class="col-md-4"><div class="card h-100"><div class="card-body d-flex align-items-center gap-4"><span class="symbol symbol-50px"><span class="symbol-label bg-light-primary"><i class="ki-outline ki-award fs-2x text-primary"></i></span></span><div><div class="fs-2 fw-bold text-gray-900">{{ $badges->total() }}</div><div class="text-muted fw-semibold">Insignias del catálogo</div></div></div></div></div>
        <div class="col-md-4"><div class="card h-100"><div class="card-body d-flex align-items-center gap-4"><span class="symbol symbol-50px"><span class="symbol-label bg-light-success"><i class="ki-outline ki-flash fs-2x text-success"></i></span></span><div><div class="fs-2 fw-bold text-gray-900">{{ $automaticCount }}</div><div class="text-muted fw-semibold">Con reglas automáticas</div></div></div></div></div>
        <div class="col-md-4"><div class="card h-100"><div class="card-body d-flex align-items-center gap-4"><span class="symbol symbol-50px"><span class="symbol-label bg-light-warning"><i class="ki-outline ki-note-2 fs-2x text-warning"></i></span></span><div><div class="fs-2 fw-bold text-gray-900">{{ $logs->total() }}</div><div class="text-muted fw-semibold">Movimientos registrados</div></div></div></div></div>
    </div>

    <div class="card mb-6">
        <div class="card-header border-0 pt-6"><div class="card-title"><div><h2 class="fw-bold text-gray-900 mb-1">Catálogo y reglas</h2><div class="text-muted fs-7">Se muestran hasta 100 insignias por página. Las insignias sin regla conservan asignación manual.</div></div></div></div>
        <div class="card-body pt-4"><div class="table-responsive"><table class="table align-middle table-row-dashed gy-5"><thead><tr class="text-start text-muted fw-bold fs-7 text-uppercase"><th>Insignia</th><th>Asignación y condición exacta</th><th>Tipo</th><th>Otorgadas</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>
            @forelse($badges as $badge)
                @php($rule = $automationRules[$badge->slug] ?? null)
                <tr>
                    <td><div class="d-flex align-items-center gap-3"><span class="fs-2">{{ $badge->icon ?: '✦' }}</span><div><div class="fw-bold text-gray-900">{{ $badge->name }}</div><div class="text-muted fs-7">{{ $badge->slug }} · prioridad {{ $badge->priority }}</div></div></div></td>
                    <td class="mw-450px">@if($rule)<span class="badge badge-light-success mb-2"><i class="ki-outline ki-flash fs-7 me-1"></i>Automática</span><div class="text-gray-800 fs-7">{{ $rule }}</div>@else<span class="badge badge-light-secondary mb-2">Manual</span><div class="text-muted fs-7">Requiere una decisión del equipo. {{ $badge->description }}</div>@endif</td>
                    <td><span class="badge badge-light-info">{{ match($badge->type) { 'legacy' => 'Histórica', 'achievement' => 'Logro', 'staff' => 'Staff', 'contribution' => 'Contribución', 'development' => 'Desarrollo', 'activity' => 'Actividad', 'forum' => 'Foro', 'questions' => 'Preguntas', 'social' => 'Social', 'catalog' => 'Catálogo', 'community' => 'Comunidad', 'seniority' => 'Antigüedad', 'event' => 'Evento', 'fun' => 'Divertida', 'secret' => 'Secreta', default => 'Especial' } }}</span></td>
                    <td><span class="badge badge-light-primary">{{ $badge->users_count + $badge->legacy_profiles_count }}</span></td>
                    <td><span class="badge {{ $badge->is_active ? 'badge-light-success' : 'badge-light-danger' }}">{{ $badge->is_active ? 'Activa' : 'Inactiva' }}</span></td>
                    <td class="text-end text-nowrap"><a href="{{ route('admin.badges.edit', $badge) }}" class="btn btn-sm btn-light-primary me-1"><i class="ki-outline ki-pencil fs-4"></i>Editar</a><form method="POST" action="{{ route('admin.badges.destroy', $badge) }}" class="d-inline" data-dialog-confirm="También se retirará de las personas que la tengan." data-dialog-title="¿Eliminar esta insignia?" data-dialog-confirm-text="Eliminar">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-light-danger"><i class="ki-outline ki-trash fs-4"></i>Eliminar</button></form></td>
                </tr>
            @empty<tr><td colspan="6" class="text-center text-muted py-10">No hay insignias comunitarias.</td></tr>@endforelse
        </tbody></table></div>{{ $badges->withQueryString()->links() }}</div>
    </div>

    <div class="card"><div class="card-header border-0 pt-6"><div class="card-title"><div><h2 class="fw-bold text-gray-900 mb-1">Historial de asignaciones</h2><div class="text-muted fs-7">Auditoría de altas y retiros, tanto manuales como automáticos.</div></div></div></div><div class="card-body pt-4"><div class="table-responsive"><table class="table align-middle table-row-dashed gy-5"><thead><tr class="text-start text-muted fw-bold fs-7 text-uppercase"><th>Fecha</th><th>Usuario</th><th>Movimiento</th><th>Insignia</th><th>Origen y motivo</th></tr></thead><tbody>
        @forelse($logs as $log)
            <tr><td class="text-nowrap"><div class="fw-semibold text-gray-800">{{ $log->created_at->format('d/m/Y H:i') }}</div><div class="text-muted fs-8">{{ $log->created_at->diffForHumans() }}</div></td><td>{{ $log->user?->displayName() ?: 'Usuario eliminado' }}</td><td><span class="badge {{ $log->action === 'assigned' ? 'badge-light-success' : 'badge-light-danger' }}">{{ $log->action === 'assigned' ? 'Asignada' : 'Retirada' }}</span></td><td><span class="fs-5 me-1">{{ $log->badge?->icon }}</span><span class="fw-bold">{{ $log->badge_name }}</span></td><td><span class="badge badge-light-{{ $log->source === 'automatic' ? 'success' : 'primary' }} mb-1">{{ $log->source === 'automatic' ? 'Automática' : 'Manual' }}</span><div class="text-muted fs-7">{{ $log->condition ?: ($log->actor ? 'Por '.$log->actor->displayName() : 'Sin detalle adicional') }}</div>@if($log->note)<div class="text-gray-700 fs-8 mt-1">Nota: {{ $log->note }}</div>@endif</td></tr>
        @empty<tr><td colspan="5" class="text-center text-muted py-10">Todavía no hay movimientos registrados. Al sincronizar o asignar una insignia aparecerán aquí.</td></tr>@endforelse
    </tbody></table></div>{{ $logs->withQueryString()->links() }}</div></div>
@endsection
