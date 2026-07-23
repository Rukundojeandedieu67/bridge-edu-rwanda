<?php

namespace App\Policies;

use App\Models\MentorshipRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MentorshipRequestPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->role === 'student';
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['student', 'mentor', 'admin'], true);
    }

    public function view(User $user, MentorshipRequest $request): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $request->student_id || $user->id === $request->mentor_id;
    }

    public function update(User $user, MentorshipRequest $request): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Allow a verified mentor to claim an unassigned request, or allow the assigned mentor to update.
        if ($user->role === 'mentor') {
            if (is_null($request->mentor_id) && $user->is_verified_mentor) {
                return true; // claiming
            }

            if ($request->mentor_id === $user->id) {
                return true; // assigned mentor
            }
        }

        return false;
    }
}
