<?php

namespace App\Http\Requests;

use App\Models\CommunityReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommunityReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'target' => ['required', 'string', Rule::in(['thread', 'post', 'user'])],
            'target_id' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', Rule::in(CommunityReport::REASONS)],
            'details' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
