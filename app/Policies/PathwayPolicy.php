<?php

namespace App\Policies;

use App\Models\Pathway;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PathwayPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Pathway $pathway): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Pathway $pathway): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Pathway $pathway): bool
    {
        return $user->role === 'admin';
    }
}
