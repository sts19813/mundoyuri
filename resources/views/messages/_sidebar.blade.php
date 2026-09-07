<aside class="messenger-sidebar" aria-label="Conversaciones">
    <header class="messenger-sidebar-header">
        <div>
            <span>Mensajería privada</span>
            <h1>Chats</h1>
        </div>
        <a href="{{ route('community.members') }}" class="messenger-new-chat" aria-label="Iniciar una conversación" title="Nueva conversación">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
        </a>
    </header>

    @php($conversationSearchUrl = $activeUser ? route('messages.show', $activeUser) : route('messages.index'))
    <form action="{{ $conversationSearchUrl }}" method="GET" class="messenger-search" role="search">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <label class="visually-hidden" for="messenger-search-input">Buscar conversaciones</label>
        <input id="messenger-search-input" type="search" name="q" value="{{ $search }}" placeholder="Buscar en Messenger">
        @if($search !== '')
            <a href="{{ $conversationSearchUrl }}" aria-label="Limpiar búsqueda">×</a>
        @endif
    </form>

    <div class="messenger-conversation-list">
        @forelse($conversations as $conversationItem)
            @php($conversationUser = $conversationItem->otherParticipant($viewer))
            @php($lastMessage = $conversationItem->lastMessage)
            <a
                href="{{ route('messages.show', $conversationUser) }}"
                @class([
                    'messenger-conversation-item',
                    'is-active' => $activeUser?->is($conversationUser),
                    'is-unread' => $conversationItem->unread_messages_count,
                ])
                @if($activeUser?->is($conversationUser)) aria-current="page" @endif
            >
                @if($conversationUser->hasProfileAvatar())
                    <img class="messenger-avatar" src="{{ $conversationUser->avatarUrl() }}" alt="">
                @else
                    <span class="messenger-avatar messenger-avatar-fallback" aria-hidden="true">{{ $conversationUser->initials() }}</span>
                @endif
                <span class="messenger-conversation-copy">
                    <span class="messenger-conversation-line">
                        <strong>{{ $conversationUser->alias ?: $conversationUser->name }}</strong>
                        <time datetime="{{ optional($conversationItem->last_message_at)->toIso8601String() }}">
                            {{ optional($conversationItem->last_message_at)->isToday() ? optional($conversationItem->last_message_at)->format('H:i') : optional($conversationItem->last_message_at)->translatedFormat('d M') }}
                        </time>
                    </span>
                    <span class="messenger-conversation-preview">
                        @if($lastMessage?->sender_id === $viewer->id)<span>Tú: </span>@endif{{ filled($lastMessage?->body) ? \Illuminate\Support\Str::limit($lastMessage->body, 72) : ($lastMessage?->attachment_name ? 'Archivo: '.$lastMessage->attachment_name : 'Sin mensajes') }}
                    </span>
                </span>
                @if($conversationItem->unread_messages_count)
                    <span class="messenger-unread-count" aria-label="{{ $conversationItem->unread_messages_count }} mensajes sin leer">{{ $conversationItem->unread_messages_count > 99 ? '99+' : $conversationItem->unread_messages_count }}</span>
                @endif
            </a>
        @empty
            <div class="messenger-list-empty">
                <span aria-hidden="true">♡</span>
                @if($search !== '')
                    <strong>No encontramos ese chat</strong>
                    <p>Prueba con otro nombre o alias.</p>
                @else
                    <strong>Aún no tienes conversaciones</strong>
                    <p>Visita la comunidad para enviar tu primer mensaje.</p>
                    <a href="{{ route('community.members') }}">Ver personas</a>
                @endif
            </div>
        @endforelse
    </div>

    @if($conversations->hasPages())
        <nav class="messenger-sidebar-pagination" aria-label="Más conversaciones">
            @if($conversations->previousPageUrl())<a href="{{ $conversations->previousPageUrl() }}">← Anteriores</a>@else<span></span>@endif
            @if($conversations->nextPageUrl())<a href="{{ $conversations->nextPageUrl() }}">Siguientes →</a>@endif
        </nav>
    @endif
</aside>
