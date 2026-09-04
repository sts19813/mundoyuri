<?php

namespace App\Http\Requests;

use App\Models\CommunityReaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommunityReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'target' => ['required', 'string', Rule::in(['thread', 'post', 'comment'])],
            'target_id' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'string', Rule::in(CommunityReaction::typeKeys())],
        ];
    }
}
