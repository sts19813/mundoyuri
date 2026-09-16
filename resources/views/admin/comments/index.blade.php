@extends('layouts.admin')

@section('title', 'Comentarios - Admin')

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center me-3">
        <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Comentarios</h1>
        <span class="text-muted fs-7">Moderación de comentarios del catálogo</span>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header flex-wrap gap-3">
            <div class="card-title d-flex flex-column">
                <h2 class="fw-bold mb-1">Comentarios de usuarios</h2>
                <span class="text-muted fs-7">Revisa, abre el contexto, oculta o elimina contenido inapropiado.</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach(['' => 'Todos', 'visible' => 'Visibles', 'hidden' => 'Ocultos'] as $value => $label)
                    <a class="btn btn-sm {{ $status === $value ? 'btn-primary' : 'btn-light' }}" href="{{ route('admin.comments.index', $value === '' ? [] : ['status' => $value]) }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed gy-4">
                    <thead>
                        <tr class="text-muted fw-bold fs-7 text-uppercase">
                            <th>Comentario</th>
                            <th>Usuario</th>
                            <th>Publicado en</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($comments as $comment)
                            @php
                                $target = $comment->commentable;
                                $targetLabel = 'Contenido eliminado';
                                $targetUrl = null;

                                if ($target instanceof \App\Models\Episode) {
                                    $targetLabel = ($target->series?->title ? $target->series->title.' · ' : '').'Episodio '.$target->episode_number.' · '.$target->title;
                                    $targetUrl = route('public.episodes.show', $target->slug).'#comment-'.$comment->id;
                                } elseif ($target instanceof \App\Models\Series) {
                                    $targetLabel = $target->title;
                                    $targetUrl = route('catalog.series.show', $target->slug).'#comment-'.$comment->id;
                                }
                            @endphp
                            <tr>
                                <td class="min-w-350px">
                                    <div class="text-gray-900 fw-semibold">{{ \Illuminate\Support\Str::limit($comment->body, 180) }}</div>
                                    <div class="text-muted fs-8 mt-1">
                                        {{ $comment->parent_id ? 'Respuesta' : 'Comentario principal' }} · {{ $comment->created_at->timezone('America/Merida')->format('d/m/Y g:i a') }}
                                        @if($comment->parent)
                                            · responde a {{ $comment->parent->display_alias }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-gray-900">{{ $comment->display_alias }}</div>
                                    <div class="text-muted fs-8">{{ $comment->user?->email ?: 'Invitada / anónima' }}</div>
                                </td>
                                <td class="min-w-250px">
                                    <div class="fw-semibold text-gray-900">{{ $targetLabel }}</div>
                                    <div class="text-muted fs-8">{{ $target instanceof \App\Models\Episode ? 'Episodio' : ($target instanceof \App\Models\Series ? 'Serie o película' : 'Sin destino') }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-light-{{ $comment->is_approved ? 'success' : 'warning' }}">{{ $comment->is_approved ? 'Visible' : 'Oculto' }}</span>
                                </td>
                                <td class="text-end min-w-250px">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        @if($targetUrl)
                                            <a href="{{ $targetUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-light-primary">Abrir página</a>
                                        @endif
                                        <form method="POST" action="{{ route('admin.comments.update', $comment) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_approved" value="{{ $comment->is_approved ? 0 : 1 }}">
                                            <button class="btn btn-sm {{ $comment->is_approved ? 'btn-light-warning' : 'btn-light-success' }}">
                                                {{ $comment->is_approved ? 'Ocultar' : 'Restaurar' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('¿Eliminar este comentario? Esta acción también elimina sus respuestas.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-light-danger">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-8">No hay comentarios para este filtro.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $comments->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
