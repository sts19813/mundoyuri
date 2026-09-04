<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommunityModerationActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('communityReport')) ?? false;
    }

    public function rules(): array
    {
        return ['action' => ['required', 'string', Rule::in(['hide', 'restore', 'lock_thread'])]];
    }
}
