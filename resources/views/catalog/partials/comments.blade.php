@php
    $commentCount = $comments->sum(fn ($comment) => 1 + $comment->replies->count());
    $replyTo = (int) old('parent_id');
@endphp

<div class="comments-section mt-5">
    <h4 class="mb-3">Comentarios ({{ $commentCount }})</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @forelse($comments as $comment)
        @php $replyFormId = 'catalog-reply-form-'.$comment->id; @endphp
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0">
                        @if($comment->avatarUrl())
                            <img src="{{ $comment->avatarUrl() }}" alt="{{ $comment->display_alias }}" class="rounded-circle" width="42" height="42" style="object-fit:cover;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:42px;height:42px;">
                                {{ $comment->initials() }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>{{ $comment->display_alias }}</strong>
                            <small class="text-muted">{{ $comment->displayTime() }}</small>
                        </div>
                        <p class="mb-2">{{ $comment->body }}</p>
                        <button class="btn btn-sm btn-light-primary" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $replyFormId }}">
                            Responder
                        </button>

                        <div class="collapse {{ $replyTo === $comment->id ? 'show' : '' }} mt-3" id="{{ $replyFormId }}">
                            <form method="POST" action="{{ route('comments.store') }}" class="border rounded p-3">
                                @csrf
                                <input type="hidden" name="target_type" value="{{ $targetType }}">
                                <input type="hidden" name="target_id" value="{{ $targetId }}">
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">

                                @guest
                                    <div class="mb-3">
                                        <label class="form-label">Alias</label>
                                        <input type="text" class="form-control @if($replyTo === $comment->id) @error('alias') is-invalid @enderror @endif" name="alias" value="{{ $replyTo === $comment->id ? old('alias') : '' }}" placeholder="Tu alias">
                                        @if($replyTo === $comment->id)
                                            @error('alias')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-light-primary mb-3">
                                        Responderas como <strong>{{ auth()->user()->alias ?: auth()->user()->name }}</strong>.
                                    </div>
                                @endguest

                                <div class="mb-3">
                                    <label class="form-label">Respuesta</label>
                                    <textarea class="form-control @if($replyTo === $comment->id) @error('body') is-invalid @enderror @endif" rows="3" name="body" placeholder="Escribe tu respuesta...">{{ $replyTo === $comment->id ? old('body') : '' }}</textarea>
                                    @if($replyTo === $comment->id)
                                        @error('body')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        @error('parent_id')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>

                                <button class="btn btn-primary btn-sm" type="submit">Enviar respuesta</button>
                            </form>
                        </div>

                        @if($comment->replies->isNotEmpty())
                            <div class="mt-3 ps-3 border-start">
                                @foreach($comment->replies as $reply)
                                    <div class="d-flex gap-3 mb-3">
                                        <div class="flex-shrink-0">
                                            @if($reply->avatarUrl())
                                                <img src="{{ $reply->avatarUrl() }}" alt="{{ $reply->display_alias }}" class="rounded-circle" width="32" height="32" style="object-fit:cover;">
                                            @else
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:.8rem;">
                                                    {{ $reply->initials() }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong class="fs-8">{{ $reply->display_alias }}</strong>
                                                <small class="text-muted">{{ $reply->displayTime() }}</small>
                                            </div>
                                            <div class="fs-7">{{ $reply->body }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-muted mb-3">Todavía no hay comentarios.</div>
    @endforelse

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
                        <input type="text" class="form-control @if(!$replyTo) @error('alias') is-invalid @enderror @endif" name="alias" value="{{ $replyTo ? '' : old('alias') }}" placeholder="Tu alias para comentar">
                        @if(!$replyTo)
                            @error('alias')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                @else
                    <div class="alert alert-light-primary mb-3">
                        Comentaras como <strong>{{ auth()->user()->alias ?: auth()->user()->name }}</strong>.
                    </div>
                @endguest

                <div class="mb-3">
                    <label class="form-label">Comentario</label>
                    <textarea class="form-control @if(!$replyTo) @error('body') is-invalid @enderror @endif" rows="4" name="body" placeholder="Escribe tu comentario...">{{ $replyTo ? '' : old('body') }}</textarea>
                    @if(!$replyTo)
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    @endif
                </div>

                <button class="btn btn-primary" type="submit">Enviar comentario</button>
            </form>
        </div>
    </div>
</div>
