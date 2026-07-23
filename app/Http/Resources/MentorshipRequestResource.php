<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MentorshipRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'full_name' => $this->student->full_name,
                    'email' => $this->student->email,
                ];
            }),
            'mentor' => $this->when($this->mentor, function () {
                return [
                    'id' => $this->mentor->id,
                    'full_name' => $this->mentor->full_name,
                    'email' => $this->mentor->email,
                ];
            }),
            'status' => $this->status,
            'topic_of_interest' => $this->topic_of_interest,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
