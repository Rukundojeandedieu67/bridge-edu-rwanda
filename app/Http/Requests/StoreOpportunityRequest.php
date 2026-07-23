<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:scholarship,bootcamp,micro_task,grant'],
            'description' => ['required', 'string'],
            'provider_name' => ['required', 'string', 'max:255'],
            'eligibility_criteria' => ['nullable', 'string'],
            'application_deadline' => ['nullable', 'date'],
            'external_link' => ['nullable', 'url', 'max:255'],
            'region_tags' => ['nullable', 'array'],
            'region_tags.*' => ['string', 'max:100'],
            'is_verified' => ['nullable', 'boolean'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
