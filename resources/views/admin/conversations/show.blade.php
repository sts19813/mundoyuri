@extends('layouts.admin')

@section('title', 'Chat privado - Admin')

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center me-3">
        <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Chat privado</h1>
        <span class="text-muted fs-7">
            {{ $conversation->userOne?->displayName() ?: 'Cuenta eliminada' }} · {{ $conversation->userTwo?->displayName() ?: 'Cuenta eliminada' }}
        </span>
    </div>
    <a href="{{ route('admin.conversations.index') }}" class="btn btn-sm btn-light">Volver</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="card-title d-flex flex-column">
                <h2 class="fw-bold mb-1">Mensajes</h2>
                <span class="text-muted fs-7">Los mensajes eliminados por usuarios se muestran aquí con su contenido original para moderación.</span>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex flex-column gap-4">
                @forelse($messages as $message)
                    <div class="border rounded p-4 {{ $message->isDeleted() ? 'bg-light-warning' : '' }}">
                        <div class="d-flex flex-wrap justify-content-between gap-3 mb-2">
                            <div>
                                <div class="fw-bold text-gray-900">{{ $message->sender?->displayName() ?: 'Cuenta eliminada' }}</div>
                                <div class="text-muted fs-8">Para {{ $message->recipient?->displayName() ?: 'Cuenta eliminada' }} · {{ $message->created_at->timezone('America/Merida')->format('d/m/Y g:i a') }}</div>
                            </div>
                            @if($message->isDeleted())
                                <span class="badge badge-light-warning">Eliminado por {{ $message->deletedBy?->displayName() ?: 'usuario' }} · {{ $message->deleted_at->timezone('America/Merida')->format('d/m/Y g:i a') }}</span>
                            @else
                                <span class="badge badge-light-success">Visible</span>
                            @endif
                        </div>
                        @if(filled($message->body))
                            <div class="text-gray-800" style="white-space: pre-wrap;">{{ $message->body }}</div>
                        @endif
                        @if($message->hasStoredAttachment())
                            <div class="mt-3">
                                @if(str_starts_with((string) $message->attachment_mime, 'image/'))
                                    <a href="{{ route('admin.conversations.attachments.show', $message) }}" target="_blank" rel="noopener">
                                        <img src="{{ route('admin.conversations.attachments.show', $message) }}" alt="{{ $message->attachment_name }}" class="rounded" style="max-width: 260px; max-height: 220px; object-fit: contain;">
                                    </a>
                                @else
                                    <a href="{{ route('admin.conversations.attachments.show', $message) }}" class="btn btn-sm btn-light-primary">{{ $message->attachment_name }} · {{ $message->attachmentSizeLabel() }}</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-muted py-8">Esta conversación no tiene mensajes.</div>
                @endforelse
            </div>

            <div class="mt-5">{{ $messages->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
@endsection
