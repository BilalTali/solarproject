<?php

namespace App\Policies;

use App\Models\Commission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Commission $commission): bool
    {
        if ($user->isAdmin()) return true;
        return $commission->entered_by === $user->id || $commission->payee_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Commission $commission): bool
    {
        if ($user->isAdmin()) return true;
        return $commission->entered_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Commission $commission): bool
    {
        if ($user->isAdmin()) return true;
        return $commission->entered_by === $user->id;
    }
}
