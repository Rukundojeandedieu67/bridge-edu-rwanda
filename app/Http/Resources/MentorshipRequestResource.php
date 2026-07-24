<?php

namespace App\Http\Resources;

use App\Http\Resources\MentorshipMessageResource;
use App\Http\Resources\UserResource;
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
            'student_id' => $this->student_id,
            'mentor_id' => $this->mentor_id,
            'assigned_by_admin_id' => $this->assigned_by_admin_id,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'student' => $this->whenLoaded('student', function () {
                return new UserResource($this->student);
            }),
            'mentor' => $this->whenLoaded('mentor', function () {
                return new UserResource($this->mentor);
            }),
            'assigned_by_admin' => $this->whenLoaded('assignedBy', function () {
                return new UserResource($this->assignedBy);
            }),
            'messages' => $this->whenLoaded('messages', function () {
                return MentorshipMessageResource::collection($this->messages);
            }),
            'status' => $this->status,
            'topic_of_interest' => $this->topic_of_interest,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
