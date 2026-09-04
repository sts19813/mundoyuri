<?php

namespace App\Http\Requests;

use App\Models\ForumPost;
use Illuminate\Foundation\Http\FormRequest;

class UpdateForumPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ForumPost $post */
        $post = $this->route('post');

        return $this->user()?->can('update', $post) ?? false;
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'min:2', 'max:12000']];
    }
}
