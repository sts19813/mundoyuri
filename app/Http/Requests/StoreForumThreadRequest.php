<?php

namespace App\Http\Requests;

use App\Models\Forum;
use Illuminate\Foundation\Http\FormRequest;

class StoreForumThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Forum $forum */
        $forum = $this->route('forum');

        return $this->user()?->can('createTopic', $forum) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'body' => ['required', 'string', 'min:2', 'max:12000'],
        ];
    }
}
