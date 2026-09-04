<?php

namespace App\Notifications;

use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class QuestionAnswerAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ForumThread $question,
        public ForumPost $answer,
        public User $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'question_answer_accepted',
            'title' => $this->actor->displayName().' aceptó tu respuesta',
            'message' => Str::limit($this->question->title, 100),
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->displayName(),
            'actor_avatar' => $this->actor->avatarUrl(),
            'forum_thread_id' => $this->question->id,
            'forum_post_id' => $this->answer->id,
            'url' => route('questions.show', $this->question).'#post-'.$this->answer->id,
        ];
    }
}
