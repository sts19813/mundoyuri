<?php

namespace App\Notifications;

use App\Models\ForumPost;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ForumMentionNotification extends Notification
{
    use Queueable;

    public function __construct(public ForumPost $post, public User $actor) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isQuestion = $this->post->thread->isQuestion();

        return [
            'kind' => 'forum_mention',
            'title' => $this->actor->displayName().' te mencionó en '.($isQuestion ? 'una pregunta' : 'el foro'),
            'message' => Str::limit($this->post->body, 100),
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->displayName(),
            'actor_avatar' => $this->actor->avatarUrl(),
            'forum_thread_id' => $this->post->forum_thread_id,
            'forum_post_id' => $this->post->id,
            'url' => route($isQuestion ? 'questions.show' : 'forum.threads.show', $this->post->thread).'#post-'.$this->post->id,
        ];
    }
}
