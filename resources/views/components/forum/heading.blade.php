@props(['eyebrow', 'title'])

<header {{ $attributes->class(['forum-thread-header', 'community-conversation-heading']) }}>
    <div class="community-conversation-heading-copy">
        <span class="profile-eyebrow">{{ $eyebrow }}</span>
        <h1>{{ $title }}</h1>
        <div class="community-conversation-description">{{ $slot }}</div>
    </div>
    @if(isset($actions) && trim((string) $actions) !== '')
        <div class="forum-thread-header-actions">{{ $actions }}</div>
    @endif
</header>
