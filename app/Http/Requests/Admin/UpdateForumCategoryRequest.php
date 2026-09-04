<?php

namespace App\Http\Requests\Admin;

use App\Models\ForumCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateForumCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage', ForumCategory::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name')))]);
    }

    public function rules(): array
    {
        /** @var ForumCategory $category */ $category = $this->route('forumCategory');

        return ['name' => ['required', 'string', 'max:120'], 'slug' => ['required', 'max:150', 'alpha_dash', Rule::unique(ForumCategory::class)->ignore($category)], 'description' => ['nullable', 'string', 'max:3000'], 'icon' => ['nullable', 'string', 'max:80'], 'sort_order' => ['required', 'integer', 'min:0'], 'is_active' => ['required', 'boolean']];
    }
}
