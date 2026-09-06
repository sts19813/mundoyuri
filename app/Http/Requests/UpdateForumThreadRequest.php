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
            'body' => ['nullable', 'string', 'min:2', 'max:12000'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            /** @var ForumThread $thread */
            $thread = $this->route('thread');
            $currentImage = $thread->posts()->where('is_initial', true)->value('image_path');
            $keepsCurrentImage = filled($currentImage) && ! $this->boolean('remove_image');

            if (blank($this->input('body')) && ! $this->hasFile('image') && ! $keepsCurrentImage) {
                $validator->errors()->add('body', 'Escribe un mensaje o conserva una imagen en el tema.');
            }
        }];
    }
}
