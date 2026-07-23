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
        return $user->role === 'admin';
    }

    public function update(User $user, PathwayStep $step): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, PathwayStep $step): bool
    {
        return $user->role === 'admin';
    }
}
