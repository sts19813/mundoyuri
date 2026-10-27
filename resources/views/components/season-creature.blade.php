@props(['kind', 'variant' => 'one'])

<div class="halloween-creature halloween-creature--{{ $kind }} halloween-creature--{{ $variant }}" @if($kind !== 'bat') data-halloween-walker="{{ $kind }}" @endif>
    <span class="halloween-creature__body">
        @if($kind === 'ant')
            <img src="{{ asset('assets/img/seasonal/ant.svg') }}" width="24" height="38" alt="" decoding="async" draggable="false">
        @else
            <img src="{{ asset('assets/img/seasonal/'.$kind.'.webp') }}" width="{{ $kind === 'spider' ? 480 : 640 }}" height="{{ $kind === 'spider' ? 439 : 427 }}" alt="" decoding="async" draggable="false">
        @endif
    </span>
</div>
