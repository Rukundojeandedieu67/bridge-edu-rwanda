<?php

namespace App\Policies;

use App\Models\PathwayStep;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PathwayStepPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PathwayStep $step): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true);
    }

    public function update(User $user, PathwayStep $step): bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true);
    }

    public function delete(User $user, PathwayStep $step): bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true);
    }
}
