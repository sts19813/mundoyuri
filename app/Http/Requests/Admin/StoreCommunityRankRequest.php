<?php

namespace App\Http\Requests\Admin;

use App\Models\CommunityRank;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCommunityRankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name'))),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique(CommunityRank::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'minimum_posts' => [Rule::requiredIf(! $this->boolean('is_special')), 'nullable', 'integer', 'min:0', 'max:4294967295'],
            'priority' => ['required', 'integer', 'min:0', 'max:65535'],
            'icon' => ['nullable', 'string', 'max:50'],
            'css_class' => ['nullable', 'string', 'max:120', 'regex:/\A[a-zA-Z][a-zA-Z0-9_-]*(?:\s+[a-zA-Z][a-zA-Z0-9_-]*)*\z/'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'is_special' => ['required', 'boolean'],
            'is_legacy' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
