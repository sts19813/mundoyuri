@props(['user', 'limit' => 3, 'compact' => false, 'detailed' => false])

@if($user->relationLoaded('badges'))
    @foreach($user->badges->take((int) $limit) as $badge)
        <x-community.badge :badge="$badge" :compact="$compact" :detailed="$detailed" />
    @endforeach
@endif
