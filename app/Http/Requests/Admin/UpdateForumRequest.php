<?php

namespace App\Http\Requests\Admin;

use App\Models\Forum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateForumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage', Forum::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name')))]);
    }

    public function rules(): array
    {
        /** @var Forum $forum */ $forum = $this->route('forum');

        return ['forum_category_id' => ['required', 'exists:forum_categories,id'], 'name' => ['required', 'string', 'max:120'], 'slug' => ['required', 'max:150', 'alpha_dash', Rule::unique(Forum::class)->ignore($forum)], 'description' => ['nullable', 'string', 'max:3000'], 'icon' => ['nullable', 'string', 'max:80'], 'sort_order' => ['required', 'integer', 'min:0'], 'is_locked' => ['required', 'boolean'], 'minimum_role' => ['nullable', Rule::in(['user', 'moderator', 'admin'])]];
    }
}
