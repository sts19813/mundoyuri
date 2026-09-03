@props(['user', 'previousUserId' => null])

@if($user && $user->id !== $previousUserId && $user->canDisplaySignatureTo(auth()->user()))
    <footer class="community-post-signature" aria-label="Firma de {{ $user->displayName() }}">
        @if(filled($user->signature_text))
            <p>{{ $user->signature_text }}</p>
        @endif
        @if($user->signatureImageUrl())
            <img src="{{ $user->signatureImageUrl() }}" alt="Firma gráfica de {{ $user->displayName() }}" loading="lazy">
        @endif
    </footer>
@endif
