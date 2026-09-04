@extends('layouts.admin')

@section('title', 'Reportes de comunidad - Admin')

@section('toolbar')
    <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Reportes de comunidad</h1>
@endsection

@section('content')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card">
        <div class="card-header flex-wrap gap-3">
            <h2 class="card-title">Cola de moderación</h2>
            <div class="d-flex flex-wrap gap-2">
                @foreach(['' => 'Todos', 'pending' => 'Pendientes', 'reviewing' => 'En revisión', 'resolved' => 'Resueltos', 'dismissed' => 'Descartados'] as $value => $label)
                    <a class="btn btn-sm {{ $status === $value ? 'btn-primary' : 'btn-light' }}" href="{{ route('admin.community-reports.index', $value === '' ? [] : ['status' => $value]) }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>Reporte</th><th>Contenido</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>@forelse($reports as $report)
                    @php($target = $report->reportable)
                    <tr>
                        <td><strong>{{ match($report->reason) { 'harassment' => 'Acoso', 'inappropriate_content' => 'Contenido inapropiado', 'unmarked_spoiler' => 'Spoiler sin marcar', 'personal_information' => 'Información personal', default => ucfirst($report->reason) } }}</strong><div class="text-muted fs-8">Por {{ $report->reporter?->displayName() ?: 'Cuenta eliminada' }} · {{ $report->created_at->diffForHumans() }}</div>@if($report->details)<div class="text-gray-700 fs-8 mt-1">{{ $report->details }}</div>@endif</td>
                        <td>@if($target instanceof \App\Models\ForumThread)<strong>{{ $target->isQuestion() ? 'Pregunta' : 'Tema' }}:</strong> {{ $target->title }}@elseif($target instanceof \App\Models\ForumPost)<strong>Mensaje:</strong> {{ \Illuminate\Support\Str::limit($target->body, 100) }}@elseif($target instanceof \App\Models\User)<strong>Perfil:</strong> {{ $target->displayName() }}@else<span class="text-muted">Contenido eliminado</span>@endif</td>
                        <td><span class="badge badge-light-{{ match($report->status) { 'pending' => 'warning', 'reviewing' => 'primary', 'resolved' => 'success', default => 'secondary' } }}">{{ match($report->status) { 'pending' => 'Pendiente', 'reviewing' => 'En revisión', 'resolved' => 'Resuelto', default => 'Descartado' } }}</span>@if($report->reviewer)<div class="text-muted fs-8 mt-1">{{ $report->reviewer->displayName() }}</div>@endif</td>
                        <td class="min-w-250px"><div class="d-flex flex-wrap gap-2"><form method="POST" action="{{ route('admin.community-reports.action', $report) }}">@csrf<input type="hidden" name="action" value="hide"><button class="btn btn-sm btn-light-warning">Ocultar</button></form><form method="POST" action="{{ route('admin.community-reports.action', $report) }}">@csrf<input type="hidden" name="action" value="restore"><button class="btn btn-sm btn-light-success">Restaurar</button></form>@if($target instanceof \App\Models\ForumThread || $target instanceof \App\Models\ForumPost)<form method="POST" action="{{ route('admin.community-reports.action', $report) }}">@csrf<input type="hidden" name="action" value="lock_thread"><button class="btn btn-sm btn-light-primary">Cerrar tema</button></form>@endif</div><form method="POST" action="{{ route('admin.community-reports.update', $report) }}" class="d-flex gap-2 mt-3">@csrf @method('PATCH')<select class="form-select form-select-sm" name="status"><option value="pending" @selected($report->status === 'pending')>Pendiente</option><option value="reviewing" @selected($report->status === 'reviewing')>En revisión</option><option value="resolved" @selected($report->status === 'resolved')>Resuelto</option><option value="dismissed" @selected($report->status === 'dismissed')>Descartado</option></select><input class="form-control form-control-sm" name="resolution" maxlength="2000" value="{{ $report->resolution }}" placeholder="Resolución opcional"><button class="btn btn-sm btn-primary">Guardar</button></form></td>
                    </tr>
                @empty<tr><td colspan="4" class="text-center text-muted">No hay reportes para este filtro.</td></tr>@endforelse</tbody>
            </table></div>
            {{ $reports->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
