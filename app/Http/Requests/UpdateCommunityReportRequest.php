<?php

namespace App\Http\Requests;

use App\Models\CommunityReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommunityReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('communityReport')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(CommunityReport::STATUSES)],
            'resolution' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
