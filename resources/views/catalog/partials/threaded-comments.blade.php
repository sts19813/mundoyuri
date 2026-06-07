@php
    $avatarClasses = $avatarClasses ?? ['', 'av2', 'av3'];
    $commentCount = $comments->sum(fn ($comment) => 1 + $comment->replies->count());
    $replyTo = (int) old('parent_id');
@endphp

<div class="comments-section">
    <div class="comments-header">
        <div class="comments-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
        </div>
        <span class="comments-title">Comentarios</span>
        <span class="comments-count">{{ $commentCount }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @forelse($comments as $comment)
        @php
            $avatarClass = $avatarClasses[$loop->index % count($avatarClasses)];
            $replyFormId = 'reply-form-'.$comment->id;
        @endphp
        <div class="comment-item">
            <div class="comment-avatar {{ $avatarClass }}">
                @if($comment->avatarUrl())
                    <img src="{{ $comment->avatarUrl() }}" alt="{{ $comment->display_alias }}">
                @else
                    {{ $comment->initials() }}
                @endif
            </div>
            <div class="comment-body">
                <div class="comment-meta">
                    <span class="comment-user">{{ $comment->display_alias }}</span>
                    <span class="comment-date">{{ $comment->displayTime() }}</span>
                </div>
                <p class="comment-text">{{ $comment->body }}</p>

                <div class="comment-actions">
                    <button class="comment-action-btn" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $replyFormId }}"
                        aria-expanded="{{ $replyTo === $comment->id ? 'true' : 'false' }}" aria-controls="{{ $replyFormId }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 17 4 12 9 7"></polyline>
                            <path d="M20 18v-2a4 4 0 0 0-4-4H4"></path>
                        </svg>
                        Responder
                    </button>
                </div>

                <div class="collapse {{ $replyTo === $comment->id ? 'show' : '' }}" id="{{ $replyFormId }}">
                    <form class="comment-reply-form" method="POST" action="{{ route('comments.store') }}">
                        @csrf
                        <input type="hidden" name="target_type" value="{{ $targetType }}">
                        <input type="hidden" name="target_id" value="{{ $targetId }}">
                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">

                        <textarea class="cf-textarea" name="body" placeholder="Responder a {{ $comment->display_alias }}...">{{ $replyTo === $comment->id ? old('body') : '' }}</textarea>
                        @if($replyTo === $comment->id)
                            @error('body')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror
                            @error('parent_id')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror
                        @endif

                        <div class="cf-fields">
                            @guest
                                <div class="cf-field">
                                    <label>Alias <span>*</span></label>
                                    <input type="text" name="alias" class="cf-input" placeholder="Tu alias"
                                        value="{{ $replyTo === $comment->id ? old('alias') : '' }}">
                                    @if($replyTo === $comment->id)
                                        @error('alias')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                            @else
                                <div class="cf-field">
                                    <label>Responderás como</label>
                                    <input type="text" class="cf-input" value="{{ auth()->user()->alias ?: auth()->user()->name }}" disabled>
                                </div>
                            @endguest
                        </div>

                        <button class="cf-submit cf-submit-sm" type="submit">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13" />
                                <polygon points="22 2 15 22 11 13 2 9 22 2" />
                            </svg>
                            Publicar respuesta
                        </button>
                    </form>
                </div>

                @foreach($comment->replies as $reply)
                    <div class="comment-reply">
                        <div class="comment-reply-inner">
                            <div class="comment-avatar av2">
                                @if($reply->avatarUrl())
                                    <img src="{{ $reply->avatarUrl() }}" alt="{{ $reply->display_alias }}">
                                @else
                                    {{ $reply->initials() }}
                                @endif
                            </div>
                            <div class="comment-reply-body">
                                <div class="comment-meta">
                                    <span class="comment-user">{{ $reply->display_alias }}</span>
                                    <span class="comment-date">{{ $reply->displayTime() }}</span>
                                </div>
                                <p class="comment-text mb-1">{{ $reply->body }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-muted small mb-4">Todavía no hay comentarios. Sé la primera persona en comentar.</div>
    @endforelse

    <div class="comment-form">
        <form method="POST" action="{{ route('comments.store') }}">
            @csrf
            <input type="hidden" name="target_type" value="{{ $targetType }}">
            <input type="hidden" name="target_id" value="{{ $targetId }}">

            <div class="comment-form-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                </svg>
                Deja un comentario
            </div>

            <textarea class="cf-textarea" name="body" placeholder="Tu comentario...">{{ $replyTo ? '' : old('body') }}</textarea>
            @if(!$replyTo)
                @error('body')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror
            @endif

            <div class="cf-fields">
                @guest
                    <div class="cf-field">
                        <label>Alias <span>*</span></label>
                        <input type="text" name="alias" class="cf-input" placeholder="Tu alias" value="{{ $replyTo ? '' : old('alias') }}">
                        @if(!$replyTo)
                            @error('alias')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                @else
                    <div class="cf-field">
                        <label>Comentarás como</label>
                        <input type="text" class="cf-input" value="{{ auth()->user()->alias ?: auth()->user()->name }}" disabled>
                    </div>
                @endguest

                <div class="cf-field">
                    <label>Correo electrónico</label>
                    <input type="email" class="cf-input" placeholder="No será publicado" disabled>
                </div>
            </div>

            <div class="cf-check-row">
                <input type="checkbox" class="cf-check" id="saveInfo-{{ $targetType }}-{{ $targetId }}" disabled>
                <label for="saveInfo-{{ $targetType }}-{{ $targetId }}">Guarda mi nombre y correo para la próxima vez que comente</label>
            </div>
            <button class="cf-submit" type="submit">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13" />
                    <polygon points="22 2 15 22 11 13 2 9 22 2" />
                </svg>
                Publicar comentario
            </button>
        </form>
    </div>
</div>
