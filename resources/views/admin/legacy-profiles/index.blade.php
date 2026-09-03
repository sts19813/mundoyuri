@extends('layouts.admin')

@section('title', 'Perfiles históricos - Admin')

@section('toolbar')
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2"><h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Perfiles históricos</h1></div>
    <a href="{{ route('admin.legacy-profiles.create') }}" class="btn btn-sm btn-primary"><i class="ki-outline ki-plus fs-2"></i>Importar manualmente</a>
@endsection

@section('content')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="card-header border-0 pt-6"><div class="card-title"><div><h2 class="fw-bold text-gray-900 mb-1">Archivo del antiguo Mundo Yuri</h2><div class="text-muted fs-7">No son cuentas actuales: no tienen correo, contraseña ni acceso de inicio de sesión.</div></div></div></div><div class="card-body pt-4"><div class="table-responsive"><table class="table align-middle table-row-dashed gy-5"><thead><tr class="text-start text-muted fw-bold fs-7 text-uppercase"><th>Perfil</th><th>Registro histórico</th><th>Mensajes</th><th>Reclamación</th><th>Estado</th><th class="text-end">Acción</th></tr></thead><tbody>@forelse($legacyProfiles as $legacyProfile)<tr><td><div class="fw-bold text-gray-900">{{ $legacyProfile->nickname }}</div><div class="text-muted fs-7">{{ $legacyProfile->legacy_external_key }}</div></td><td>{{ $legacyProfile->legacy_joined_at?->format('d/m/Y') ?: 'Sin fecha' }}</td><td>{{ number_format($legacyProfile->legacy_message_count) }}</td><td><span class="badge badge-light-info">{{ ucfirst($legacyProfile->claim_status) }}</span>@if($legacyProfile->claimedBy)<div class="text-muted fs-8 mt-1">{{ $legacyProfile->claimedBy->name }}</div>@endif</td><td><span class="badge {{ $legacyProfile->is_published ? 'badge-light-success' : 'badge-light-warning' }}">{{ $legacyProfile->is_published ? 'Publicado' : 'Archivado' }}</span></td><td class="text-end"><a href="{{ route('admin.legacy-profiles.edit', $legacyProfile) }}" class="btn btn-sm btn-light-primary">Editar</a></td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-10">No hay perfiles históricos importados.</td></tr>@endforelse</tbody></table></div>{{ $legacyProfiles->links() }}</div></div>
@endsection
