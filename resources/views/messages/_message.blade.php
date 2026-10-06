@php($outgoing = $message->sender_id === $viewer->id)
<article class="messenger-message {{ $outgoing ? 'is-outgoing' : 'is-incoming' }}" data-message-id="{{ $message->id }}">
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
        <time datetime="{{ $message->created_at->toIso8601String() }}">
            {{ $message->created_at->timezone('America/Merida')->format('d M · g:i a') }}
            @if($outgoing)<span data-message-read-status aria-label="{{ $message->read_at ? 'Leído' : 'Enviado' }}">{{ $message->read_at ? '✓✓' : '✓' }}</span>@endif
        </time>
        @if($outgoing && ! $message->isDeleted())
            <form method="POST" action="{{ route('messages.destroy', $message) }}" class="messenger-delete-form" onsubmit="return confirm('¿Eliminar este mensaje? Se ocultará de la conversación.');">
                @csrf
                @method('DELETE')
                <button type="submit">Eliminar</button>
            </form>
        @endif
    </div>
</article>
