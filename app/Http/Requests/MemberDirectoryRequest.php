<?php

namespace App\Http\Requests;

use App\Models\CommunityRank;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberDirectoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:80'],
            'filter' => ['nullable', Rule::in(['new', 'oldest', 'active', 'legacy'])],
            'sort' => ['nullable', Rule::in(['joined', 'activity', 'messages', 'name'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'rank' => ['nullable', 'integer', Rule::exists(CommunityRank::class, 'id')->where('is_active', true)],
        ];
    }
}
