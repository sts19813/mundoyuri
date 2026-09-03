@props(['user', 'limit' => 3])

@if($user->relationLoaded('badges'))
    @foreach($user->badges->take((int) $limit) as $badge)
        <x-community.badge :badge="$badge" />
    @endforeach
@endif
