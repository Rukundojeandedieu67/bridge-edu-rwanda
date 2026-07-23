<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'category' => ['sometimes', 'required', 'string', 'in:scholarship,bootcamp,micro_task,grant'],
            'description' => ['sometimes', 'required', 'string'],
            'provider_name' => ['sometimes', 'required', 'string', 'max:255'],
            'eligibility_criteria' => ['sometimes', 'nullable', 'string'],
            'application_deadline' => ['sometimes', 'nullable', 'date'],
            'external_link' => ['sometimes', 'nullable', 'url', 'max:255'],
            'region_tags' => ['sometimes', 'nullable', 'array'],
            'region_tags.*' => ['sometimes', 'string', 'max:100'],
            'is_verified' => ['sometimes', 'boolean'],
            'created_by' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ];
    }
}
