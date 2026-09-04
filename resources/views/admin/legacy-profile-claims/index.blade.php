@extends('layouts.admin')

@section('title', 'Reclamaciones históricas - Admin')

@section('toolbar')
    <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Reclamaciones históricas</h1>
@endsection

@section('content')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card">
        <div class="card-header flex-wrap gap-3">
            <div><h2 class="card-title">Solicitudes de reclamación</h2><p class="text-muted fs-7 mb-0">La aprobación vincula un archivo histórico a una cuenta actual; no modifica sus credenciales.</p></div>
            <div class="d-flex flex-wrap gap-2">
                @foreach(['' => 'Todas', 'pending' => 'Pendientes', 'approved' => 'Aprobadas', 'rejected' => 'Rechazadas'] as $value => $label)
                    <a class="btn btn-sm {{ $status === $value ? 'btn-primary' : 'btn-light' }}" href="{{ route('admin.legacy-profile-claims.index', $value === '' ? [] : ['status' => $value]) }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive"><table class="table align-middle table-row-dashed gy-5">
                <thead><tr class="text-start text-muted fw-bold fs-7 text-uppercase"><th>Perfil y solicitante</th><th>Explicación</th><th>Estado</th><th class="min-w-300px">Revisión</th></tr></thead>
                <tbody>@forelse($claims as $claim)
                    <tr>
                        <td><strong>{{ $claim->legacyProfile?->nickname ?: 'Perfil eliminado' }}</strong><div class="text-muted fs-8">Solicita: {{ $claim->claimant?->displayName() ?: 'Cuenta eliminada' }} · {{ $claim->created_at->diffForHumans() }}</div></td>
                        <td><div class="text-gray-800">{{ $claim->message }}</div>@if($claim->evidence)<div class="text-muted fs-8 mt-2">Evidencia: {{ $claim->evidence }}</div>@endif</td>
                        <td><span class="badge badge-light-{{ match($claim->status) { 'pending' => 'warning', 'approved' => 'success', default => 'secondary' } }}">{{ match($claim->status) { 'pending' => 'Pendiente', 'approved' => 'Aprobada', default => 'Rechazada' } }}</span>@if($claim->reviewer)<div class="text-muted fs-8 mt-2">{{ $claim->reviewer->displayName() }} · {{ $claim->reviewed_at?->diffForHumans() }}</div>@endif</td>
                        <td>@if($claim->status === 'pending')<form method="POST" action="{{ route('admin.legacy-profile-claims.update', $claim) }}">@csrf @method('PATCH')<select name="status" class="form-select form-select-sm mb-2" required><option value="approved">Aprobar y vincular</option><option value="rejected">Rechazar</option></select><textarea name="admin_notes" class="form-control form-control-sm mb-2" rows="3" maxlength="5000" placeholder="Notas privadas de revisión"></textarea><button type="submit" class="btn btn-sm btn-primary">Guardar decisión</button></form>@elseif($claim->admin_notes)<span class="text-muted fs-8">Nota privada: {{ $claim->admin_notes }}</span>@else<span class="text-muted fs-8">Sin notas de revisión.</span>@endif</td>
                    </tr>
                @empty<tr><td colspan="4" class="text-center text-muted py-10">No hay solicitudes para este filtro.</td></tr>@endforelse</tbody>
            </table></div>
            {{ $claims->links() }}
        </div>
    </div>
@endsection
