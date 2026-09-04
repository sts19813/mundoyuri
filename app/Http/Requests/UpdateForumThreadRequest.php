<?php

namespace App\Http\Requests;

use App\Models\ForumThread;
use Illuminate\Foundation\Http\FormRequest;

class UpdateForumThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ForumThread $thread */
        $thread = $this->route('thread');

        return $this->user()?->can('update', $thread) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'body' => ['required', 'string', 'min:2', 'max:12000'],
        ];
    }
}
