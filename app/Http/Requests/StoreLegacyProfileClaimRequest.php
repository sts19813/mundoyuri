<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLegacyProfileClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'legacy_profile_id' => ['required', 'integer', 'exists:legacy_profiles,id'],
            'message' => ['required', 'string', 'min:20', 'max:3000'],
            'evidence' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
