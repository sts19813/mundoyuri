<div class="comments-section mt-5">
    <h4 class="mb-3">Comentarios ({{ $comments->count() }})</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @foreach($comments as $comment)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>{{ $comment->display_alias }}</strong>
                    <small class="text-muted">{{ $comment->created_at->format('d/m/Y H:i') }}</small>
                </div>
                <p class="mb-0">{{ $comment->body }}</p>

                @if($comment->replies->isNotEmpty())
                    <div class="mt-3 ps-3 border-start">
                        @foreach($comment->replies as $reply)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong class="fs-8">{{ $reply->display_alias }}</strong>
                                    <small class="text-muted">{{ $reply->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <div class="fs-7">{{ $reply->body }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    <div class="card mt-4">
        <div class="card-body">
            <h5 class="mb-3">Publicar comentario</h5>
            <form method="POST" action="{{ route('comments.store') }}">
                @csrf
                <input type="hidden" name="target_type" value="{{ $targetType }}">
                <input type="hidden" name="target_id" value="{{ $targetId }}">

                @guest
                    <div class="mb-3">
                        <label class="form-label">Alias</label>
                        <input type="text" class="form-control @error('alias') is-invalid @enderror" name="alias" value="{{ old('alias') }}" placeholder="Tu alias para comentar">
                        @error('alias')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @else
                    <div class="alert alert-light-primary mb-3">
                        Comentaras como <strong>{{ auth()->user()->alias ?: auth()->user()->name }}</strong>.
                    </div>
                @endguest

                <div class="mb-3">
                    <label class="form-label">Comentario</label>
                    <textarea class="form-control @error('body') is-invalid @enderror" rows="4" name="body" placeholder="Escribe tu comentario...">{{ old('body') }}</textarea>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary" type="submit">Enviar comentario</button>
            </form>
        </div>
    </div>
</div>
