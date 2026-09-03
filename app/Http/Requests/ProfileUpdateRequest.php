<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'alias' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'avatar_remove' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'cover_remove' => ['nullable', 'boolean'],
            'biography' => ['nullable', 'string', 'max:600'],
            'profile_visibility' => ['sometimes', Rule::in(['public', 'members', 'private'])],
            'show_last_seen' => ['sometimes', 'boolean'],
            'show_join_date' => ['sometimes', 'boolean'],
            'show_favorites' => ['sometimes', 'boolean'],
            'show_activity' => ['sometimes', 'boolean'],
            'signature_text' => ['nullable', 'string', 'max:1000'],
            'signature_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048', 'dimensions:max_width=800,max_height=300'],
            'signature_remove' => ['nullable', 'boolean'],
            'location' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'url:http,https', 'max:2048'],
            'occupation' => ['nullable', 'string', 'max:160'],
            'interests' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
