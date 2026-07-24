<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOpportunityApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', 'in:pending,reviewed,accepted,rejected'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'cover_letter' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
