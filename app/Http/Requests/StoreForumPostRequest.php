<?php

namespace App\Http\Requests;

use App\Models\ForumThread;
use Illuminate\Foundation\Http\FormRequest;

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
        return ['body' => ['required', 'string', 'min:2', 'max:12000']];
    }
}
