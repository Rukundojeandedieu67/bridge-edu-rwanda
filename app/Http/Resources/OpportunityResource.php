<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category,
            'description' => $this->description,
            'provider_name' => $this->provider_name,
            'eligibility_criteria' => $this->eligibility_criteria,
            'application_deadline' => $this->application_deadline?->toDateString(),
            'external_link' => $this->external_link,
            'region_tags' => $this->region_tags,
            'is_verified' => (bool) $this->is_verified,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
