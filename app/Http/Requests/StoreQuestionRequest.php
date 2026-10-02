<?php

namespace App\Http\Requests;

use App\Services\RichContentService;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'body' => ['nullable', 'string', 'min:2', 'max:65000', 'required_without:image'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'body' => app(RichContentService::class)->sanitize($this->input('body')),
        ]);
    }
}
