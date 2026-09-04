@extends('layouts.admin')

@section('title', 'Temas de foros - Admin')

@section('toolbar')
    <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Temas de foros</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table align-middle">
                <thead><tr><th>Tema</th><th>Foro</th><th>Autor</th><th>Respuestas</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                @forelse($threads as $thread)
                    <tr>
                        <td>{{ $thread->title }}</td>
                        <td>{{ $thread->forum->name }}</td>
                        <td>{{ $thread->authorName() }}</td>
                        <td>{{ number_format($thread->replies_count) }}</td>
                        <td>{{ $thread->is_hidden ? 'Oculto' : ($thread->is_locked ? 'Cerrado' : 'Visible') }}{{ $thread->is_pinned ? ' · Fijado' : '' }}</td>
                        <td class="text-end"><a href="{{ $thread->isQuestion() ? route('questions.show', $thread) : route('forum.threads.show', $thread) }}" class="btn btn-sm btn-light-primary">Abrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No hay temas todavía.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $threads->links() }}
        </div>
    </div>
@endsection
