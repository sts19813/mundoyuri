<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewLegacyProfileClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('review', $this->route('legacyProfileClaim')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['approved', 'rejected'])],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
