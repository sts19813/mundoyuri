@extends('layouts.admin')

@section('title', 'Chats privados - Admin')

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center me-3">
        <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Chats privados</h1>
        <span class="text-muted fs-7">Revisión de conversaciones entre usuarios</span>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header flex-wrap gap-3">
            <div class="card-title d-flex flex-column">
                <h2 class="fw-bold mb-1">Conversaciones</h2>
                <span class="text-muted fs-7">Útil para moderar reportes y revisar contexto.</span>
            </div>
            <form method="GET" action="{{ route('admin.conversations.index') }}" class="d-flex gap-2">
                <input class="form-control form-control-sm" name="q" value="{{ $search }}" placeholder="Buscar usuario o correo">
                <button class="btn btn-sm btn-primary">Buscar</button>
                @if($search !== '')
                    <a class="btn btn-sm btn-light" href="{{ route('admin.conversations.index') }}">Limpiar</a>
                @endif
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed gy-4">
                    <thead>
                        <tr class="text-muted fw-bold fs-7 text-uppercase">
                            <th>Participantes</th>
                            <th>Último mensaje</th>
                            <th class="text-end">Mensajes</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conversations as $conversation)
                            @php($lastMessage = $conversation->lastMessage)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-gray-900">{{ $conversation->userOne?->displayName() ?: 'Cuenta eliminada' }}</div>
                                    <div class="text-muted fs-8">{{ $conversation->userOne?->email }}</div>
                                    <div class="fw-semibold text-gray-900 mt-2">{{ $conversation->userTwo?->displayName() ?: 'Cuenta eliminada' }}</div>
                                    <div class="text-muted fs-8">{{ $conversation->userTwo?->email }}</div>
                                </td>
                                <td class="min-w-300px">
                                    @if($lastMessage)
                                        <div class="fw-semibold text-gray-900">{{ $lastMessage->sender?->displayName() ?: 'Cuenta eliminada' }}</div>
                                        <div class="text-muted fs-7">
                                            {{ $lastMessage->isDeleted() ? 'Mensaje eliminado' : (filled($lastMessage->body) ? \Illuminate\Support\Str::limit($lastMessage->body, 120) : 'Archivo: '.$lastMessage->attachment_name) }}
                                        </div>
                                        <div class="text-muted fs-8">{{ $lastMessage->created_at->timezone('America/Merida')->format('d/m/Y g:i a') }}</div>
                                    @else
                                        <span class="text-muted">Sin mensajes</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="badge badge-light-primary">{{ $conversation->messages_count }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.conversations.show', $conversation) }}" class="btn btn-sm btn-light-primary">Abrir chat</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-8">No hay conversaciones.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $conversations->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
