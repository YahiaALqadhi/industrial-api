<?php

namespace App\Policies;

use App\Models\User;

class BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->hasRole('super_admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function forceDelete(User $user, mixed $model): bool
    {
        return $user->hasRole('super_admin');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function restore(User $user, mixed $model): bool
    {
        return $user->hasRole('super_admin');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}