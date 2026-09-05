<?php

namespace App\Http\Requests;

use App\Models\ForumThread;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreForumPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ForumThread $thread */
        $thread = $this->route('thread');

        return $this->user()?->can('reply', $thread) ?? false;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:2', 'max:12000'],
            'reply_to_post_id' => ['nullable', 'integer', Rule::exists('forum_posts', 'id')
                ->where('forum_thread_id', $this->route('thread')->id)
                ->where('is_hidden', 0)->whereNull('deleted_at')],
        ];
    }
}
