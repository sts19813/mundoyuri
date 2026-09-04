<?php

namespace App\Http\Requests\Admin;

use App\Models\Forum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreForumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage', Forum::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name'))),
            'is_active' => $this->has('is_active') ? $this->input('is_active') : true,
        ]);
    }

    public function rules(): array
    {
        return ['forum_category_id' => ['required', 'exists:forum_categories,id'], 'name' => ['required', 'string', 'max:120'], 'slug' => ['required', 'max:150', 'alpha_dash', Rule::unique(Forum::class)], 'description' => ['nullable', 'string', 'max:3000'], 'icon' => ['nullable', 'string', 'max:80'], 'sort_order' => ['required', 'integer', 'min:0'], 'is_active' => ['required', 'boolean'], 'is_locked' => ['required', 'boolean'], 'minimum_role' => ['nullable', Rule::in(['user', 'moderator', 'admin'])]];
    }
}
