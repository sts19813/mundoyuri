@php($outgoing = $message->sender_id === $viewer->id)
@php($messageAuthor = $message->sender_id === $viewer->id ? 'Tú' : $otherUser->displayName())
@php($reactionTypes = \App\Models\CommunityReaction::types())
@php($reactionSummary = $message->reaction_summary ?? array_fill_keys(array_keys($reactionTypes), 0))
@php($viewerReaction = $message->viewer_reaction_type ?? null)
@php($selectedReaction = $reactionTypes[$viewerReaction] ?? null)
@php($receivedReactions = collect($reactionSummary)->filter(fn ($count) => $count > 0)->sortDesc())
@php($reactionTotal = $receivedReactions->sum())
<article
    id="message-{{ $message->id }}"
    @class([
        'messenger-message',
        'is-outgoing' => $outgoing,
        'is-incoming' => ! $outgoing,
        'has-reactions' => ! $message->isDeleted() && $reactionTotal > 0,
    ])
    data-message-id="{{ $message->id }}"
    data-message-author="{{ $messageAuthor }}"
    data-message-preview="{{ $message->previewText() }}"
>
    @if(!$outgoing)
        <span @class(['messenger-avatar-wrap', 'messenger-message-avatar-wrap', 'is-online' => $otherUser->isOnlineForMessages()])>
            @if($otherUser->hasProfileAvatar())
                <img class="messenger-message-avatar" src="{{ $otherUser->avatarUrl() }}" alt="">
            @else
                <span class="messenger-message-avatar messenger-avatar-fallback" aria-hidden="true">{{ $otherUser->initials() }}</span>
            @endif
        </span>
    @endif
    <div class="messenger-bubble">
        @if($message->isDeleted())
            <p class="messenger-deleted-message">Mensaje eliminado</p>
        @else
            @if($message->replyTo)
                <a class="messenger-reply-quote" href="#message-{{ $message->replyTo->id }}" data-jump-message="{{ $message->replyTo->id }}">
                    <strong>{{ $message->replyTo->sender_id === $viewer->id ? 'Tú' : $message->replyTo->sender?->displayName() }}</strong>
                    <span>{{ $message->replyTo->previewText() }}</span>
                </a>
            @endif
            @if(filled($message->body))<p>{{ $message->body }}</p>@endif
            @if($message->hasAttachment())
                @if($message->attachmentIsImage())
                    <a class="messenger-attachment-image" href="{{ route('messages.attachments.show', $message) }}" target="_blank" rel="noopener">
                        <img src="{{ route('messages.attachments.show', $message) }}" alt="{{ $message->attachment_name }}" loading="lazy">
                    </a>
                @else
                    <a class="messenger-attachment-file" href="{{ route('messages.attachments.show', $message) }}">
                        <span aria-hidden="true">▤</span>
                        <span><strong>{{ $message->attachment_name }}</strong><small>{{ $message->attachmentSizeLabel() }}</small></span>
                    </a>
                @endif
            @endif
        @endif
        @if(!$message->isDeleted() && $reactionTotal > 0)
            <div class="messenger-reaction-summary" aria-label="{{ $reactionTotal }} reacciones">
                @foreach($receivedReactions->take(3) as $type => $count)
                    <span title="{{ $reactionTypes[$type]['label'] }}">{{ $reactionTypes[$type]['emoji'] }}</span>
                @endforeach
                <small>{{ number_format($reactionTotal) }}</small>
            </div>
        @endif
        <time datetime="{{ $message->created_at->toIso8601String() }}">
            {{ $message->created_at->timezone('America/Merida')->format('d M · g:i a') }}
            @if($outgoing)<span data-message-read-status aria-label="{{ $message->read_at ? 'Leído' : 'Enviado' }}">{{ $message->read_at ? '✓✓' : '✓' }}</span>@endif
        </time>
        @if(! $message->isDeleted())
            <details class="messenger-message-menu">
                <summary aria-label="Opciones del mensaje">•••</summary>
                <div class="messenger-message-menu-panel">
                    <button type="button" data-message-reply>Responder</button>
                    <div class="messenger-message-reactions" role="group" aria-label="Reaccionar">
                        @foreach($reactionTypes as $type => $reaction)
                            <form method="POST" action="{{ route('messages.reactions.store', $message) }}" data-message-reaction-form>
                                @csrf
                                <input type="hidden" name="type" value="{{ $type }}">
                                <button type="submit" @class(['is-active' => $viewerReaction === $type]) title="{{ $reaction['label'] }}" aria-pressed="{{ $viewerReaction === $type ? 'true' : 'false' }}" aria-label="{{ $reaction['label'] }}">
                                    <span aria-hidden="true">{{ $reaction['emoji'] }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                    @if($outgoing)
                        <form method="POST" action="{{ route('messages.destroy', $message) }}" onsubmit="return confirm('¿Eliminar este mensaje? Se ocultará de la conversación.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Eliminar</button>
                        </form>
                    @endif
                </div>
            </details>
        @endif
    </div>
</article>
