@extends('layouts.admin')

@section('title', 'Mensajes de Miyu - Mundo Yuri')

@section('toolbar')
    <div class="d-flex align-items-center gap-3">
        <div>
            <h1 class="fs-2 fw-bold mb-1">Mensajes de Miyu</h1>
            <div class="text-muted fs-7">Reportes, mensajes y solicitudes enviados desde la asistente.</div>
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <form class="d-flex flex-wrap gap-3" method="GET">
                <select class="form-select form-select-solid w-auto" name="type">
                    <option value="">Todos los tipos</option>
                    <option value="report" @selected(request('type') === 'report')>Reportes</option>
                    <option value="request" @selected(request('type') === 'request')>Solicitudes</option>
                    <option value="message" @selected(request('type') === 'message')>Mensajes</option>
                </select>
                <select class="form-select form-select-solid w-auto" name="status">
                    <option value="">Todos los estados</option>
                    <option value="unread" @selected(request('status') === 'unread')>Sin leer</option>
                    <option value="read" @selected(request('status') === 'read')>Leídos</option>
                    <option value="resolved" @selected(request('status') === 'resolved')>Resueltos</option>
                </select>
                <button class="btn btn-primary" type="submit">Filtrar</button>
                @if(request()->hasAny(['type', 'status']))
                    <a class="btn btn-light" href="{{ route('admin.assistant-messages.index') }}">Limpiar</a>
                @endif
            </form>
        </div>

        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                            <th>Tipo</th>
                            <th>Mensaje</th>
                            <th>Contacto</th>
                            <th>Página</th>
                            <th>Fecha</th>
                            <th class="text-end">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700">
                        @forelse($messages as $message)
                            @php
                                $typeLabels = ['report' => 'Reporte', 'request' => 'Solicitud', 'message' => 'Mensaje'];
                                $typeClasses = ['report' => 'badge-light-danger', 'request' => 'badge-light-warning', 'message' => 'badge-light-primary'];
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge {{ $typeClasses[$message->type] ?? 'badge-light' }}">
                                        {{ $typeLabels[$message->type] ?? ucfirst($message->type) }}
                                    </span>
                                </td>
                                <td style="min-width:280px;max-width:520px;">
                                    <div class="text-gray-900">{{ $message->message }}</div>
                                    @if($message->user)
                                        <div class="text-muted fs-8 mt-1">Cuenta: {{ $message->user->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($message->contact_email)
                                        <a href="mailto:{{ $message->contact_email }}">{{ $message->contact_email }}</a>
                                    @else
                                        <span class="text-muted">Sin correo</span>
                                    @endif
                                </td>
                                <td style="max-width:220px;">
                                    @if($message->page_url)
                                        <a class="d-block text-truncate" href="{{ $message->page_url }}"
                                            target="_blank" rel="noopener noreferrer" title="{{ $message->page_url }}">
                                            {{ $message->page_url }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $message->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.assistant-messages.update', $message) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select class="form-select form-select-sm" name="status"
                                            onchange="this.form.submit()" aria-label="Estado del mensaje">
                                            <option value="unread" @selected($message->status === 'unread')>Sin leer</option>
                                            <option value="read" @selected($message->status === 'read')>Leído</option>
                                            <option value="resolved" @selected($message->status === 'resolved')>Resuelto</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-muted">
                                    Aún no hay mensajes para estos filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $messages->links() }}
        </div>
    </div>
@endsection
