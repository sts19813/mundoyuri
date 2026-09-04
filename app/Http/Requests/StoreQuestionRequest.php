<?php

namespace App\Http\Requests;

use App\Models\Forum;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $forum = Forum::query()->find($this->integer('forum_id'));

        return $forum !== null && ($this->user()?->can('createTopic', $forum) ?? false);
    }

    public function rules(): array
    {
        return [
            'forum_id' => ['required', 'integer', 'exists:forums,id'],
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'body' => ['required', 'string', 'min:2', 'max:12000'],
            'tags' => ['nullable', 'array', 'max:5'],
            'tags.*' => ['nullable', 'string', 'min:2', 'max:50', 'regex:/^[\pL\pN][\pL\pN\s-]*$/u'],
        ];
    }
}
