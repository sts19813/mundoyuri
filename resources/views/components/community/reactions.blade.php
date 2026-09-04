@props(['reactable'])

@php
    $summary = $reactable->reaction_summary ?? array_fill_keys(array_keys(\App\Models\CommunityReaction::types()), 0);
    $viewerReaction = $reactable->viewer_reaction_type ?? null;
    $target = \App\Models\CommunityReaction::targetKeyFor($reactable);
@endphp

@if($target)
    <div class="community-reactions" aria-label="Reacciones">
        @foreach(\App\Models\CommunityReaction::types() as $type => $reaction)
            @auth
                <form method="POST" action="{{ route('community.reactions.store') }}">
                    @csrf
                    <input type="hidden" name="target" value="{{ $target }}">
                    <input type="hidden" name="target_id" value="{{ $reactable->id }}">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <button type="submit" class="community-reaction {{ $viewerReaction === $type ? 'is-active' : '' }}" title="{{ $reaction['label'] }}" aria-label="{{ $reaction['label'] }}{{ ($summary[$type] ?? 0) ? ': '.number_format($summary[$type]) : '' }}">
                        <span aria-hidden="true">{{ $reaction['emoji'] }}</span>@if(($summary[$type] ?? 0) > 0)<small>{{ number_format($summary[$type]) }}</small>@endif
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="community-reaction" title="Inicia sesión para reaccionar" aria-label="{{ $reaction['label'] }}{{ ($summary[$type] ?? 0) ? ': '.number_format($summary[$type]) : '' }}">
                    <span aria-hidden="true">{{ $reaction['emoji'] }}</span>@if(($summary[$type] ?? 0) > 0)<small>{{ number_format($summary[$type]) }}</small>@endif
                </a>
            @endauth
        @endforeach
    </div>
@endif
