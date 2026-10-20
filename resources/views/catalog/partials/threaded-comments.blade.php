@php
    $flattenTree = function ($nodes) use (&$flattenTree) {
        return $nodes->flatMap(fn ($comment) => collect([$comment])->concat($flattenTree($comment->treeReplies)));
    };
    $renderedComments = new \Illuminate\Database\Eloquent\Collection($flattenTree($comments)->all());
    app(\App\Services\CommunityReactionService::class)->hydrateSummaries($renderedComments, auth()->user());
    $commentCount = $renderedComments->count();
    $replyTo = (int) old('parent_id');
    $previousRootSignatureUserId = null;
    $commentPrompts = $targetType === 'episode'
        ? [
            '¿Qué te pareció este capítulo?',
            '¿Qué escena te dejó pensando?',
            '¿Qué crees que pasará después?',
            '¿Hubo algún momento que te sorprendió?',
            '¿Con qué personaje conectaste más?',
        ]
        : [
            '¿Qué te pareció esta serie?',
            '¿Por qué la recomendarías?',
            '¿Cuál fue tu personaje favorito?',
            '¿Qué fue lo que más te atrapó?',
        ];
    $commentPrompt = $commentPrompts[array_rand($commentPrompts)];
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
        <p class="comments-empty">Aún nadie ha comentado. Puedes abrir la conversación.</p>
    @endforelse

    <div class="comment-form">
        <div class="comment-form-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
            {{ $commentPrompt }}
        </div>

        @auth
            <form method="POST" action="{{ route('comments.store') }}">
                @csrf
                <input type="hidden" name="target_type" value="{{ $targetType }}">
                <input type="hidden" name="target_id" value="{{ $targetId }}">
                <textarea class="cf-textarea" name="body" required minlength="2" maxlength="2500" placeholder="Escribe lo que pensaste…">{{ $replyTo ? '' : old('body') }}</textarea>
                @if(!$replyTo)
                    @error('body')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                @endif
                <button class="cf-submit" type="submit">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13" />
                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                    </svg>
                    Comentar
                </button>
            </form>
        @else
            <textarea
                class="cf-textarea cf-textarea-guest"
                readonly
                data-auth-open="register"
                aria-label="Regístrate o inicia sesión para comentar"
                placeholder="Regístrate o inicia sesión para escribir tu comentario…"
            ></textarea>
        @endauth
    </div>
</div>

@once
    <script src="{{ asset('assets/js/forum.js') }}?v={{ filemtime(public_path('assets/js/forum.js')) }}" defer></script>
@endonce
