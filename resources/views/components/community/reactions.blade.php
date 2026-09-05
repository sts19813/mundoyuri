@props(['reactable'])

@php
    $summary = $reactable->reaction_summary ?? array_fill_keys(array_keys(\App\Models\CommunityReaction::types()), 0);
    $viewerReaction = $reactable->viewer_reaction_type ?? null;
    $target = \App\Models\CommunityReaction::targetKeyFor($reactable);
    $types = \App\Models\CommunityReaction::types();
    $selected = $types[$viewerReaction] ?? null;
    $total = array_sum($summary);
    $received = collect($summary)->filter(fn ($count) => $count > 0)->sortDesc();
    $breakdown = $received->map(fn ($count, $type) => $types[$type]['label'].': '.$count)->implode(', ');
@endphp

@if($target)
    <div class="community-reactions" data-reaction-control aria-label="Reacciones">
        @auth
            <details class="community-reaction-picker">
                <summary class="community-reaction-trigger {{ $selected ? 'is-active' : '' }}" aria-label="{{ $selected ? 'Cambiar reacción: '.$selected['label'] : 'Elegir una reacción' }}">
                    <span aria-hidden="true">{{ $selected['emoji'] ?? '♡' }}</span>
                    {{ $selected['label'] ?? 'Reaccionar' }}
                </summary>
                <div class="community-reaction-palette">
                    <span class="community-reaction-hint">{{ $selected ? 'Elige otra o toca la misma para retirarla' : '¿Qué te hizo sentir?' }}</span>
                    <div class="community-reaction-options" role="group" aria-label="Elige una reacción">
                        @foreach($types as $type => $reaction)
                            <form method="POST" action="{{ route('community.reactions.store') }}">
                                @csrf
                                <input type="hidden" name="target" value="{{ $target }}">
                                <input type="hidden" name="target_id" value="{{ $reactable->id }}">
                                <input type="hidden" name="type" value="{{ $type }}">
                                <button type="submit" class="community-reaction {{ $viewerReaction === $type ? 'is-active' : '' }}" title="{{ $reaction['label'] }}" aria-pressed="{{ $viewerReaction === $type ? 'true' : 'false' }}" aria-label="{{ $reaction['label'] }}">
                                    <span aria-hidden="true">{{ $reaction['emoji'] }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </details>
        @else
            <a href="{{ route('login') }}" class="community-reaction-trigger" aria-label="Inicia sesión para reaccionar"><span aria-hidden="true">♡</span> Reaccionar</a>
        @endauth
        @if($total > 0)
            <span class="community-reaction-total" title="{{ $breakdown }}" aria-label="{{ $total }} reacciones. {{ $breakdown }}">
                <span class="community-reaction-faces" aria-hidden="true">@foreach($received->take(3) as $type => $count)<span>{{ $types[$type]['emoji'] }}</span>@endforeach</span>
                <span>{{ number_format($total) }}</span>
            </span>
        @endif
        <span class="community-reaction-feedback" role="status"></span>
    </div>
@endif
