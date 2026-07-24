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
        return $user->role === 'student' || in_array($user->role, ['admin', 'super_admin'], true);
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['student', 'mentor', 'admin', 'super_admin'], true);
    }

    public function view(User $user, MentorshipRequest $request): bool
    {
        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            return true;
        }

        return $user->id === $request->student_id || $user->id === $request->mentor_id;
    }

    public function update(User $user, MentorshipRequest $request): bool
    {
        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            return true;
        }

        if ($user->role === 'mentor') {
            if (is_null($request->mentor_id) && $user->is_verified_mentor) {
                return true;
            }

            if ($request->mentor_id === $user->id) {
                return true;
            }
        }

        return false;
    }

    public function delete(User $user, MentorshipRequest $request): bool
    {
        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            return true;
        }

        return $user->id === $request->student_id;
    }
}
