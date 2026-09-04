<?php

namespace App\Notifications;

use App\Models\CommunityReaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class CommunityReactionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Model $reactable,
        public User $actor,
        public string $reactionType,
        public string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $reaction = CommunityReaction::types()[$this->reactionType];

        return [
            'kind' => 'community_reaction',
            'title' => $this->actor->displayName().' reaccionó '.$reaction['emoji'].' a tu publicación',
            'message' => $reaction['label'],
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->displayName(),
            'actor_avatar' => $this->actor->avatarUrl(),
            'actor_ids' => [$this->actor->id],
            'actor_names' => [$this->actor->displayName()],
            'actor_count' => 1,
            'reaction_type' => $this->reactionType,
            'reactable_type' => $this->reactable->getMorphClass(),
            'reactable_id' => $this->reactable->getKey(),
            'url' => $this->url,
        ];
    }
}
