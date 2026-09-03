<?php

namespace App\Http\Requests\Admin;

use App\Models\Badge;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserBadgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'badge_id' => [
                'required',
                'integer',
                Rule::exists(Badge::class, 'id')->where('is_active', true),
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
