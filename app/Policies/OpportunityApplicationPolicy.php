<?php

namespace App\Policies;

use App\Models\OpportunityApplication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OpportunityApplicationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['student', 'mentor', 'admin', 'super_admin'], true);
    }

    public function view(User $user, OpportunityApplication $application): bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true) || $user->id === $application->student_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['student', 'mentor', 'admin', 'super_admin'], true);
    }

    public function update(User $user, OpportunityApplication $application): bool
    {
        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            return true;
        }

        return $user->id === $application->student_id && in_array($application->status, ['pending', 'reviewed'], true);
    }

    public function delete(User $user, OpportunityApplication $application): bool
    {
        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            return true;
        }

        return $user->id === $application->student_id && $application->status === 'pending';
    }
}
