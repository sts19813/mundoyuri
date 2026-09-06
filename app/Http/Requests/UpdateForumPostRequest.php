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
        return [
            'body' => ['nullable', 'string', 'min:2', 'max:12000'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            /** @var ForumPost $post */
            $post = $this->route('post');
            $keepsCurrentImage = filled($post->image_path) && ! $this->boolean('remove_image');

            if (blank($this->input('body')) && ! $this->hasFile('image') && ! $keepsCurrentImage) {
                $validator->errors()->add('body', 'Escribe un mensaje o conserva una imagen.');
            }
        }];
    }
}
