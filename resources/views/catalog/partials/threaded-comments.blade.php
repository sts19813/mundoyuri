@php
    $flattenTree = function ($nodes) use (&$flattenTree) {
        return $nodes->flatMap(fn ($comment) => collect([$comment])->concat($flattenTree($comment->treeReplies)));
    };
    $renderedComments = new \Illuminate\Database\Eloquent\Collection($flattenTree($comments)->all());
    app(\App\Services\CommunityReactionService::class)->hydrateSummaries($renderedComments, auth()->user());
    $commentCount = $renderedComments->count();
    $replyTo = (int) old('parent_id');
    $previousRootSignatureUserId = null;
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
        <div class="comments-notice" role="status">{{ session('success') }}</div>
    @endif

    @forelse($comments as $comment)
        <x-community.catalog-comment-node :comment="$comment" :target-type="$targetType" :target-id="$targetId" :previous-user-id="$previousRootSignatureUserId" />
        @php($previousRootSignatureUserId = $comment->user_id)
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

@once
    <script src="{{ asset('assets/js/forum.js') }}?v={{ filemtime(public_path('assets/js/forum.js')) }}" defer></script>
@endonce
